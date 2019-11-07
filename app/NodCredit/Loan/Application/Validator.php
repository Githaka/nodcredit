<?php
namespace App\NodCredit\Loan\Application;

use App\LoanApplication;
use App\LoanDocumentType;
use App\NodCredit\Account\User;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Application\Rules\LoanAmountLessThanStatementHighestCreditAmount;
use App\NodCredit\Loan\Application\Rules\LoanAmountLessThanStatementMonthlyAvgCreditsAmount;
use App\NodCredit\Loan\Application\Rules\MinThresholdAmount;
use App\NodCredit\Loan\Application\Rules\StatementCustomerMatchUser;
use App\NodCredit\Loan\Application\Rules\StatementHighestDebitLessThanHighestCredit;
use App\NodCredit\Loan\Application\Rules\StatementInflateCredits;
use App\NodCredit\Loan\Application\Rules\StatementLastMonthHighestCreditProtected;
use App\NodCredit\Loan\Application\Rules\StatementPeriod;
use App\NodCredit\Loan\Application\Rules\StatementPeriodMoreThanMonth;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;
use App\NodCredit\Settings;
use App\NodCredit\Statement\Exceptions\FileReaderException;
use App\NodCredit\Statement\Exceptions\RecognizingStatementException;
use App\NodCredit\Statement\PdfParser\Factory;
use App\NodCredit\Statement\Statement;
use App\NodCredit\Statement\Transactions;

class Validator
{

    /**
     * @var LoanApplication
     */
    private $loanApplication;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $documents;

    /**
     * @var Statement
     */
    private $statement;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Application
     */
    private $application;

