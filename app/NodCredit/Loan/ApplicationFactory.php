<?php
namespace App\NodCredit\Loan;

use App\Events\NewLoanApplication;
use App\LoanApplication as EloquentModel;
use App\LoanApplication;
use App\LoanDocumentType;
use App\LoanRange;
use App\LoanType;
use App\NodCredit\Account\User;
use App\NodCredit\Loan\Exceptions\ApplicationFactoryException;
use App\NodCredit\Loan\Exceptions\ApplicationValidateException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;


class ApplicationFactory
{

    /**
     * @param User $user
     * @return array
     */
    public static function init(User $user): array
    {
        $loanRange = $user->getModel()->getUserLoanMinMax();

        return [
            'loan_types' => LoanType::all(),
            'loan_min' => $loanRange->loan_min,
            'loan_max' => $loanRange->loan_max,
            'interest_rate' => $user->getModel()->getInterestRate(),
            'documents' => LoanDocumentType::where('is_required', true)->get()
        ];
    }

    /**
     * @param User $user
     * @param int $amount
     * @param int $tenor
     * @return array
     * @throws ApplicationFactoryException
     */
    public static function calculate(User $user, int $amount, int $tenor): array
    {
        $errors = new MessageBag();

        $userLoanRange = $user->getModel()->getUserLoanMinMax();

        // Validate amount
        if ($amount > $userLoanRange->loan_max) {
            $errors->add('amount.max', "Amount may not be greater than {$userLoanRange->loan_max}");
        }

        if ($amount < $userLoanRange->loan_min) {
            $errors->add('amount.min', "Amount must be at least {$userLoanRange->loan_min}");
        }

        $loanRangeArray = [];

        // Validate tenor
        if ($loanRange = LoanRange::getByAmount($amount)) {
            $loanRangeArray = range($loanRange->min_month, $loanRange->max_month);
        }
        else {
            $errors->add('amount.range', 'Loan range is not valid');
        }

        if (! in_array($tenor, $loanRangeArray)) {
            $tenorError = 'Tenor is not valid';

            if (count($loanRangeArray)) {
                $tenorError .= ". Valid values: " . implode(', ', $loanRangeArray);
            }

            $errors->add('tenor', $tenorError);
        }

        if ($errors->any()) {
            $exception = new ApplicationFactoryException('Data is invalid');
            $exception->setErrors($errors);

            throw $exception;
        }

        $payments = LoanApplication::buildMonthlyPayment($amount, $user->getModel()->getInterestRate(), $tenor);

        return [
            'tenor' => $loanRangeArray,
            'payments' => $payments
        ];
    }

    /**
     * @param User $user
     * @param array $data
     * @param array $uploadedDocuments
     * @return Application
     * @throws ApplicationFactoryException
     */
    public static function create(User $user, array $data, array $uploadedDocuments)
    {
        $errors = new MessageBag();

        if (! $user->canApplyForLoan()) {
            $errors->add('user', 'Can not apply for a new Loan.');

            $exception = new ApplicationFactoryException('Can not apply for a new Loan.');
            $exception->setErrors($errors);

            throw $exception;
        }

        $amount = (int) array_get($data, 'amount');
        $tenor = (int) array_get($data, 'tenor');
        $loanTypeId = array_get($data, 'loan_type_id');
        $interestRate = $user->getModel()->getInterestRate();

        $userLoanRange = $user->getModel()->getUserLoanMinMax();


        // Validate amount
        if ($amount > $userLoanRange->loan_max) {
            $errors->add('amount.max', "Amount may not be greater than {$userLoanRange->loan_max}");
        }

        if ($amount < $userLoanRange->loan_min) {
            $errors->add('amount.min', "Amount must be at least {$userLoanRange->loan_min}");
        }

        $loanRangeArray = [];

        // Validate tenor
        if ($loanRange = LoanRange::getByAmount($amount)) {
            $loanRangeArray = range($loanRange->min_month, $loanRange->max_month);
        }
        else {
            $errors->add('amount.range', 'Loan range is not valid');
        }

        if (!in_array($tenor, $loanRangeArray)) {
            $tenorError = 'Tenor is not valid';

            if (count($loanRangeArray)) {
                $tenorError .= ". Valid values: " . implode(', ', $loanRangeArray);
            }

            $errors->add('tenor', $tenorError);
        }

        // Validate loan type
        if (! $loanType = LoanType::find($loanTypeId)) {
            $errors->add('loan_type_id', 'Loan Type is not valid');
        }

        // Validate required documents
        $requiredDocuments = LoanDocumentType::where('is_required', true)->get();

        $documentRules = [];
        $documentAttributes = [];

        foreach ($requiredDocuments as $requiredDocument) {

            $documentAttributes[$requiredDocument->id] = $requiredDocument->name;

            $documentRules[$requiredDocument->id] = ['required', 'file'];

            if ($requiredDocument->file_type !== '*') {
                $documentRules[$requiredDocument->id][] = 'mimes:' . $requiredDocument->file_type;
            }
        }

        $documentsValidator = Validator::make($uploadedDocuments, $documentRules, [], $documentAttributes);

        if ($documentsValidator->fails()) {
            $errors->merge($documentsValidator->errors());
        }

        if ($errors->any()) {
            $exception = new ApplicationFactoryException('Data is invalid');
            $exception->setErrors($errors);

            throw $exception;
        }

        // Create Loan
        $application = Application::create([
            'amount_requested' => $amount,
            'amount_approved' => 0,
            'loan_type_id' => $loanTypeId,
            'tenor' => $tenor,
            'interest_rate' => $interestRate,
            'user_id' => $user->getId()
        ]);

        // Create payments plan
        $application->generatePaymentsForAmount($amount);

        // Create Loan Documents
        foreach ($requiredDocuments as $requiredDocument) {

            /** @var UploadedFile $uploadedDocument */
            $uploadedDocument = $uploadedDocuments[$requiredDocument->id];

            Document::storeAndCreate($uploadedDocument, [
                'loan_application_id' => $application->getId(),
                'document_type' => $requiredDocument->id,
                'document_extension' => $uploadedDocument->extension(),
                'description' => $requiredDocument->name,
            ]);
        }

        $application->refreshHasRequiredUploadedDocuments();

        event(new NewLoanApplication($application->getModel()));

        $user->getModel()->loanActions()->create([
            'loan_application_id' => $application->getId(),
            'action' => 'Loan created',
            'finger_print' => request()->ip()
        ]);

        return $application;
    }

}