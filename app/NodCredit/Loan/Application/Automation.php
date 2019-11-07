<?php
namespace App\NodCredit\Loan\Application;


use App\Mail\LoanApplicationValidatorReportMail;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\ApplicationRepaymentPlan;
use App\NodCredit\Loan\Document;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Message\Template;
use App\NodCredit\Settings;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Automation
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var User
     */
    private $moderator;

    /**
     * @var ValidatorResult
     */
    private $validatorResult;

    /**
     * @var Application|null
     */
    private $lastCompletedApplication;

    /**
     * @var Template
     */
    private $rejectMessageTemplate;

    /**
     * @var Template
     */
    private $statementPeriodErrorMessageTemplate;

    /**
     * @var Template
     */
    private $processingMessageTemplate;

    /**
     * @var Template
     */
    private $loanTransferredMessageTemplate;

    /**
     * @var Template
     */
    private $confirmNewAmountMessageTemplate;

    /**
     * @var Template
     */
    private $confirmLastApprovedAmountMessageTemplate;


    public static function sendHandlingConfirmationMail(Application $application)
    {
        $application->generateHandlingConfirmationToken();
        $application->setHandlingConfirmationSentAt();

        MessageSender::send('loan-application-reconfirmation', $application->getAccountUser(), [
            '#LOAN_AGE_IN_DAYS#' => $application->getCreatedAt()->diffInDays(),
            '#LOAN_HANDLING_CONFIRM_URL#' => route('loan.handling-confirmation.confirm', ['token' => $application->getHandlingConfirmationToken()]),
            '#LOAN_HANDLING_REJECT_URL#' => route('loan.handling-confirmation.reject', ['token' => $application->getHandlingConfirmationToken()]),
        ]);

    }

    public static function handle(Application $application)
    {
        $automation = new static($application);

        if ($application->isNew()) {
            return $automation->handleNewAndReady();
        }
        else if ($application->isProcessing()) {
            return $automation->handleUsingBankStatement();
        }
        else if ($application->isApproval()) {
            return $automation->handleApproval();
        }

        return null;
    }

    /**
     * Automation constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->validator = new Validator($application->getModel());
        $this->settings = app(Settings::class);
        $this->moderator = User::whereEmail(config('nodcredit.administrator'))->first();

        if ($lastCompletedLoan = $this->application->getUser()->getLastCompletedLoan()) {
            $this->lastCompletedApplication = new Application($lastCompletedLoan);
        }
    }

    /**
     * @return bool
     */
    public function handleNewAndReady()
    {
        if (! $this->application->isNew()) {
            return false;
        }

        $this->log("Handle new and ready Loan Application {$this->application->getModel()}");


        // User has completed loan
        if ($this->lastCompletedApplication) {
            return $this->handleUsingCompletedLoan();
        }

        // No completed loan
        return $this->handleUsingBankStatement();
    }


    public function handleApproval()
    {
        if (! $this->application->isApproval()) {
            return false;
        }

        $this->log("Handle approval Loan Application {$this->application->getModel()}");

        try {
            $approved = $this->application->approved($this->application->getAmountAllowed(), $this->moderator);
        }
        catch (\Exception $exception) {
            $this->log('Approve failed. Error: ' . $exception->getMessage());

            return false;
        }

        if (! $approved) {
            return false;
        }

        // Build payment records
        try {
            $this->application->generatePaymentsForApprovedAmount();
        }
        catch (\Exception $exception) {
            $this->log('Generate Payments for approved amount failed. Error: ' . $exception->getMessage());
        }

        $user = $this->application->getUser();

        if (! $paymentInfo = $user->paymentInfo()) {
            $this->log('Payment information not correct. Check to make sure bank account number, name and recipient is correct. ' . json_encode($paymentInfo));

            return false;
        }

        $createPayload = [
            'type' => 'nuban',
            'name' => $paymentInfo['name'],
            'description' => 'Nod credit loan transfer',
            'account_number' => $paymentInfo['account_number'],
            'bank_code' => $paymentInfo['bank']->code,
            'currency' => 'NGN',
            'metadata' => [/*'uinscope' => $request->user()->id, */'stime' => now()]
        ];

        $this->log('Paystack transfer recipient request. Payload: ' . json_encode($createPayload));

        $createRecipientResponse = makePaystackPostRequest('https://api.paystack.co/transferrecipient', $createPayload);

        $this->log('Paystack transfer recipient request. Response: ' . json_encode($createRecipientResponse));

        if ($createRecipientResponse['status'] !== 'ok' OR $createRecipientResponse['data']->status === false) {
            $this->log('Paystack transfer recipient error.');

            return false;
        }

        $recipientCode = $createRecipientResponse['data']->data->recipient_code;

        $user->recipient_code = $recipientCode;
        $user->save();

        $transferPayload = [
            'source' => 'balance',
            'reason' => $createPayload['description'],
            'amount' => doubleval($this->application->getAmountApproved() * 100),
            'recipient' => $recipientCode
        ];

        $this->log('Paystack transfer request. Payload: ' . json_encode($createPayload));

        $transferResponse = makePaystackPostRequest('https://api.paystack.co/transfer', $transferPayload);

        $this->log('Paystack transfer request. Response: ' . json_encode($transferResponse));

        if ($createRecipientResponse['status'] !== 'ok' OR $transferResponse['data']->status === false) {
            $this->log('Paystack transfer request error.');

            return false;
        }

        $this->application->paidOut();

        $user->loanActions()->create([
            'loan_application_id' => $this->application->getId(),
            'action' => 'Money transferred to your account',
            'finger_print' => 'automation'
        ]);

        $this->log('Money transferred to account.');

        $this->sendLoanTransferredMailToUser();

        return true;
    }

    private function handleUsingCompletedLoan()
    {
        // CASE 1
        // Requested amount is higher than prev. approved amount
        if ($this->application->getAmountRequested() > $this->lastCompletedApplication->getAmountApproved()) {
            return $this->handleUsingBankStatement();
        }

        // CASE 2
        // Requested amount is less than or equal to prev. approved amount
        $this->log("Requested amount {$this->application->getAmountRequested()} is less OR equal to last approved amount {$this->lastCompletedApplication->getAmountApproved()}. Approve immediately.");

        $this->application->setAmountAllowed($this->application->getAmountRequested());

        try {
            $this->application->approval();

            // Approve immediately
            return $this->handleApproval();
        }
        catch (\Exception $exception) {
            $this->log("Approval exception: {$exception->getMessage()}");

            return false;
        }
    }

    private function handleUsingBankStatement()
    {
        /** @var Document $document */
        $document = $this->application->getBankStatementDocument();

        if (! $document) {
            $this->log("[{$this->application->getId()}] Application has no bank statement document.");

            return false;
        }

        // Document has related parser
        if ($document->hasParserId()) {

            // Export document to Parser
            if (! $document->hasBeenSentToParser()) {

                $this->sendProcessingMailToUser();

                $document->parserStatusNew();

                try {
                    $this->application->processing();
                }
                catch (\Exception $exception) {
                    $this->log("Can`t change status to processing. Message: {$exception->getMessage()}");

                    return false;
                }

                $this->log("Added to queue: export Bank Statement to Parser");

                return true;
            }

            if (! $document->isHandledByParser()) {
                return true;
            }
        }

        $this->validatorResult = $this->validator->validate();

        // REJECT APPLICATION
        if ($this->validatorResult->shouldReject()) {
            return $this->reject();
        }

        // APPLICATION NOT VALID
        if ($this->validatorResult->isValid() === false) {
            return $this->handleNotValid();
        }

        // APPLICATION VALID
        return $this->handleValid();
    }

    private function reject(): bool
    {
        try {
            $this->application->reject();
        }
        catch (\Exception $exception) {
            Log::channel('loan-automation')->info("[{$this->application->getId()}] Rejecting fail. Exception message: {$exception->getMessage()}");

            return false;
        }

        Log::channel('loan-automation')->info("[{$this->application->getId()}] Rejected. Validator result errors: " . strip_tags(implode('. ', $this->validatorResult->getErrors())));

        $this->sendRejectedMailToUser();

        return true;
    }


    private function handleNotValid()
    {
        // CASE 1
        // Send email to user if statement period is not valid
        if ($statementPeriodError = $this->validatorResult->getStatementPeriodError()) {
            $this->sendStatementPeriodErrorToUser($statementPeriodError);

            try {
                $this->application->new();
            }
            catch (\Exception $exception) {
                Log::channel('loan-automation')->info("[{$this->application->getId()}] Set status to new fail. Exception message: {$exception->getMessage()}");

                return false;
            }

            $this->moderator->loanActions()->create([
                'loan_application_id' => $this->application->getId(),
                'action' => 'Document deleted #' . e($this->application->getBankStatementDocument()->getDescription()),
                'finger_print' => ''
            ]);

            $this->application->getBankStatementDocument()->delete();
            $this->application->resetRequiredUploadedDocuments();

            return true;
        }

        // CASE 2
        // Can`t parse statement, send mail to admin
        if ($this->validatorResult->getStatementError()) {

            $this->log("Can`t parse Statement. Change status to 'waiting' and send email to admin.");

            try {
                $this->application->waiting();
            }
            catch (\Exception $exception) {
                $this->log("Can`t change status to waiting. Message: {$exception->getMessage()}");

                return false;
            }

            $to = config('nodcredit.mail_to.not_parsed_loans');

            $this->sendValidatorReportToAdmin($to);

            return true;
        }

        return false;
    }

    private function handleValid()
    {
        // CASE 1
        if ($this->application->getAmountRequested() > $this->validatorResult->getAmountAllowed()) {

            $this->application->setAmountAllowed($this->validatorResult->getAmountAllowed());

            try {
                $this->application->waiting();
            }
            catch (\Exception $exception) {
                $this->log("Waiting exception: {$exception->getMessage()}");

                return false;
            }

            // CASE 1.1
            if ($this->lastCompletedApplication AND $this->lastCompletedApplication->getAmountApproved() > $this->validatorResult->getAmountAllowed()) {
                $this->log("User has completed loan and last approved amount {$this->lastCompletedApplication->getAmountApproved()} is greater than allowed amount {$this->validatorResult->getAmountAllowed()} by validator. Send email to user to approve new amount.");

                $this->application->setAmountAllowed($this->lastCompletedApplication->getAmountApproved());

                $this->sendConfirmLastApprovedAmountMailToUser();
            }
            // CASE 1.2
            else {
                $this->log("Allowed amount {$this->validatorResult->getAmountAllowed()} is less than requested amount {$this->application->getAmountRequested()}. Send email to user to approve new amount.");

                $this->sendConfirmNewAmountMailToUser();
            }

        }
        // CASE 2
        else {

            $this->application->setAmountAllowed($this->application->getAmountRequested());

            $this->log('Loan Application is valid for approval. Change status to approval and send mails.');

            try {
                $this->application->approval();
            } catch (\Exception $exception) {
                $this->log("Approval exception: {$exception->getMessage()}");

                return false;
            }

            $to = config('nodcredit.mail_to.parsed_loans');

            $this->sendValidatorReportToAdmin($to);
        }

    }

    private function sendValidatorReportToAdmin(array $to = null): self
    {
        $to = $to ?: config('nodcredit.mail_to_admins');

        Mail::to($to)->queue(new LoanApplicationValidatorReportMail($this->application->getModel(), $this->validatorResult));

        return $this;
    }

    private function sendProcessingMailToUser(): self
    {
        MessageSender::send($this->getProcessingMessageTemplate(), $this->application->getAccountUser());

        return $this;
    }

    private function sendConfirmNewAmountMailToUser(): self
    {
        MessageSender::send($this->getConfirmNewAmountMessageTemplate(), $this->application->getAccountUser(), [
            '#LOAN_AMOUNT_CONFIRM_URL#' => route('account.loans.amount-confirm', ['id' => $this->application->getId()]),
            '#LOAN_AMOUNT_REJECT_URL#' => route('account.loans.amount-reject', ['id' => $this->application->getId()]),
            '#LOAN_AMOUNT_ALLOWED#' => 'N' . number_format($this->application->getAmountAllowed())
        ]);

        return $this;
    }

    private function sendConfirmLastApprovedAmountMailToUser(): self
    {
        MessageSender::send($this->getConfirmLastApprovedAmountMessageTemplate(), $this->application->getAccountUser(), [
            '#LOAN_AMOUNT_CONFIRM_URL#' => route('account.loans.amount-confirm', ['id' => $this->application->getId()]),
            '#LOAN_AMOUNT_REJECT_URL#' => route('account.loans.amount-reject', ['id' => $this->application->getId()]),
            '#LOAN_AMOUNT_ALLOWED#' => 'N' . number_format($this->application->getAmountAllowed())
        ]);

        return $this;
    }

    private function sendRejectedMailToUser(): self
    {
        MessageSender::send($this->getRejectMessageTemplate(), $this->application->getAccountUser());

        return $this;
    }

    private function sendStatementPeriodErrorToUser(string $statementPeriodError = ''): self
    {
        MessageSender::send($this->getStatementPeriodErrorMessageTemplate(), $this->application->getAccountUser(), [
            '#LOAN_URL#' => route('account.loans.show', ['id' => $this->application->getId()]),
            '#REASON#' => $statementPeriodError
        ]);

        return $this;
    }

    private function sendLoanTransferredMailToUser(): self
    {

        MessageSender::send('loan-application-money-transferred', $this->application->getAccountUser(), [
            '#LOAN_AMOUNT_APPROVED#' => 'N' . number_format($this->application->getAmountApproved(),2),
            '#LOAN_REPAYMENT_PLAN#' => ApplicationRepaymentPlan::generateHtmlTable($this->application)
        ]);

        return $this;
    }

    /**
     * @return Template|null
     */
    private function getRejectMessageTemplate()
    {
        $templateKey = 'loan-application-rejected';

        try {
            $this->rejectMessageTemplate = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }

        return $this->rejectMessageTemplate;
    }

    /**
     * @return Template|null
     */
    private function getProcessingMessageTemplate()
    {
        $templateKey = 'loan-application-processing';

        try {
            $this->processingMessageTemplate = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }

        return $this->processingMessageTemplate;
    }

    /**
     * @return Template|null
     */
    private function getConfirmNewAmountMessageTemplate()
    {
        $templateKey = 'loan-application-confirm-new-amount';

        try {
            $this->confirmNewAmountMessageTemplate = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }

        return $this->confirmNewAmountMessageTemplate;
    }

    /**
     * @return Template|null
     */
    private function getConfirmLastApprovedAmountMessageTemplate()
    {
        $templateKey = 'loan-application-confirm-last-approved-amount';

        try {
            $this->confirmLastApprovedAmountMessageTemplate = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }

        return $this->confirmLastApprovedAmountMessageTemplate;
    }

    /**
     * @return Template|null
     */
    private function getStatementPeriodErrorMessageTemplate()
    {
        $templateKey = 'loan-application-invalid-statement-period';

        try {
            $this->statementPeriodErrorMessageTemplate = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }

        return $this->statementPeriodErrorMessageTemplate;
    }

    private function log(string $message): self
    {
        Log::channel('loan-automation')->info("[{$this->application->getId()}] " . $message);

        return $this;
    }

}