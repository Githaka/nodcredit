<?php

namespace App\Http\Controllers;

use App\LoanApplication;
use App\Mail\LoanApplicationProcessingMail;
use App\Mail\LoanApplicationValidatorReportMail;
use App\NodCredit\Loan\Application;
use App\NodCredit\Message\MessageSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AccountLoanController extends Controller
{

    public function getAllowedAmountConfirm(Request $request, string $id)
    {
        $user = $request->user();

        /** @var LoanApplication $model */
        $model = LoanApplication::where('id', $id)
            ->where('status', Application::STATUS_WAITING)
            ->where('amount_allowed', '>', 0)
            ->where('user_id', $user->id)
            ->first();

        // no loan
        if (! $model) {
            return redirect()->route('account.loans');
        }

        $application = new Application($model);

        Log::channel('loan-automation')->info("[{$application->getModel()->id}] User confirmed new amount.");

        try {
            $application->approval();
        }
        catch (\Exception $exception) {
            Log::channel('loan-automation')->info("[{$application->getModel()->id}] Approval exception: {$exception->getMessage()}");

            return redirect()->route('account.loans')->with('error', 'Error. Please contact admin.');
        }

        $user->loanActions()->create([
            'loan_application_id' => $application->getId(),
            'action' => 'New loan amount confirmed',
            'finger_print' => $request->ip()
        ]);

        // Build payment records
        try {
            $application->generatePaymentsForAllowedAmount();
        }
        catch (\Exception $exception) {
            Log::channel('loan-automation')->error('Generate Payments for allowed amount failed. Error: ' . $exception->getMessage());
        }

        MessageSender::send('loan-application-processing', $application->getAccountUser());

        $validator = new Application\Validator($application->getModel());
        $validatorResult = $validator->validate();

        Mail::to(config('nodcredit.mail_to_admins'))->queue(new LoanApplicationValidatorReportMail($application->getModel(), $validatorResult));

        return redirect()->route('account.loans.show', ['id' => $application->getId()])->with('success', 'New loan amount confirmed. Your Loan is being processed.');
    }

    public function getAllowedAmountReject(Request $request, string $id)
    {
        $user = $request->user();

        $model = LoanApplication::where('id', $id)
            ->where('status', Application::STATUS_WAITING)
            ->where('amount_allowed', '>', 0)
            ->where('user_id', $user->id)
            ->first();

        if (! $model) {
            return redirect()->route('account.loans');
        }

        $application = new Application($model);

        Log::channel('loan-automation')->info("[{$application->getModel()->id}] User rejected new amount.");

        try {
            $application->reject();
        }
        catch (\Exception $exception) {
            Log::channel('loan-automation')->info("[{$application->getModel()->id}] Reject exception: {$exception->getMessage()}");

            return redirect()->route('account.loans')->with('error', 'Error. Please contact admin.');
        }

        $user->loanActions()->create([
            'loan_application_id' => $application->getId(),
            'action' => 'New loan amount rejected',
            'finger_print' => $request->ip()
        ]);

        return redirect()->route('account.loans.show', ['id' => $application->getId()])->with('success', 'New loan amount rejected.');
    }

    public function getPrevLoanAmountConfirm(Request $request, string $id)
    {
        $user = $request->user();

        /** @var LoanApplication $model */
        $model = LoanApplication::where('id', $id)
            ->where('status', Application::STATUS_WAITING)
            ->where('amount_allowed', '>', 0)
            ->where('user_id', $user->id)
            ->first();

        $prevLoan = $user->getLastCompletedLoan();

        if (! $model OR ! $prevLoan) {
            return redirect()->route('account.loans');
        }

        $application = new Application($model);

        Log::channel('loan-automation')->info("[{$application->getModel()->id}] User confirmed last completed loan amount.");

        try {
            $application->setAmountAllowed($prevLoan->amount_approved);
            $application->approval();
        }
        catch (\Exception $exception) {
            Log::channel('loan-automation')->info("[{$application->getModel()->id}] Approval exception: {$exception->getMessage()}");

            return redirect()->route('account.loans')->with('error', 'Error. Please contact admin.');
        }

        // Build payment records
        try {
            $application->generatePaymentsForAllowedAmount();
        }
        catch (\Exception $exception) {
            Log::channel('loan-automation')->error('Generate Payments for allowed amount failed. Error: ' . $exception->getMessage());
        }

        $user->loanActions()->create([
            'loan_application_id' => $application->getId(),
            'action' => 'Previous completed loan amount confirmed',
            'finger_print' => $request->ip()
        ]);

        MessageSender::send('loan-application-processing', $application->getAccountUser());

        $validator = new Application\Validator($application->getModel());
        $validatorResult = $validator->validate();

        Mail::to(config('nodcredit.mail_to_admins'))->queue(new LoanApplicationValidatorReportMail($application->getModel(), $validatorResult));

        return redirect()->route('account.loans.show', ['id' => $application->getId()])->with('success', 'Previous completed loan amount confirmed. Your Loan is being processed.');
    }

    public function getNewAmountConfirmManually(Request $request, string $id)
    {
        $user = $request->user();

        /** @var LoanApplication $model */
        $model = LoanApplication::where('id', $id)
            ->where('status', Application::STATUS_WAITING)
            ->where('amount_allowed', '>', 0)
            ->where('user_id', $user->id)
            ->first();

        // no loan
        if (! $model) {
            return redirect()->route('account.loans');
        }

        $application = new Application($model);

        try {
            $application->approval();
        }
        catch (\Exception $exception) {
            return redirect()->route('account.loans')->with('error', 'Error. Please contact admin.');
        }

        $user->loanActions()->create([
            'loan_application_id' => $application->getId(),
            'action' => 'New loan amount confirmed',
            'finger_print' => $request->ip()
        ]);

        // Build payment records
        try {
            $application->generatePaymentsForAllowedAmount();
        }
        catch (\Exception $exception) {
            return redirect()->route('account.loans')->with('error', 'Error. Please contact admin.');
        }

        return redirect()->route('account.loans.show', ['id' => $application->getId()])->with('success', 'New loan amount confirmed. Your Loan is being processed.');
    }

    public function getLoanHandlingConfirm(string $token)
    {
        $model = LoanApplication::where('handling_confirmation_token', $token)
            ->where('status', Application::STATUS_NEW)
            ->where('required_documents_uploaded', true)
            ->first();

        if (! $model) {
            abort(404);
        }

        $application = new Application($model);
        $application->setHandlingConfirmedAt();

        return redirect()->route('account.loans.show', ['id' => $application->getId()])->with('success', 'Loan confirmed. Your Loan is being processed.');
    }

    public function getLoanHandlingReject(string $token)
    {
        $model = LoanApplication::where('handling_confirmation_token', $token)
            ->where('status', Application::STATUS_NEW)
            ->where('required_documents_uploaded', true)
            ->first();

        if (! $model) {
            abort(404);
        }

        $application = new Application($model);
        $application->setHandlingRejectedAt();
        $application->reject();

        return redirect()->route('account.home')->with('success', 'Loan rejected.');
    }
}