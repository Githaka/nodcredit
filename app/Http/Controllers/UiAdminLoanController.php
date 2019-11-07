<?php

namespace App\Http\Controllers;

use App\Events\LoanApplicationDeleted;
use App\LoanDocument;
use App\LoanPayment;
use App\Mail\Loan\ConfirmNewAmountMail;
use App\Mail\Loan\ManuallyConfirmNewAmountMail;
use App\Mail\LoanMoneyTransfer;
use App\NodCredit\Account\Collections\LocationCollection;
use App\NodCredit\Account\Policies\LoanPaymentPolicy;
use App\NodCredit\Account\Policies\PartPaymentPolicy;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\ApplicationRepaymentPlan;
use App\NodCredit\Loan\Exceptions\ApplicationStatusChangeException;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\MessageSender;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Events\SendMessage;
use App\LoanApplication;
use App\LoanRange;
use App\LoanType;
use App\LoanDocumentType;
use App\Mail\LoanEmailNotification;
use App\Message;
use Illuminate\Http\Request;
use App\Setting;
use App\User;
use App\Events\OnLoanPaymentMade;
use App\Mail\LoanPaymentMade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class UiAdminLoanController extends AdminController
{


    public function dashboard()
    {

        $disbursedPlusRollover = DB::table('loan_applications')
            ->select([
                DB::raw('IF(CEIL(datediff(`updated_at`, `paid_out`) / 30) <= 1, 1, CEIL(datediff(`updated_at`, `paid_out`) / 30)) * `amount_approved` as `total`'),
            ])
            ->whereIn('status', [Application::STATUS_APPROVED, Application::STATUS_COMPLETED])
            ->whereNotNull('paid_out')
        ;

        $totalDisbursedPlusRollover = $disbursedPlusRollover->get()->sum('total');

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'totalPaid' => LoanPayment::paid()->sum('amount'),
            'totalPaidOut' => LoanApplication::whereNotNull('paid_out')->sum('amount_approved'),
            'totalDisbursedLoans' => LoanApplication::whereNotNull('paid_out')->count(),
            'totalCompletedLoans' => LoanApplication::where('status', Application::STATUS_COMPLETED)->count(),
            'totalRejectedLoans' => LoanApplication::where('status', Application::STATUS_REJECTED)->count(),
            'totalDisbursedPlusRollover' => $totalDisbursedPlusRollover
        ]);
    }

    public function loans(Request $request)
    {
        $user = $request->user();

        // handle deletion of loan
        if($request->input('deleteLoan'))
        {
            $loanToDelete = LoanApplication::findOrFail($request->input('deleteLoan'));
            $hasAnyPayment = $loanToDelete->payments()->where('status', 'paid')->count();
            if($hasAnyPayment > 0)
            {
                return back()->with('error', 'This loan has received some payments, you should mark it as completed instead');
            }

              $loanToDelete->status = 'rejected';
              $loanToDelete->save();

           event(new LoanApplicationDeleted($loanToDelete));


            $user->loanActions()->create(['loan_application_id' => $loanToDelete->id, 'action' => 'Admin deleted loan', 'finger_print' => $request->ip()]);

            return back()->with('success', 'Loan marked rejected and all scheduled payments deleted.');
        }


        $filterByOwner = $request->input('owner');
        $filterByStatus = $request->input('status');
        $filterByLoanType = $request->input('loan_type');
        $filterByDateFrom = $request->input('date_from');
        $filterByReadiness = $request->input('readiness');

        $builder = (new LoanApplication)->newQuery();

        if ($filterByOwner) {
            $builder->whereHas('owner', function($query) use ($filterByOwner) {
                $query->where('name', 'like', '%'.$filterByOwner.'%');
            });
        }

        if ($filterByStatus) {
            $builder->where('status', $filterByStatus);
        }

        if ($filterByLoanType) {
            $builder->where('loan_type_id', $filterByLoanType);
        }

        if ($filterByDateFrom) {
            $builder->whereDate('created_at', '>=', $filterByDateFrom);
        }

        if ($filterByReadiness === 'ready_for_approval') {
            $builder->where('status', Application::STATUS_NEW);
            $builder->where('required_documents_uploaded', true);

            $builder->whereHas('owner', function($query) {
                $query
                    ->whereNotNull('name')
                    ->whereNotNull('bank')
                    ->whereNotNull('account_number')
                ;

                $query->whereHas('card');
                $query->whereHas('works');
            });
        }
        else if ($filterByReadiness === 'pay_out') {
            $builder->where('status', Application::STATUS_APPROVED);
            $builder->whereNull('paid_out');
        }
        else if ($filterByReadiness === 'paid_out') {
            $builder->whereNotNull('paid_out');
        }

        $loans = $builder->with('loanType', 'documents', 'owner')->latest()->paginate(10);

        $loans->appends([
            'owner' => $filterByOwner,
            'status' => $filterByStatus,
            'loan_type' => $filterByLoanType,
            'date_from' => $filterByDateFrom,
            'readiness' => $filterByReadiness
        ]);

        $downloadLink = route('mainframe.loans.download', [
            'owner' => $filterByOwner,
            'status' => $filterByStatus,
            'loan_type' => $filterByLoanType,
            'date_from' => $filterByDateFrom,
            'readiness' => $filterByReadiness
        ]);

        return view('admin.loans')
            ->with('loans', $loans)
            ->with('totalLoans', LoanApplication::count())
            ->with('loanTypes', LoanType::get())
            ->with('title', 'Loans')
            ->with('downloadLink', $downloadLink)
            ;
    }

    public function downloadLoans(Request $request)
    {
        $filterByOwner = $request->input('owner');
        $filterByStatus = $request->input('status');
        $filterByLoanType = $request->input('loan_type');
        $filterByDateFrom = $request->input('date_from');
        $filterByReadiness = $request->input('readiness');

        $builder = (new LoanApplication)->newQuery();

        if ($filterByOwner) {
            $builder->whereHas('owner', function($query) use ($filterByOwner) {
                $query->where('name', 'like', '%'.$filterByOwner.'%');
            });
        }

        if ($filterByStatus) {
            $builder->where('status', $filterByStatus);
        }

        if ($filterByLoanType) {
            $builder->where('loan_type_id', $filterByLoanType);
        }

        if ($filterByDateFrom) {
            $builder->whereDate('created_at', '>=', $filterByDateFrom);
        }

        if ($filterByReadiness === 'ready_for_approval') {
            $builder->where('status', Application::STATUS_NEW);
            $builder->where('required_documents_uploaded', true);

            $builder->whereHas('owner', function($query) {
                $query
                    ->whereNotNull('name')
                    ->whereNotNull('bank')
                    ->whereNotNull('account_number')
                ;

                $query->whereHas('card');
                $query->whereHas('works');
            });
        }
        else if ($filterByReadiness === 'pay_out') {
            $builder->where('status', Application::STATUS_APPROVED);
            $builder->whereNull('paid_out');
        }
        else if ($filterByReadiness === 'paid_out') {
            $builder->whereNotNull('paid_out');
        }

        $loans = $builder->with('loanType', 'documents', 'owner')
            ->latest()
            ->get();


        $filepath = storage_path('loans-' . time() . '.csv');
        $file = fopen($filepath, 'w');

        // Headers
        fputcsv($file, ['Amount', 'Name', 'Email', 'Phone', 'Date', 'Status', 'Loan Type']);

        // Data
        foreach ($loans as $loan) {
            fputcsv($file, [
                $loan->amount(),
                $loan->owner ? str_replace(',', ' ', $loan->owner->name) : '',
                $loan->owner ? $loan->owner->email : '',
                $loan->owner ? $loan->owner->phone : '',
                $loan->created_at,
                $loan->status,
                $loan->loanType ? $loan->loanType->name : ''
            ]);
        }

        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function getLoanJson(Request $request, $id)
    {
        $loan = LoanApplication::with('documents', 'loanType', 'owner')->findOrFail($id);

        return response()->json([
            'loan' => $loan
        ]);
    }

    public function postSendNewAmount(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:10000'
        ]);

        $application = Application::find($id);

        $amount = (float) $request->get('amount');

        try {
            if ($application->setAmountAllowed($amount)) {
                $application->waiting();
            }
        }
        catch (ApplicationStatusChangeException $exception) {
            return response()->json([
                'errors' => [
                    'status' => [
                        $exception->getMessage()
                    ]
                ]
            ], 422);
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'status' => ['Something went wrong. Please, try again later or contact administrator.']
                ]
            ], 422);
        }

        MessageSender::send('loan-application-manually-confirm-new-amount', $application->getAccountUser(), [
            '#LOAN_AMOUNT_CONFIRM_URL#' => route('account.loans.amount-confirm-manually', ['id' => $application->getId()]),
            '#LOAN_AMOUNT_REJECT_URL#' => route('account.loans.amount-reject', ['id' => $application->getId()]),
            '#LOAN_AMOUNT_ALLOWED#' => 'N' . number_format($application->getAmountAllowed())
        ]);

        return response()->json([
            'success' => true
        ]);
    }


    public function viewLoan(Request $request, $id)
    {
        $user = $request->user();
        $loan = LoanApplication::with('documents', 'loanType', 'owner')->findOrFail($id);

        $application = new Application($loan);

        $documentTypes = LoanDocumentType::get();
        // handle deletion of document
        if($request->input('deleteDocument') && $loan->status == 'new')
        {
            $documentToDelete = $loan->documents()->findOrFail($request->input('deleteDocument'));

            \Storage::disk('documents')->delete($documentToDelete->path);
            $documentToDelete->delete();

            $application->refreshHasRequiredUploadedDocuments();

            $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Admin deleted document #' . e($documentToDelete->description), 'finger_print' => $request->ip()]);

            return redirect()->route('mainframe.loans.show', $id)->with('success', 'Document deleted');
        }

        $application->refreshHasRequiredUploadedDocuments();

        if($request->input('changeStatus') && $request->input('loan'))
        {

            if(in_array($loan->status, ['approved', 'completed', 'rejected']))
            {
                return redirect()->route('mainframe.loans.show', $id)->with('error', 'You can not change the status of this loan.');
            }

            if($request->input('changeStatus') == 'approved')
            {
                return redirect()->route('mainframe.loans.approval', $id);
            }


            $newLoan = LoanApplication::findOrFail($request->input('loan'));

            if($newLoan->status != $request->input('changeStatus'))
            {
                $newLoan->status = $request->input('changeStatus');
                $newLoan->save();

                $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Admin changed status to #' . e($newLoan->status), 'finger_print' => $request->ip()]);

                if($newLoan->status === 'rejected')
                {
                    $newLoan->payments()->delete();
                    event(new LoanApplicationDeleted($newLoan));
                }
                else
                {
                    MessageSender::send('loan-application-status-notification', $application->getAccountUser(), [
                        '#LOAN_STATUS#' => $newLoan->status
                    ]);
                }

                return redirect()->route('mainframe.loans.show', $id)->with('success', 'Loan status changed');
            }
            else
            {
                return redirect()->route('mainframe.loans.show', $id)->with('success', 'Loan status changed');
            }

        }

        // force document download
        if($request->input('downloadDocument'))
        {
            $documentToDownload = $loan->documents()->findOrFail($request->input('downloadDocument'));
            $fullPath = \Storage::disk('documents')->getDriver()->getAdapter()->applyPathPrefix($documentToDownload->path);
            return response()->download($fullPath);
        }

        return view('admin.loan-view')
            ->with('loan', $loan)
            ->with('user', $loan->owner)
            ->with('documentTypes', $documentTypes)
            ->with('loanActions', $loan->loanActions()->with('user')->latest()->get())
            ->with('documentInfo', (object)$loan->getUploadedDocumentTypeInfo())
            ->with('title', 'View loan')
            ->with('locations', $application->getAccountUser()->getLocations())
            ;
    }

    /**
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getLoanDocumentDownload(string $id)
    {
        $document = LoanDocument::find($id);

        if (! $document) {
            abort(404);
        }

        $fullPath = Storage::disk('documents')->getDriver()->getAdapter()->applyPathPrefix($document->path);

        return response()->download($fullPath);
    }

    public function payments(Request $request)
    {

        $user = $request->user();

        // mark as paid
        if($request->input('markAsPaid') AND $user->isAdmin())
        {

            $payment = LoanPayment::where('status', '!=', 'paid')->findOrFail($request->input('markAsPaid'));
            $payment->status = 'paid';
            $payment->updated_at = now();
            $payment->payment_info = 'Marked as paid by ' . $user->signature();
            $payment->save();
            event(new OnLoanPaymentMade($payment, 'Marked as paid by ' . e($user->name)));
            $admins = User::where('role', 'admin')->get();
            Mail::to($payment->loan->owner)
                ->bcc($admins)
                ->send(new LoanPaymentMade($payment));

            // check for completed loan repayment
            $payment->loan->checkForCompletedLoanRepayment();

            return redirect()->route('mainframe.payments')->with('success', 'Marked as paid and email sent to customer');
        }

        // when admin click on Bill Customer, charge the users account
        if($request->input('billPayment') AND $user->isAdmin())
        {
            $payment = LoanPayment::where('status', '!=', 'paid')->findOrFail($request->input('billPayment'));
            $res = LoanPayment::chargeWithPayStack($payment);
            $user->loanActions()->create(['loan_application_id' => $payment->loan->id, 'action' => 'Admin triggered loan re-payment', 'finger_print' => $request->ip()]);
            if($res === 'ok')
            {
                return redirect(route('mainframe.payments'))->with('success', 'Customer Card Billed');
            }

            return redirect(route('mainframe.payments'))->with('error', 'ERROR: ' . e($res));
        }


        if($request->input('removePayment') AND $user->isAdmin()) {

            $payment = LoanPayment::findOrFail($request->input('removePayment'));
            if($payment->isOverdue() && !$payment->loan->paid_out)
            {
                $payment->delete();
                return redirect(route('mainframe.payments'))->with('success', 'Removed.');
            }
        }

        $filterByOverduePast = $request->get('overdue_past');
        $filterByStatus = $request->input('status');
        $filterByDueIn = $request->input('due_in');
        $filterByQuery = $request->input('q');

        $perPage = $request->input('per_page') ? abs(intval($request->input('per_page'))) : 30;
        $filter = (new LoanPayment)->newQuery();

        // filter by status
        if ($filterByStatus) {
            $filter->where('status', $filterByStatus);
        }

        // filter by days
        if ($filterByOverduePast > 0) {
            $past = now()->subDays($request->get('overdue_past'))->startOfDay();

            $filter->whereBetween('due_at', [$past, now()->subDay()->endOfDay()]);
        }
        else if ($filterByDueIn !== null) {
            $startOfTheDay = now()->addDays($filterByDueIn)->startOfDay();
            $endOfTheDay = now()->addDays($filterByDueIn)->endOfDay();

            $filter->whereBetween('due_at', [$startOfTheDay, $endOfTheDay]);
        }

        //filter by query - customer name, email, or phone number
        if ($filterByQuery) {
            $filter->whereHas('loan.owner', function($query) use ($filterByQuery) {
                $query
                    ->where('name', 'like', '%' . $filterByQuery .  '%')
                    ->orWhere('email', 'like', '%' . $filterByQuery .  '%')
                    ->orWhere('phone', 'like', '%' . $filterByQuery .  '%');
            });
        }
        else {
            $filter->whereHas('loan.owner');
        }

        $payments = $filter->with('loan.owner')
            ->whereHas('loan', function($q)
            {
                $q->where('status', LoanPayment::STATUS_APPROVED)
                 ->whereNotNull('paid_out');
            })->orderBy('due_at')->paginate($perPage);

        $payments->appends([
            'overdue_past' => $filterByOverduePast,
            'status' => $filterByStatus,
            'due_in' => $filterByDueIn,
            'q' => $filterByQuery
        ]);

        $downloadLink = route('mainframe.payments.download', [
            'overdue_past' => $filterByOverduePast,
            'status' => $filterByStatus,
            'due_in' => $filterByDueIn,
            'q' => $filterByQuery
        ]);

        return view('admin.payments')
                    ->with('payments', $payments)
                    ->with('title', 'Payments')
                    ->with('downloadLink', $downloadLink)
            ;
    }

    public function downloadPayments(Request $request)
    {

        $filterByOverduePast = $request->get('overdue_past');
        $filterByStatus = $request->input('status');
        $filterByDueIn = $request->input('due_in');
        $filterByQuery = $request->input('q');

        $builder = (new LoanPayment)->newQuery();

        // filter by status
        if ($filterByStatus) {
            $builder->where('status', $filterByStatus);
        }

        // filter by days
        if ($filterByOverduePast > 0) {
            $past = now()->subDays($request->get('overdue_past'))->startOfDay();

            $builder->whereBetween('due_at', [$past, now()->subDay()->endOfDay()]);
        }
        else if ($filterByDueIn !== null) {
            $startOfTheDay = now()->addDays($filterByDueIn)->startOfDay();
            $endOfTheDay = now()->addDays($filterByDueIn)->endOfDay();

            $builder->whereBetween('due_at', [$startOfTheDay, $endOfTheDay]);
        }

        //filter by query - customer name, email, or phone number
        if ($filterByQuery) {
            $builder->whereHas('loan.owner', function($query) use ($filterByQuery) {
                $query
                    ->where('name', 'like', '%' . $filterByQuery .  '%')
                    ->orWhere('email', 'like', '%' . $filterByQuery .  '%')
                    ->orWhere('phone', 'like', '%' . $filterByQuery .  '%');
            });
        }
        else {
            $builder->whereHas('loan.owner');
        }

        $payments = $builder->with('loan.owner')
            ->whereHas('loan', function($query) {
                $query
                    ->where('status', LoanPayment::STATUS_APPROVED)
                    ->whereNotNull('paid_out');
            })
            ->orderBy('due_at')
            ->get();

        $filepath = storage_path('payments-' . time() . '.csv');
        $file = fopen($filepath, 'w');

        // Headers
        fputcsv($file, ['Amount', 'Interest', 'Name', 'Email', 'Phone', 'Due Date', 'Status', 'Month']);

        // Data
        foreach ($payments as $payment) {
            $loan = $payment->loan;

            fputcsv($file, [
                'NGN' . number_format($payment->amount,2),
                'NGN' . number_format($payment->interest,2),
                $loan->owner ? str_replace(',', ' ', $loan->owner->name) : '',
                $loan->owner ? $loan->owner->email : '',
                $loan->owner ? $loan->owner->phone : '',
                $payment->due_at,
                $payment->status,
                $payment->payment_month
            ]);
        }

        return response()->download($filepath)->deleteFileAfterSend();
    }


    public function showPayment(Request $request, $id)
    {

        $payment = LoanPayment::whereHas('loan.owner')->with('loan.owner')->orderBy('due_at')->findOrFail($id);

        if ($request->isXmlHttpRequest()) {
            return response()->json([
                'payment' => $payment,
                'payment_json' => (new Payment($payment))->transform(),
            ]);
        }

        $loanApplication = new Application($payment->loan()->first());

        return view('admin.payment-view', [
            'payment' => $payment,
            'documents' => $loanApplication->getDocuments(),
            'title' => 'View payment',
        ]);

    }

    public function setDueDate(Request $request, $id)
    {
       $request->validate([
           'new_date' => [
                    'required',
                    'date_format:Y-m-d',
                    function ($attribute, $value, $fail) {
                        $newDate = Carbon::createFromFormat('Y-m-d', $value);
                       if ($newDate->isPast()) {
                           $fail('Please select a date in the future.');
                         }
                    },
           ]
       ]);

        $payment = LoanPayment::findOrFail($id);
        if($payment->status !== 'scheduled')
        {
            return back()->with('error', 'The payment status must be "scheduled" before you can set a new date.');
        }

        $newDate = Carbon::createFromFormat('Y-m-d', $request->input('new_date'));
        $newDate->endOfDay();

        $payment->due_at = $newDate;
        $payment->save();

        return back()->with('success', 'New due date set to ' .  e($payment->due_at));

    }


    public function loanApproval(Request $request, $id)
    {
        $loan = LoanApplication::with('documents', 'loanType', 'owner')->findOrFail($id);

        if(in_array($loan->status, ['approved', 'completed']))
        {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'You can not change the status of this loan');
        }

        $info = $loan->getUploadedDocumentTypeInfo();
        if($info['required'] !== $info['uploadedRequired'])
        {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'You need to upload all required documents before you can approve the loan.');
        }

        if($loan->owner->checkList())
        {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'Loan check list not completed.');
        }

        $loanRange = LoanRange::getByAmount($loan->amount_requested);
        if(!$loanRange) {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'Loan not within the loan range we have in database.');
        }


        return view('admin.loan-approval')
                    ->with('loan', $loan)
                    ->with('loanRange', $loanRange)
                    ->with('interestRate', Setting::v('default_interest_rate'))
                    ->with('title', 'Loan Approval');
    }

    public function loanApprovalStore(Request $request,$id)
    {
        $user = $request->user();
        $loan = LoanApplication::with('owner')->where('status', '!=', 'approved')->findOrFail($id);
        if(in_array($loan->status, ['approved', 'completed']))
        {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'You can not change the status of this loan');
        }

        $info = $loan->getUploadedDocumentTypeInfo();
        if($info['required'] !== $info['uploadedRequired'])
        {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'You need to upload all required documents before you can approve the loan.');
        }

        if($loan->owner->checkList())
        {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'Loan check list not completed.');
        }

        $loanRange = LoanRange::getByAmount($loan->amount_requested);
        if(!$loanRange) {
            return redirect()->route('mainframe.loans.show', $loan->id)->with('error', 'Loan not within the loan range we have in database.');
        }

        $amount = doubleval($request->input('amount'));
        $inputRate = doubleval($request->input('interest_rate'));
        $inputTenor = intval($request->input('tenor'));


        // Add log if values were changed
        if(($amount >  $loan->amount_requested || $amount < $loan->amount_requested) ||
            ($inputRate < $loan->interest_rate || $inputRate > $loan->interest_rate) ||
            ($inputTenor !== $loan->tenor)
        )
        {
            $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Admin adjusted loan', 'finger_print' => $request->ip()]);
        }

        // Re-generate the payments
        $payments = LoanApplication::buildMonthlyPayment($amount,$inputRate, $inputTenor);

        $loan->payments()->delete(); // clear old payments

        foreach($payments as $item)
        {
            $loan->payments()->create([
                'amount' => $item['amount'],
                'due_at' => now()->addMonths($item['month'])->endOfDay(),
                'payment_month' => $item['month'],
                'interest' => $item['interest']
            ]);
        }

        $loan->tenor = $inputTenor;
        $loan->interest_rate = $inputRate;
        $loan->amount_approved = $amount;
        $loan->approved_at = now();
        $loan->status = Application::STATUS_APPROVED;
        $loan->save();

        $bcc = config('nodcredit.mail_to.loan_approved');

        Mail::to($loan->owner)
                ->bcc($bcc)
                ->send(new LoanEmailNotification($loan->owner, $loan));

        $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Admin approved loan', 'finger_print' => $request->ip()]);

        $user->getScoreInfo('LOAN_APPROVED', $loan->amount_approved);

        return redirect()->route('mainframe.loans.show', $id)->with('success', 'Loan Approved and Payment plan created.');


    }

    public function loanPayments(Request $request, $id)
    {
        $loan = LoanApplication::with('documents', 'loanType', 'owner')->findOrFail($id);

        if($loan->paid_out) return redirect()->route('mainframe.loans.show', $id)->with('error', 'Loan already paid to customer on ' . e($loan->paid_out));

        if($loan->status !== 'approved') return redirect()->route('mainframe.loans.show', $id)->with('error', 'Loan must be approved before payout');

        $investors = User::investors()->pluck('id', 'name')->toArray();
        $recipient = User::find($loan->owner->id);

        return view('admin.loan-payments')
            ->with('loan', $loan)
            ->with('investors', $investors)
            ->with('recipient', $recipient)
            ->with('canBePaid', $recipient->paymentInfo())
            ->with('title', 'Loan Payments');
    }

    public function transferPayments(Request $request, $id)
    {

        $user = $request->user();
        $loan = LoanApplication::with('owner')->findOrFail($id);
        $application = new Application($loan);

        if($loan->status !== 'approved')
        {
            return back()->with('error', 'You can not transfer fund for the loan with status of : ' . e($loan->status));
        }

        if($loan->paid_out)
        {
            return back()->with('error', 'Loan already paid out. Please check PayStack for transaction');
        }

        if(!\Hash::check($request->input('password'), $request->user()->password))
        {
            return back()->with('error', 'Password is not valid');
        }

        $investor = null;
        if($request->input('investor'))
        {
            $investor = User::investors()->find($request->input('investor'));
            if(!$investor) return back()->with('error', 'This is not an investor account.');
        }

        // create transfer recipient
        // transfer fund

        $recipient = User::find($loan->owner->id);
        $paymentInfo = $recipient->paymentInfo();

        if(!$paymentInfo)
        {
            return back()->with('error', 'Payment information not correct. Check to make sure bank account number, name and recipient is correct');
        }

        // create recipient
        $createPayload = [
            'type' => 'nuban',
            'name' => $paymentInfo['name'],
            'description' => 'Nod credit loan transfer',
            'account_number' => $paymentInfo['account_number'],
            'bank_code' => $paymentInfo['bank']->code,
            'currency' => 'NGN',
            'metadata' => ['uinscope' => $request->user()->id, 'stime' => now()]
        ];
        $createRecipientReq = makePaystackPostRequest('https://api.paystack.co/transferrecipient', $createPayload);

        if($createRecipientReq['status'] == 'ok' && $createRecipientReq['data']->status)
        {

            $recipientCode = $createRecipientReq['data']->data->recipient_code;
            $recipient->save();

            $transferPayload = [
                'source' => 'balance',
                'reason' => $createPayload['description'],
                'amount' => doubleval($loan->amount_approved * 100),
                'recipient' =>$recipientCode
            ];
            $transferRequest = makePaystackPostRequest('https://api.paystack.co/transfer', $transferPayload);
            if(!$transferRequest['data']->status) return back()->with('error', 'PayStack Error: ' . e($transferRequest['data']->message));

            $loan->paid_out  = now();
            $loan->approved_by = $request->user()->id;
            $loan->investor = $investor ? $investor->id : null;
            $loan->save();

            $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Money transferred to your account', 'finger_print' => $request->ip()]);

            MessageSender::send('loan-application-money-transferred', $application->getAccountUser(), [
                '#LOAN_AMOUNT_APPROVED#' => 'N' . number_format($application->getAmountApproved(),2),
                '#LOAN_REPAYMENT_PLAN#' => ApplicationRepaymentPlan::generateHtmlTable($application)
            ]);

            return redirect()->route('mainframe.loans.show', $id)->with('success', $transferRequest['data']->message);
        }
        else
        {
            return back()->with('error', 'PayStack error: ' . e($createRecipientReq['data']->message));
        }
    }

    public function getPartPayments(string $id)
    {
        try {
            $payment = Payment::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        $parts = $payment->getParts();

        return response()->json([
            'parts' => $parts,
            'payment' => $payment->getModel()
        ]);
    }

    public function postPartPaymentAdd(Request $request, string $id, \App\NodCredit\Account\User $accountUser)
    {
        try {
            $payment = Payment::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        $this->validate($request, [
            'amount' => 'required|integer|min:1|max:' . $payment->getAmount(),
        ]);

        if (! PartPaymentPolicy::canCreate($accountUser)) {
            return response()->json([
                'errors' => ['error' => ['You have no permission.']]
            ], 422);
        }

        $amount = $request->get('amount');

        $payment->createPartPaymentAndDeductAmount($amount);

        return response()->json([
            'success' => true
        ]);
    }

    public function postPaymentIncreaseAmount(Request $request, string $id, \App\NodCredit\Account\User $accountUser)
    {
        try {
            $payment = Payment::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        $this->validate($request, [
            'value' => 'required|integer|min:1',
            'type' => [
                'required',
                Rule::in(['fixed', 'percent'])
            ]
        ]);

        if (! LoanPaymentPolicy::canIncreaseAmount($accountUser)) {
            return response()->json([
                'errors' => ['error' => ['You have no permission.']]
            ], 422);
        }

        try {
            $payment->increaseAmountBy($request->get('value'), $request->get('type'));
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'error' => ['Error, please, try again.']
                ]
            ], 422);
        }

        return response()->json([
            'success' => true,
            'payment' => $payment->getModel()
        ]);
    }

    public function postPaymentPausePenalty(Request $request, \App\NodCredit\Account\User $accountUser, string $id)
    {
        try {
            $payment = Payment::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }


        if ($date = $request->get('date')) {
            try {
                $until = Carbon::createFromFormat('Y-m-d', $date);

                $payment->pausePenaltyUntil($until, $accountUser);

            }
            catch (\Exception $exception) {
                return response()->json([
                    'errors' => [
                        'error' => ['Please, check your inputs.']
                    ]
                ], 422);
            }
        }
        else {
            $payment->resetPenaltyPause();
        }

        return response()->json([
            'success' => true,
            'payment_json' => $payment->transform()
        ]);
    }

}
