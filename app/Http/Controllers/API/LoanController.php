<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\LoanPauseRequest;
use App\Http\Requests\API\LoanRepaymentRequest;
use App\Http\Requests\API\LoanRequest;
use App\LoanAction;
use App\LoanDocumentType;
use App\LoanPayment;
use App\LoanRange;
use App\LoanType;
use App\NodCredit\Account\User;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\ApplicationFactory;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use App\NodCredit\Loan\Exceptions\ApplicationFactoryException;
use App\NodCredit\Loan\Exceptions\ApplicationHasNoPaymentException;
use App\NodCredit\Loan\Exceptions\ApplicationPauseException;
use App\NodCredit\Loan\Exceptions\CardDoesNotBelongsToUserException;
use App\NodCredit\Loan\Exceptions\PaymentChargeException;
use App\NodCredit\Loan\Exceptions\UserHasNoCardException;
use App\NodCredit\Loan\LoanPause;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Loan\PaymentCharge;
use App\NodCredit\Loan\Transformers\TransactionLogTransformer;
use App\NodCredit\Settings;
use App\TransactionLog;
use App\UserCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoanController extends ApiController
{

    public function getLoans()
    {
        $applications = ApplicationCollection::findByUserId($this->user()->id);

        return $this->successResponse('OK', [
            'loans' => $applications->transform()
        ]);
    }
    
    /**
     * @param LoanPauseRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function pauseLoan(LoanPauseRequest $request, string $id)
    {
        try {
            $application = Application::find($id);
        }
        catch (\Exception $exception) {
            return $this->errorResponse('Loan Application not found');
        }

        $card = UserCard::find($request->get('card_id'));

        try {
            $pauseHandler = new LoanPause($application);
            $paused = $pauseHandler->pauseByUserUsingCard($card);
        }
        catch (CardDoesNotBelongsToUserException $exception) {
            return $this->errorResponse('Card does not belongs to User');
        }
        catch (ApplicationHasNoPaymentException $exception) {
            return $this->errorResponse($exception->getMessage());
        }
        catch (ApplicationPauseException $exception) {
            return $this->errorResponse($exception->getMessage());
        }
        catch (\Exception $exception) {
            $paused = false;
        }

        if (! $paused) {
            return $this->errorResponse('Payment error. Be sure that you have enough balance.');
        }

        return $this->successResponse();
    }

    /**
     * @param LoanRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoanHistory(LoanRequest $request, string $id)
    {
        try {
            $application = Application::find($id);
        }
        catch (\Exception $exception) {
            return $this->errorResponse('Loan Application not found');
        }

        $actions = LoanAction::where('loan_application_id', $application->getId())
            ->orderBy('created_at', 'DESC')
            ->get();

        $payments = LoanPayment::where('loan_application_id', $application->getId())
            ->orderBy('created_at', 'DESC')
            ->get(['id'])
            ->pluck('id');


        $transactionLogs = TransactionLog::whereIn('model_id', $payments->toArray())
            ->orderBy('created_at', 'DESC')
            ->get();

        $records = collect();

        foreach ($actions as $action) {
            $records->push([
                'type' => 'action',
                'created_at' => $action->created_at,
                'description' => $action->action,
            ]);
        }

        foreach ($transactionLogs as $log) {
            $records->push([
                'type' => 'transaction_log',
                'created_at' => $log->created_at,
                'description' => $log->pay_for,
                'status' => $log->status,
                'amount' => Money::formatInNairaAsArray($log->amount)
            ]);
        }

        $records = $records->sortByDesc(function ($record) {
            return $record['created_at']->timestamp;
        });

        return $this->successResponse('OK', [
            'history' => $records->values()
        ]);
    }

    /**
     * @param LoanRepaymentRequest $request
     * @param Settings $settings
     * @param User $accountUser
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function loanRepayment(LoanRepaymentRequest $request, Settings $settings, User $accountUser, string $id)
    {
        $application = Application::find($id);

        $payment = Payment::find($request->get('loan_payment_id'));

        if ($application->getId() !== $payment->getApplicationId()) {
            return $this->errorResponse('Please select a payment.');
        }

        $card = UserCard::find($request->get('card_id'));

        $amount = (float) $request->get('amount');

        $paymentAmount = $payment->getAmount();

        $charger = new PaymentCharge($payment);

        try {
            $charged = $charger->chargeUsingCard($amount, $card, $this->user(), false);
        }
        catch (UserHasNoCardException $exception) {
            return $this->errorResponse('Please select a valid card or add a new one.');
        }
        catch (CardDoesNotBelongsToUserException $exception) {
            return $this->errorResponse('Please select a valid card or add a new one.');
        }
        catch (PaymentChargeException $exception) {
            return $this->errorResponse($exception->getMessage());
        }
        catch (\Exception $exception) {
            return $this->errorResponse('Something went wrong. Please, try again later.');
        }

        if (! $charged) {
            return $this->errorResponse('Something went wrong. Please, try again later.');
        }

        // Check payment and pause penalty
        if ($payment->isDefault() AND ! $payment->isPenaltyPaused()) {
            if ($amount >= $paymentAmount * $settings->get('loan_due_payments_penalty_pause_threshold', 20) / 100) {
                $payment->pausePenaltyFor($settings->get('loan_due_payments_penalty_pause_days', 5), $accountUser);
            }
        }

        return $this->successResponse();
    }

    /**
     * @param User $accountUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function applicationInit(User $accountUser)
    {
        $init = ApplicationFactory::init($accountUser);

        return $this->successResponse('OK', [
            'loan_types' => array_get($init, 'loan_types'),
            'loan_min' => array_get($init, 'loan_min'),
            'loan_max' => array_get($init, 'loan_max'),
            'interest_rate' => array_get($init, 'interest_rate'),
            'documents' => array_get($init, 'documents')
        ]);
    }

    /**
     * @param Request $request
     * @param User $accountUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function applicationCalculate(Request $request, User $accountUser)
    {
        $amount = $request->get('amount', 0);
        $tenor = $request->get('tenor', 0);

        try {
            $calculation = ApplicationFactory::calculate($accountUser, $amount, $tenor);
        }
        catch (ApplicationFactoryException $exception) {
            return $this->errorResponse(
                $exception->getMessage(),
                $exception->getErrors() ? $exception->getErrors()->getMessages() : null
            );
        }

        return $this->successResponse('OK', [
            'tenor' => array_get($calculation, 'tenor'),
            'payments' => array_get($calculation, 'payments')
        ]);
    }

    /**
     * @param Request $request
     * @param User $accountUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function applicationConfirm(Request $request, User $accountUser)
    {
        $data = $request->only([
            'amount',
            'tenor',
            'loan_type_id',
        ]);

        $uploadedDocuments = $request->file('documents', []);

        try {
            $application = ApplicationFactory::create($accountUser, $data, $uploadedDocuments);
        }
        catch (ApplicationFactoryException $exception) {
            return $this->errorResponse(
                $exception->getMessage(),
                $exception->getErrors() ? $exception->getErrors()->getMessages() : null
            );
        }
        catch (\Exception $exception) {
            Log::info("API Loan create exception: {$exception->getMessage()}");

            return $this->errorResponse('Something went wrong. Please, try again later.');
        }

        return $this->successResponse('Loan created', [
            'loan' => $application->transform()
        ]);
    }
}