    /**
     * ApplicationValidator constructor.
     * @param LoanApplication $loanApplication
     */
    public function __construct(LoanApplication $loanApplication)
    {
        $this->loanApplication = $loanApplication;

        $this->application = new Application($loanApplication);

        $this->accountUser = new User($loanApplication->owner);

        $this->documents = $loanApplication->documents()->get();

        $this->statement = $this->getParsedStatement();

        $this->settings = app(Settings::class);
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getStatement(): Statement
    {
        return $this->statement;
    }

    public function getLoanApplication(): LoanApplication
    {
        return $this->loanApplication;
    }

    public function getAccountUser(): User
    {
        return $this->accountUser;
    }

    public function validate(): ValidatorResult
    {
        $validLoanAmount = $this->loanApplication->amount_requested;

        $isNewValidLoanAmount = false;
        $shouldReject = false;
        $shouldReview = false;
        $errors = [];
        $messages = [];
        $deductions = [];

        if (! $this->hasRequiredDocuments()) {
            $errors['documents'] = 'Application has not all required Documents.';
            $shouldReview = true;
        }

        // If there is statement
        if ($this->statement) {

            // RULE
            try {
                $isStatementPeriodValid = StatementPeriod::validate($this);
            }
            catch (ApplicationValidateRuleException $exception) {
                $errors['statement_period'] = $exception->getMessage();
                $shouldReview = true;
                $isStatementPeriodValid = false;
            }

            // if statement period is valid, validate statement
            if ($isStatementPeriodValid) {

                // RULE, new valid loan amount
                try {
                    StatementInflateCredits::validate($this);
                }
                catch (ApplicationValidateRuleException $exception) {

                    if ($highestCredit = $this->statement->getTransactions()->getHighestCredit()) {
                        $percent = $this->getSettings()->get('automation_rule_inflate_credits_new_valid_amount', 10);

                        $validLoanAmount = $highestCredit->getAmount() * $percent / 100;

                        $deductions['StatementInflateCredits'] = [
                            $exception->getMessage(),
                            $percent. '% of highest credit amount'
                        ];

                        $isNewValidLoanAmount = true;
                    }
                }

                if (! $isNewValidLoanAmount) {
                    // RULE, new valid loan amount
                    try {
                        LoanAmountLessThanStatementMonthlyAvgCreditsAmount::validate($this);
                    }
                    catch (ApplicationValidateRuleException $exception) {
                        $percent = $this->getSettings()->get('automation_rule_loan_amount_less_than_statement_monthly_avg_credits_amount', 33);
                        $validLoanAmount = $this->statement->getMonthlyAvgCreditsAmount() * $percent / 100;
                        $deductions['LoanAmountLessThanStatementMonthlyAvgCreditsAmount'] = $percent. '% of MonthlyAvgCreditsAmount';
                        $isNewValidLoanAmount = true;
                    }
                }

                // RULE, deduct
                try {
                    LoanAmountLessThanStatementHighestCreditAmount::validate($this);
                }
                catch (ApplicationValidateRuleException $exception) {
                    $percent = $this->getSettings()->get('automation_rule_loan_amount_less_than_statement_highest_credit_amount_deduct', 5);
                    $validLoanAmount = $validLoanAmount * (100 - $percent) / 100;
                    $deductions['LoanAmountLessThanStatementHighestCreditAmount'] = $percent;
                }

                // RULE, deduct
                try {
                    StatementHighestDebitLessThanHighestCredit::validate($this);
                }
                catch (ApplicationValidateRuleException $exception) {
                    $percent = $this->getSettings()->get('automation_rule_statement_highest_debit_less_than_highest_credit_deduct', 5);
                    $validLoanAmount = $validLoanAmount * (100 - $percent) / 100;
                    $deductions['StatementHighestDebitLessThanHighestCredit'] = $percent;
                }

                // RULE, deduct
                try {
                    StatementLastMonthHighestCreditProtected::validate($this);
                }
                catch (ApplicationValidateRuleException $exception) {
                    $percent = $this->getSettings()->get('automation_rule_statement_last_month_highest_credit_protected_deduct', 5);
                    $validLoanAmount = $validLoanAmount * (100 - $percent) / 100;
                    $deductions['StatementLastMonthHighestCreditProtected'] = $percent;
                }

                // RULE, deduct
                try {
                    StatementPeriodMoreThanMonth::validate($this);
                }
                catch (ApplicationValidateRuleException $exception) {
                    $percent = $this->getSettings()->get('automation_rule_statement_period_more_than_month_deduct', 10);
                    $validLoanAmount = $validLoanAmount * (100 - $percent) / 100;
                    $deductions['StatementPeriodMoreThanMonth'] = $percent;
                }

                // RULE
                try {
                    StatementCustomerMatchUser::validate($this);
                    $messages['customer_name'] = "Statement customer name <b>" . $this->getStatement()->getCustomerName() . "</b> 
                        match user name <b>" . $this->getLoanApplication()->owner->name . "</b>.";
                }
                catch (ApplicationValidateRuleException $exception) {
                    $shouldReject = true;
                    $errors['customer_name'] = $exception->getMessage();
                }

                // RULE: Deduct if lenders loans are not returned
                if ($lendersDeductCount = $this->countLendersForDeduction()) {
                    $percent = $this->getSettings()->get('automation_rule_lender_deduct_percent', 5);
                    $totalPercent = $percent * $lendersDeductCount;

                    $validLoanAmount = $validLoanAmount * (100 - $totalPercent) / 100;
                    $deductions['automation_rule_lender_deduct'] = [
                        'percent_per_lender' => $percent,
                        'percent_per_lenders' => $totalPercent,
                        'count' => $lendersDeductCount
                    ];
                }

                // RULE: Deduct if customer did not install app and confirmed "no mobile device"
                try {
                    Application\Rules\AppIsInstalled::validate($this);
                }
                catch (ApplicationValidateRuleException $exception) {
                    $percent = $this->getSettings()->get('automation_rule_app_install_skipped_deduct', 30);

                    $validLoanAmount = $validLoanAmount * (100 - $percent) / 100;

                    $deductions['automation_rule_app_install_skipped_deduct'] = "Deduct {$percent}%.";
                }

                // MESSAGE: Salary transactions
                $salaryTransactions = $this->getStatement()->findSalaryTransactions();
                if ($salaryTransactions->count()) {
                    $messages['salary_transactions'] = $this->createSalaryMessage($salaryTransactions);
                }

                // MESSAGE: Account number matching
                $accountMatchingMessage = "Statement Account number <b>" . $this->getStatement()->getAccountNumber() . "</b> 
                    %s User Bank Account number <b>" . $this->getLoanApplication()->owner->account_number."</b>.";

                if ($this->getLoanApplication()->owner->account_number !== $this->getStatement()->getAccountNumber()) {
                    $errors['bank_account_number'] = sprintf($accountMatchingMessage, ' does not match ');
                }
                else {
                    $messages['bank_account_number'] = sprintf($accountMatchingMessage, ' match ');
                }

                // MESSAGE: User name in transactions
                if ($this->getStatement()->getTransactions()->hasUserNameInDescription($this->getLoanApplication()->owner->name)) {
                    $messages['user_name_in_transactions'] = 'Found';
                }
                else {
                    $errors['user_name_in_transactions'] = 'Not found';
                }

            }
        }
        else {
            $errors['statement'] = 'Can`t parse Statement';
            $shouldReview = true;
        }

        // Reject if new loan amount is less than min threshold
        if ($validLoanAmount < $this->getLoanApplication()->amount_requested) {
            try {
                MinThresholdAmount::validate($this, $validLoanAmount);
            }
            catch (ApplicationValidateRuleException $exception) {
                $shouldReject = true;
                $errors['min_threshold_amount'] = $exception->getMessage();
            }
        }

        $result = new ValidatorResult(floatval($validLoanAmount), $errors, $messages);
        $result->setRejectStatus($shouldReject);
        $result->setReviewStatus($shouldReview);
        $result->setDeductions($deductions);

        return $result;
    }

    private function hasRequiredDocuments(): bool
    {
        $requiredIds = LoanDocumentType::where('is_required', 1)->select('id')->get()->pluck('id')->toArray();
        $requiredIds = array_unique($requiredIds);

        $uploadedIds = $this->documents->pluck('document_type')->toArray();
        $uploadedIds = array_unique($uploadedIds);

        $diff = array_diff($requiredIds, $uploadedIds);

        if (count($diff)) {
            return false;
        }

        return true;
    }

    private function getParsedStatement()
    {
        if (! $document = $this->application->getBankStatementDocument()) {
            return null;
        }

        if (! $document->hasParsedData()) {
            return null;
        }

        return $document->convertParsedDataToStatement();
    }

    /**
     * @deprecated docparser is new way to parse
     * @return Statement|null
     */
    private function parseStatementDocument()
    {
        if ($document = $this->application->getBankStatementDocument()) {

            try {
                $parser = Factory::createParserFromFile($document->getFullpath());
            }
            catch (FileReaderException $exception) {
                return null;
            }
            catch (RecognizingStatementException $exception) {
                return null;
            }

            $statement = $parser->parse();

            return $statement;
        }

        return null;
    }

    private function countLendersForDeduction(): int
    {
        $count = 0;

        $lenders = $this->getSettings()->get('automation_rule_lender_list', []);

        $lendersTransactions = $this->getStatement()->findLendersTransactions($lenders);

        foreach ($lenders as $lender) {
            if ($lendersTransactions->findTransactionsByKeywords([$lender])->calculateBalanceBetweenTransactions() > 0) {
                $count++;
            }
        }

        return $count;
    }

    private function createSalaryMessage(Transactions $salaryTransactions): string
    {
        $periodValidDays = $this->getStatement()->getPeriodDaysValidCount();
        $monthCount = ceil($periodValidDays / 30);

        $message = 'Found Salary transaction(s) in Bank Statement.<br>';
        $message .= "Statement period cover for <b>$periodValidDays</b> days (round up to <b>$monthCount</b> month(s)).<br>";

        if ($salaryTransactions->count() > 1) {
            $salaryAvgAmount = $salaryTransactions->getCreditsAmount() / $monthCount;
            $salaryAvgAmount = 'N' . number_format($salaryAvgAmount);
            $message .= "Salary average value is <b>$salaryAvgAmount</b>. <br>";
        }

        $message .= '<br>';
        $message .= $salaryTransactions->toHtml();

        return $message;
    }


}