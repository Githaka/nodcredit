<?php

namespace App\Http\Controllers;

use App\Events\InvestmentLiquidated;
use App\Events\UserUpdatedProfile;
use App\Mail\InvestmentLiquidateEmail;
use App\Mail\MessageSenderMail;
use App\NodCredit\Account\User as AccountUser;
use App\NodCredit\Account\Validators\NameMatching;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Factories\InvestmentFactory;
use App\NodCredit\Investment\Notifications\InvestmentAdded;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Document;
use App\NodCredit\Loan\Exceptions\ApplicationHasNoPaymentException;
use App\NodCredit\Loan\Exceptions\ApplicationPauseException;
use App\NodCredit\Loan\Exceptions\CardDoesNotBelongsToUserException;
use App\NodCredit\Loan\Exceptions\PaymentChargeException;
use App\NodCredit\Loan\Exceptions\UserHasNoCardException;
use App\NodCredit\Loan\LoanPause;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Loan\PaymentCharge;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Settings;
use App\NodLog;
use App\Paystack\PaystackApi;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Mail;
use App\Events\NewLoanApplication;
use App\Events\SendMessage;
use App\LoanPayment;
use App\LoanPaymentState;
use App\TempPayment;
use App\TransactionLog;
use Illuminate\Support\Facades\Hash;
use App\Bank;
use App\LoanApplication;
use App\LoanType;
use App\UserCard;
use App\LoanDocumentType;
use App\LoanDocument;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use App\Setting;
use Validator;
use App\User;
use App\LoanRange;
use App\Message;


class UiAccountController extends Controller
{
    public function index(Request $request, AccountUser $accountUser)
    {

        if(auth()->user()->isPartner()) {
            return redirect(auth()->user()->partnerUrl());
        }

        $totalRecieved = $request->user()->applications->whereIn('status', ['approved', 'completed'])
                                                        ->sum('amount_approved');

        $totalPaid = 0;

        foreach($request->user()->applications() as $application)
        {
            foreach($application->payments()->where('status', 'paid')->get() as $item)
            {
                $totalPaid += $item->amount;
            }
        }

        $completedApplicationsCount = $request->user()->applications()->where('status', Application::STATUS_COMPLETED)->count();
        $rejectedApplicationsCount = $request->user()->applications()->where('status', Application::STATUS_REJECTED)->count();

        $customers = User::whereIn('role', ['user', 'partner'])->count();

        $investors = User::where('role', 'partner')->count();

        return view('account.index', [
            'totalRecieved' => $totalRecieved,
            'totalPaid' => $totalPaid,
            'customers' => $customers,
            'investors' => $investors,
            'title' => 'Account',
            'completedApplicationsCount' => $completedApplicationsCount,
            'rejectedApplicationsCount' => $rejectedApplicationsCount,
            'accountUser' => $accountUser
        ]);
    }

    public function logout()
    {
        \Auth::logout();
        return redirect()->route('login');
    }

    public function profile(Request $request, AccountUser $accountUser)
    {

        /** When the user click on remove card, check if this is not the only card before removing it */
        if(request('removeCard') && request('card'))
        {
            // Defaulter can`t remove a card
            if ($accountUser->isDefaulter()) {
                return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'You can not remove a card.');
            }

            /** @var UserCard $card */
            $card = auth()->user()->cards()->find(request('card'));

//            // Deny to delete last valid card
//            if ($accountUser->countValidCard() <= 1 AND $card->isValid()) {
//                return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'You can not remove this card. Add a new one and try again.');
//            }

            $cardsCount = auth()->user()->cards()->count();

            if($cardsCount <= 1)
            {
                return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'You can not remove this card. Add a new one and try again.');
            }

            if(!$card)
            {
                return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'Card not found.');
            }

            NodLog::write(auth()->user(), 'Card deleted', json_encode($card->toArray()));

            $card->delete();

            auth()->user()->getScoreInfo('USER_REMOVED_CARD', auth()->user()->cards()->count());

            event(new UserUpdatedProfile($accountUser->getModel()));

            return redirect()->route('account.profile', ['#tab-billing'])->with('success', 'Card deleted.');
        }


        $banks = Bank::orderBy('name')->get();


        return view('account.profile', [
            'banks' => $banks,
            'title' => 'Profile',
            'accountUser' => $accountUser,
            'user' => $accountUser->getModel()
        ]);
    }

    public function profileProcess(Request $request, AccountUser $accountUser)
    {

        // Defaulter can`t change information
        if ($accountUser->isDefaulter()) {
            return back()->with('error', 'You can not change Account settings');
        }

        $user = $request->user();

        if ($user->isPartner()) {
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => [
                    'required',
                    'min:10',
                    'max:14',
                    Rule::unique('users')->ignore($user->id),
                ],
                'gender' => [
                    'required',
                    Rule::in(['male', 'female', 'others']),
                ],
            ]);

            $data = [
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'dob' => $request->get('dob'),
                'newsletter' => $request->input('newsletter') ? now() : null,
                'track_usage' => $request->input('track_usage') ? now() : null,
                'gender' => $request->input('gender'),
            ];
        }
        else {
            $request->validate([
                'gender' => [
                    'required',
                    Rule::in(['male', 'female', 'others']),
                ],
            ]);

            $data = [
                'newsletter' => $request->input('newsletter') ? now() : null,
                'track_usage' => $request->input('track_usage') ? now() : null,
                'gender' => $request->input('gender'),
            ];
        }



        $user->update($data);

        $user->save();

        event(new UserUpdatedProfile($user));

        return back()->with('success', 'Account updated');
    }

    public function profileUpdateBank(Request $request, PaystackApi $paystackApi, AccountUser $accountUser)
    {
        // Defaulter can`t change information
        if ($accountUser->isDefaulter()) {
            return response()->json([
                'errors' => [
                    ['You can not change Bank Account information']
                ]
            ], 422);
        }

        $user = $request->user();

        if ($user->isPartner()) {
            $request->validate([
                'bvn' => [
                    'required',
                    Rule::unique('users')->ignore($user->id),
                ],
                'account_number' => [
                    'min:10',
                    'max:10',
                    Rule::unique('users')->ignore($user->id),
                ],
                'bank' => 'required|exists:banks,id',
            ]);

            $data = $request->only(['account_number', 'bank', 'bvn']);
        }
        else {
            $request->validate([
                'account_number' => [
                    'required',
                    'min:10',
                    'max:10',
                    Rule::unique('users')->ignore($user->id),
                ],
                'bank' => 'required|exists:banks,id',
            ]);

            $data = $request->only(['account_number', 'bank']);
        }



        $bank = Bank::find($request->get('bank'));

        try {
            $response = $paystackApi->resolveAccountNumber($request->get('account_number'), $bank->code);
        }
        catch (ClientException $exception) {
            $response = null;
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    ['Network error. Please, try again.']
                ]
            ], 422);
        }

        if ($response AND $response->status) {
            if (! NameMatching::validate($user->name, $response->data->account_name)) {
                return response()->json([
                    'errors' => [
                        ['Bank Account Number does not belongs to Account Name. Please, check your inputs.']
                    ]
                ], 422);
            }
        }

        $user->update($data);

        event(new UserUpdatedProfile($user));

        $request->session()->flash('success', 'Account updated');
        return ['status' => 'ok', 'message' => 'Account updated'];
    }

    public function checkpaystackTransaction(Request $request, PaystackApi $paystackApi, AccountUser $accountUser)
    {
        $user = $request->user();

        $payment = $request->user()->payments()
                        ->where('status', 'pending')
                        ->where('payment_reference', $request->input('trxref'))->first();
        if(!$payment)
        {
            return redirect()->route('account.profile')->with('error', 'Payment not found.');
        }

        // we have the payment, verify from paystack
        try{
            $response = makePaystackGetRequest("https://api.paystack.co/transaction/verify/" . rawurlencode($payment->payment_reference));
            if($response['status'] !== 'ok') throw new \Exception($response['message']);

            $paystack  = $response['data']->data;

            if($paystack->status !== 'success') {
                $payment->status = 'rejected';
                $payment->save();
                throw new \Exception($paystack->gateway_response);
            }

            // check to make sure the amount is correct
            $recordedAmount = $payment->amount * 100;
            if($paystack->amount < $recordedAmount)
            {
                $payment->status = 'rejected';
                $payment->reason = $paystack->gateway_response;
                $payment->save();
                throw new \Exception('The amount you paid is less than the transaction amount. PAID: N' . number_format($paystack->amount / 100));
            }

            // payment was successful
            // link card
            $payment->status = 'success';
            $payment->save();

            // Check exists card owner
            $existsCard = UserCard::where('bin', $paystack->authorization->bin)
                ->where('last4', $paystack->authorization->last4)
                ->where('exp_month', $paystack->authorization->exp_month)
                ->where('exp_year', $paystack->authorization->exp_year)
                ->where('signature', $paystack->authorization->signature)
                ->withTrashed()
                ->first();

            // If card exists and card belongs to another user
            if ($existsCard AND $existsCard->user_id !== $accountUser->getId()) {

                // User who has active loan should not be banned
                if (! $accountUser->hasActiveLoan()) {

                    $cardOwner = $existsCard->user;

                    // Investor should not be banned if card added exists as user
                    if (! $accountUser->getModel()->isPartner() OR $cardOwner->isUser()) {

                        $accountUser->ban("Adding card data [{$existsCard->id}] which were added by {$cardOwner->name} ({$cardOwner->email}).");

                        throw new \Exception('You can not use this card. Your account is suspended. Please, contact admin.');
                    }
                }
            }

            $cardNumber = sprintf('%s***%s', $paystack->authorization->bin, $paystack->authorization->last4);

            $cardData  = [
                'currency' => $paystack->currency,
                'auth_code' => $paystack->authorization->authorization_code,
                'card_number' => $cardNumber,
                'exp_month' => $paystack->authorization->exp_month,
                'exp_year' => $paystack->authorization->exp_year,
                'card_type' => $paystack->authorization->card_type,
                'brand' => $paystack->authorization->brand,
                'reusable' => $paystack->authorization->reusable,
                'signature' => $paystack->authorization->signature,
                'email' => $paystack->customer->email,
                'bin' => $paystack->authorization->bin,
                'last4' => $paystack->authorization->last4,
                'bank_name' => strtoupper($paystack->authorization->bank),
            ];

            /** @var UserCard $card */
            $card = $user->cards()
                ->where('bin', $paystack->authorization->bin)
                ->where('last4', $paystack->authorization->last4)
                ->where('exp_month', $paystack->authorization->exp_month)
                ->where('exp_year', $paystack->authorization->exp_year)
                ->where('signature', $paystack->authorization->signature)
                ->first()
            ;

            if(!$card)
            {
                $card = $user->cards()->create($cardData);
                if($user->cards()->count())
                {
                    // if this is an additional card, reward the user
                    $user->getScoreInfo('ADDING_ADDITIONAL_CARD');
                }
            }
            else
            {
                // Reset disable status
                $cardData['disabled_at'] = null;
                $cardData['disable_message'] = null;

                $card->update($cardData);
            }

            if( $payment->reason == 'investment')
           {
               $message = 'Account credited';

               $investment = InvestmentFactory::createUsingPayment($payment);

               $investment->publicLog([
                   'text' => 'Investment created and credited',
                   'created_by' => $user->id,
                   'ip' => $request->getClientIp(),
               ]);

               InvestmentAdded::notify($investment, 'all');

           }else {
               $message =  'Payment was successfully.';
           }

            event(new UserUpdatedProfile($user));

           // If user has no a valid card, verify just added card and show error message
            if (! $accountUser->hasValidCard()) {

                // Verify card issuer and user bank
                if (! $card->isMatchingBankName()) {
                    return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'This card does not match your banking institution. Please, add another card.');
                }

                // Verify card expire date
                if (! $card->isValidExpireAt()) {
                    return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'This card expires in less than 3 months. Please, add a new card.');
                }

                // Verify if card is reusable
                if (! $card->isReusable()) {
                    return redirect()->route('account.profile', ['#tab-billing'])->with('error', 'This card is not a valid. Please, add a new card.');
                }
            }

            return redirect()->route('account.profile', ['#tab-billing'])->with('success', $message);

        }catch(\Exception $e)
        {
            return redirect()->route('account.profile', ['#tab-billing'])->with('error', $e->getMessage());
        }

    }

    public function loans(Request $request, AccountUser $accountUser)
    {

        if($request->input('removeLoan'))
        {
            $loanToDelete = $request->user()->applications()->where('status', 'new')->findOrFail($request->input('removeLoan'));
            $loanToDelete->payments()->delete();
            $loanToDelete->delete();
            return back();
        }
        $loans = $request->user()->applications()
                    ->with('loanType', 'documents')
                    ->latest()
                    ->paginate(15);

        return view('account.loans', [
            'loans' => $loans,
            'accountUser' => $accountUser,
            'title' => 'Loans'
        ]);
    }

    public function processWizardLoanForm(Request $request)
    {

        $rules = [
            'email' => 'required|email|unique:users',
            'phone' => 'required|min:10|max:14|unique:users',
            'bvn' => 'required|unique:users',
            'agree' => 'required|accepted'
        ];

        $email = $request->input('email');

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorMessage = '';

            /** @var MessageBag $error */
            foreach ($validator->errors()->all() as $error) {
                $errorMessage .= $error . ' ';
            }

            return ['status' => 'error', 'message' => $errorMessage];
        }

        $respData = getBVNInfoFromPayStack($request->input('bvn'));
        if($respData)
        {

            $phone = formatPhone($request->input('phone'));

            // lets check if we need to continue or not

            $check = User::where('phone', '!=', $phone)->where('bvn', $request->input('bvn'))->first();
            if($check)
            {
                return ['status' => 'error','message' => 'BVN already you in use. You can try to reset your password.'];
            }

            if(User::where('email', $email)->first())
            {
                return ['status' => 'error','message' => 'Email already you in use. You can try to reset your password.'];
            }

            $userAccount = User::where('phone', $phone)
                        ->whereNotNull('dob')
                        ->whereNotNull('bvn')
                        ->first();

            $user = $userAccount ? $userAccount : new User();

            try{
                $user->name = sprintf('%s %s', $respData->first_name, $respData->last_name);
                $user->email = $email;
                $user->phone = $phone;
                $user->bvn_phone  = formatPhone($respData->mobile);
                $user->password = str_random(16);
                $user->bvn = $respData->bvn;
                $user->dob  = $respData->formatted_dob;
                $user->force_change_pwd = true;
                $user->save();

                $user->sendOTP();

                $loanInfo = ['loanAmount' => doubleval($request->input('loanAmount')), 'loanType' => $request->input('loanType'), 'user' => $user->id];
                $request->session()->put('loanInfo', $loanInfo);


            }catch(\Exception $e)
            {
                if($user->id)
                {
                    $user->delete();
                }
                return ['status' => 'error', 'message' => $e->getMessage()];
            }


            return  ['status' => 'ok', 'message' => 'Please provide OTP'];

        }
        else
        {
            return ['status' => 'error', 'message' => 'BVN is not valid'];
        }

    }

    public function viewLoan(Request $request, $id)
    {
        $user = $request->user();
        $loan = $request->user()->applications()->with('documents', 'loanType')->findOrFail($id);

        $documentTypes = LoanDocumentType::get();

        $application = new Application($loan);

        // handle deletion of document
        if($request->input('deleteDocument') && $loan->status == 'new')
        {
            $documentToDelete = $loan->documents()->findOrFail($request->input('deleteDocument'));

            \Storage::disk('documents')->delete($documentToDelete->path);
            $documentToDelete->delete();

            $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Document deleted #' . e($documentToDelete->description), 'finger_print' => $request->ip()]);

            $application->refreshHasRequiredUploadedDocuments();

            return redirect()->route('account.loans.show', $id)->with('success', 'Document deleted');
        }

        $application->refreshHasRequiredUploadedDocuments();

        return view('account.loan-view')
            ->with('loan', $loan)
            ->with('documentTypes', $documentTypes)
            ->with('documentInfo', (object)$loan->getUploadedDocumentTypeInfo())
            ->with('loanActions', $loan->loanActions()->with('user')->latest()->get())
            ->with('title', 'View loan');
    }

    public function uploadLoanDocument(Request $request, $id)
    {
        $loan = null;
        $user = $request->user();
        if($user->role == 'admin')
        {
            $loan = LoanApplication::with('documents', 'loanType')->findOrFail($id);
        }
        else
        {
            $loan = $request->user()->applications()
                ->with('documents', 'loanType')
                ->findOrFail($id);

            if($loan->status !== 'new') {
                return back()->with('error', 'You can not upload document for this loan anymore. The status has changed from new to ' . $loan->status);
            }
        }
        $documentType = LoanDocumentType::find($request->input('documentType'));

        if(!$documentType) return back()->with('error', 'Selected document type is not valid');

        $rules = [
            'file' => 'required|file|max:10000',
            'documentType' => 'required',
        ];

        if($documentType->file_type !== '*')
        {
            $rules = [
                'file' => 'required|file|max:10000|mimes:' . $documentType->file_type,
                'documentType' => 'required'
            ];
        }

        $request->validate($rules);

        // Check if document type has already added to the loan
        $documentExists = LoanDocument::where('loan_application_id', $loan->id)->where('document_type', $documentType->id)->first();

        if ($documentExists) {
            return back()->with('error', "You have already added $documentType->name. If You want to re-upload, please, remove old $documentType->name before.");
        }

        $document = LoanDocument::create([
            'loan_application_id' => $loan->id,
            'path' => '',
            'document_type' => $documentType->id,
            'description' => $documentType->name,
        ]);

        if($request->file('file')->isValid())
        {
            $document->path  = $request->file('file')->store($loan->id, 'documents');
            $document->document_extension = $request->file('file')->extension();

            if ($request->get('unlock_password')) {
                $document->unlock_password = $request->get('unlock_password');
                $document->is_unlocked = false;
            }
            else {
                $document->is_unlocked = true;
            }

            $document->save();

            $nodDocument = new Document($document);

            try {
                $nodDocument->unlock();
            }
            catch (\Exception $exception) {}

            if (! $nodDocument->isUnlocked()) {

                $document->delete();

                return back()->with('error', 'Error while unlocking your document. Please, provide a valid password and re-upload document or contact admin.');
            }

            //TODO: this can move to event handler
            $user->loanActions()
                    ->create([
                            'loan_application_id' => $loan->id,
                            'action' => 'Document uploaded #' . e($documentType->name),
                            'finger_print' => $request->ip()]);

            $message = 'Document uploaded.';

            // Notify user if he has uploaded all required documents
            if ($documentType->is_required AND $loan->hasRequiredDocuments()) {
                $message = "Document uploaded. Your Loan is being processed.";
            }

            return back()->with('success', $message);
        }
        else
        {
            return back()->with('error', 'Unable to upload document');
        }
    }

    public function applyForLoan(Request $request, AccountUser $accountUser)
    {
        if (! $accountUser->canApplyForLoan()) {
            return redirect()->route('account.loans')->with('error', 'You can not apply for a new loan');
        }

        return view('account.loan-apply')
            ->with('loanTypes', LoanType::get())
            ->with('title', 'Apply for loan');
    }

    public  function applyForLoanInit( Request $request) {
        $user = $request->user();

        $loanRange = $user->getUserLoanMinMax();

        return [
            'status' => '00',
            'loanTypes' => LoanType::get(),
            'loanMin' => $loanRange->loan_min,
            'loanMax' => $loanRange->loan_max,
            'sess' => $user->id

        ];
    }

    /**
     * This method valid and create new loan, it returns url of the newly created loan and redirects the user on the client side
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function applyForLoanCreatePost(Request $request, AccountUser $accountUser) {

        if (! $accountUser->canApplyForLoan()) {
            return ['status' => 'false', 'goto' => route('account.profile.apply')];
        }

        $user = $request->user();

        $request['amount'] = doubleval(str_replace(',', '', $request->input('amount')));

        $messages = [
            'uuid' => 'The :attribute field is not valid .',
        ];

        $validator =  Validator::make($request->all(), [
           'amount' => 'required|numeric|min:' . doubleval(Setting::v('loan_min')) .'|max:' . doubleval(Setting::v('loan_max')),
           'loanType' => 'required|uuid'
        ], $messages);

        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        $loanFromSession = TempPayment::where('sess', $user->id)->get();

        if(!count($loanFromSession))
        {
            return ['status' => 'error', 'errors' => 'Data is not valid'];
        }

        $firstLoan = $loanFromSession[0];

        $loan = $request->user()->applications()->create([
            'amount_requested' => $firstLoan->loan_amount,
            'amount_approved' => 0,
            'loan_type_id' => $request->input('loanType'),
            'tenor' => count($loanFromSession),
            'interest_rate' => $request->user()->getInterestRate()
        ]);

        foreach($loanFromSession as $item)
        {
            $loan->payments()->create([
                'amount' => $item->amount,
                'due_at' => now()->addMonths($item->month)->endOfDay(),
                'payment_month' => $item->month,
                'interest' => $item->interest
            ]);
        }

        TempPayment::where('sess', $user->id)->delete();

        event(new NewLoanApplication($loan));

        $user->loanActions()->create(['loan_application_id' => $loan->id, 'action' => 'Loan created', 'finger_print' => $request->ip()]);

        return ['status' => 'ok', 'goto' => route('account.loans.show', $loan->id), 'message' => 'Loan created'];

    }

    /**
     * This endpoint is for pre-building the loan payment plan
     * It accept ajax request passing in amount and returns payment pans
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function applyForLoanInitPost(Request $request) {

        $amount = doubleval(str_ireplace(',','', $request->input('amount')));

        $record = LoanRange::getByAmount($amount);
        if(!$record) {
            return ['status' => 'error', 'message' => 'Amount is not valid'];
        }

        $loanMonth = $request->input('tenor') ?:  $record->pay_month;

        if($loanMonth > $record->max_month || $loanMonth < $record->min_month)
        {
            return ['status' => 'error', 'message' => 'Tenor is not valid'];
        }

        $sess = $request->input('sess');
        TempPayment::where('sess', $sess)->delete();
        $payments = LoanApplication::buildMonthlyPayment($amount, $request->user()->getInterestRate(), $loanMonth);
        foreach($payments as $p)
        {
            TempPayment::create([
                            'interest' => $p['interest'],
                            'loan_amount' => $amount,
                            'amount' => $p['amount'],
                            'sess' => $sess,
                            'month' => $p['month'],
                            'tenor' => doubleval($request->input('tenor'))
                        ]);
        }
        $tempPayments = TempPayment::where('sess', $sess)->get();

        return ['status' => 'ok', 'payments' => $tempPayments, 'range_info' => range($record->min_month, $record->max_month), 'amount' => number_format($amount)];

    }


    public function applyForLoanInitRecalculate(Request $request)
    {

        $sess = $request->input('sess');
        $tempPayments = TempPayment::where('sess', $sess)->get();
        $newValue = $request->input('newValue');
        $newMonth = $request->input('month');
        $loanMin = doubleval(Setting::v('loan_min'));

        $sum =  TempPayment::where('sess', $sess)->sum('amount');

        if($newMonth == $tempPayments[count($tempPayments)-1]->month)
        {
            return ['status' => 'error', 'message' => 'You can not change the last', 'payments' => $tempPayments];
        }

        if($newValue < $loanMin)
        {
            return ['status' => 'error', 'message' => 'Value can not be less than N' . number_format($loanMin), 'payments' => $tempPayments];
        }

        if($newValue > $sum)
        {
            return ['status' => 'error', 'message' => 'Value can not be more than N' . number_format($sum), 'payments' => $tempPayments];
        }


        if(count($tempPayments) == 1)
        {
            return ['status' => 'ok', 'payments' => $tempPayments];
        }


        $balance =  $sum - ($newValue + TempPayment::where('sess', $sess)->where('month', '<', $newMonth)->sum('amount'));

        $remainingMonths = TempPayment::where('sess', $sess)->where('month', '>', $newMonth)->count();

        TempPayment::where('sess', $sess)->where('month', $newMonth)->update(['amount' => $newValue]);

        TempPayment::where('sess', $sess)
            ->where('month', '>', $newMonth)
            ->update(['amount' => $balance / $remainingMonths]);


        $tempPayments = TempPayment::where('sess', $sess)->get();
        return ['status' => 'ok', 'payments' => $tempPayments, 'balance' => $balance];

    }

    public function loadWorkHistory(Request $request)
    {
        return $request->user()->works;
    }

    public function storeWorkHistory(Request $request, AccountUser $accountUser)
    {

        $request['is_current'] = ($request['is_current'] == 'true' ? 1 : 0);
        $rules = [
            'employer_name' => 'required|min:2',
            'work_address' => 'required',
            'work_phone' => 'nullable|max:15',
            'work_email' => 'required|email',
            'work_website' => 'nullable|max:255',
            'is_current' => 'nullable|numeric',
            'started_date' => 'nullable|date',
            'stopped_date' => 'nullable|date'

        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return ['status' => 'error', 'messages' => $validator->messages()];
        }

       try{
           if(!$request->input('id'))
           {
               $data =  $request->user()->works()->create($request->except('user_id'));
           } else {
               $item = $request->user()->works()->findOrfail($request->input('id'));

               // Defaulter can`t change
               if ($accountUser->isDefaulter()) {
                   return ['status' => 'error', 'messages' => ['stopped_date' => ['You can not change a work.']]];
               }

               $data = $item->update($request->except('user_id'));
           }

           event(new UserUpdatedProfile($request->user()));

           return ['status' => 'ok', 'messages' => $data];

       }catch(\Exception $e) {
           return ['status' => 'error', 'messages' => $e->getMessage()];
       }
    }

    public function deleteWorkHistory(Request $request, $id, AccountUser $accountUser)
    {
        // Defaulter can`t delete
        if ($accountUser->isDefaulter()) {
            return ['status' => 'error', 'message' => 'You can not delete a work.'];
        }

        $d  = $request->user()->works()->findOrFail($id);
        $d->delete();

        event(new UserUpdatedProfile($request->user()));

        return ['status' => 'ok', 'message' => 'Delete'];
    }


    public function invest(Request $request)
    {

        $investmentConfig = json_decode(Setting::v('investmentConfig'));
        $investments = $request->user()->payments()->investments()->latest()->paginate(2);

        $latestInvestment = count($investments) ? $investments[0] : null; // get the first investment from the latest
        $totalInvestment = $request->user()->payments()->investments()->sum('amount'); // amount in currency
        $investmentCount = $request->user()->payments()->investments()->count(); // count total investments


        return view('account.invest')
                    ->with('latestInvestment', $latestInvestment)
                    ->with('investments', $investments)
                    ->with('investmentCount', $investmentCount)
                    ->with('totalInvestment', $totalInvestment)
                    ->with('investmentConfig', $investmentConfig)
                    ->with('title', 'Invest');
    }


    public function changePassword(Request $request)
    {


        return view('account.accounts-change-password')
            ->with('user', $request->user())
            ->with('title', 'Change Password');
    }

    public function changePasswordStore(Request $request)
    {

        $user = $request->user();
        $gotoLogin = false;

        if(!$user->force_change_pwd)
        {
            $gotoLogin = true;
            $request->validate([
                'current_password' => [
                    'required',
                    function($attribute, $value, $fail) use ($user) {
                        if (!Hash::check($value, $user->password)) {
                            return $fail(str_ireplace('_', ' ', $attribute) . ' is not valid');
                        }
                    }
                ],
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'required|min:8'
            ]);
        }
        else
        {
            $request->validate([
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'required|min:8'
            ]);

        }

        $user->password = $request->input('password');
        $user->force_change_pwd = 0;
        $user->save();


        if($gotoLogin)
        {
            return redirect()->route('login')->with('success', 'You have changed your password');
        }

        return redirect()->route('account.home');
    }

    public function loanRepayment(Request $request)
    {

        // look for the loan with new or servicing payments
        $loan = $request->user()
            ->applications()
            ->with('payments')
            ->where('status', Application::STATUS_APPROVED)
            ->whereHas('payments', function($query){
                $query->where('status', 'scheduled');
            })
            ->first();

        if(!$loan)
        {
            return redirect()->route('account.home')->with('success', 'No loan repayment');
        }

        if($loan->status != 'approved')
        {
            return redirect()->route('account.home')->with('success', 'Your loan is has not been approved yet');
        }


        if(!$loan || !isset($loan->payments))
        {
            return redirect()->route('account.profile')->with('success', 'No loan re-payment. You can click on transactions to view all your payments');
        }

        $total = 0;
        foreach($loan->payments as $payment)
            $total += $payment->amount;

        // load transaction
        $transactions = null;
        if($request->input('transactionInfo'))
        {
            $transactionInfo = LoanPayment::whereHas('loan', function($query) use ($loan){
                $query->where('user_id', $loan->owner->id);
            })->find($request->input('transactionInfo'));

             $transactions = TransactionLog::where('model', 'LoanPayment')
                                                ->where('model_id', $transactionInfo->id)
                                                ->latest()
                                                ->paginate(15);
             if(!count($transactions))
             {
                 return back()->with('error', 'No payment log yet');
             }
        }

        return view('account.load-repayment')
                        ->with('loan', $loan)
                        ->with('application', new Application($loan))
                        ->with('total', $total)
                        ->with('transactions', $transactions)
                        ->with('nextPayment', $loan->payments()->where('status', 'scheduled')->first())
                        ->with('Loan Repayment');
    }

    /**
     * @param Request $request
     * @param Settings $settings
     * @param AccountUser $accountUser
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handlePayNow(Request $request, Settings $settings, AccountUser $accountUser)
    {
        $amount = cleanAmount($request->input('amount'));

        if ($amount < 100) {
            return redirect()->route('account.loans.repayment')->with('error', 'Amount is too low');
        }

        try {
            $payment = Payment::find($request->get('paynow'));
        }
        catch (\Exception $exception) {
            return redirect()->route('account.loans.repayment')->with('error', 'Please, select a payment.');
        }

        if ($payment->getApplication()->getUserId() !== $accountUser->getId()) {
            return redirect()->route('account.loans.repayment')->with('error', 'Payment not found.');
        }

        if (! $card = UserCard::find($request->get('card'))) {
            return redirect()->route('account.loans.repayment')->with('error', 'Please, select a valid card or add a new one.');
        }

        $charger = new PaymentCharge($payment);

        $paymentAmount = $payment->getAmount();

        try {
            $charged = $charger->chargeUsingCard($amount, $card, $accountUser->getModel(), false);
        }
        catch (UserHasNoCardException $exception) {
            return redirect()->route('account.loans.repayment')->with('error', 'Please, select a valid card or add a new one.');
        }
        catch (CardDoesNotBelongsToUserException $exception) {
            return redirect()->route('account.loans.repayment')->with('error', 'Please, select a valid card or add a new one.');
        }
        catch (PaymentChargeException $exception) {
            return redirect()->route('account.loans.repayment')->with('error', $exception->getMessage());
        }
        catch (\Exception $exception) {
            return redirect()->route('account.loans.repayment')->with('error', 'Something went wrong. Please, try again later or contact administrator.');
        }

        if (! $charged) {
            return redirect()->route('account.loans.repayment')->with('error', 'Something went wrong. Please, try again later or contact administrator.');
        }

        // Check payment and pause penalty
        if ($payment->isDefault() AND ! $payment->isPenaltyPaused()) {
            if ($amount >= $paymentAmount * $settings->get('loan_due_payments_penalty_pause_threshold', 20) / 100) {
                $payment->pausePenaltyFor($settings->get('loan_due_payments_penalty_pause_days', 5), $accountUser);
            }
        }

        return redirect()->route('account.loans.repayment')->with('success', 'Payment was successful');
    }

    /**
     * @deprecated Old method.
     */
    protected function _handlePayNow(Request $request)
    {

        $amount = cleanAmount($request->input('amount'));
        $card = $request->user()->cards()->find($request->input('card'));

        if(!$card)
        {
            return redirect()->route('account.loans.repayment')->with('error', 'Please select a valid card or add a new one.');
        }
        $user = $request->user();
        if($amount > 100)
        {
            $paymentId = $request->input('paynow');
            $payment = LoanPayment::where('status', 'scheduled')->find($paymentId);

            if(!$payment)
            {
                return redirect()->route('account.loans.repayment')->with('error', 'Loan not found');
            }

            $owner = $payment->loan->owner;
            $loan = $payment->loan;
            $paymentRealAmount = doubleval($payment->amount);
            if($owner->id != $user->id)
            {
                return redirect()->route('account.loans.repayment')->with('error', 'Loan not found');
            }

            $chargeAmount = $amount * 100;
            $fields = [
                'email' => $loan->owner->email,
                'amount' => $chargeAmount,
                'authorization_code' => $card->auth_code,
                'metadata' => ['custom_fields' => ['payment_for' => 'loan_repayment', 'payment_id' => $payment->id]]
            ];

            $res = makePaystackPostRequest('https://api.paystack.co/transaction/charge_authorization', $fields);
            if($res['status'] == 'ok')
            {
                $data = isset($res['data']->data) ? $res['data']->data : null;
                if(!$data)
                {
                    return redirect()->route('account.loans.repayment')->with('error', 'Payment Error: ' .  e($res['data']->message));
                }

                if($data->status == 'success')
                {

                    TransactionLog::create([
                        'trans_type' => 'debit',
                        'payload' => $payment->payload,
                        'amount' => $amount,
                        'performed_by' => $payment->loan->owner->id,
                        'user_id' => $payment->loan->owner->id,
                        'card_id' => $card->id,
                        'status' => 'successful',
                        'model' => 'LoanPayment',
                        'model_id' => $payment->id,
                        'response_message' => 'successful',
                        'pay_for' => 'Loan repayment'
                    ]);


                    $payment->parts()->create(['amount' => $amount]);

                    $totalPaid = doubleval($payment->parts()->sum('amount'));

                    if($totalPaid === $paymentRealAmount || $totalPaid > $paymentRealAmount)
                    {
                        $payment->status = 'paid';
                        $payment->amount = $totalPaid; // reset the amount. Payment completed.
                    }
                    else
                    {
                        // put payment in part payment table
                        $payment->amount = ($paymentRealAmount - $totalPaid);
                    }

                    $payment->save();
                    // check if loan is completed
                    $loan->checkForCompletedLoanRepayment();

                    $message = Message::create([
                        'message' => 'Your loan-repayment was successful. Amount billed: N' . number_format($amount,2),
                        'message_type' => 'email',
                        'subject' => 'Loan Re-payment Alert',
                        'user_id' => $owner->id,
                        'sender' => $owner->id
                    ]);

                    event(new SendMessage($message));
                    return redirect()->route('account.loans.repayment')->with('success', 'Payment was successful');
                }
                else
                {
                    return redirect()->route('account.loans.repayment')->with('error', 'Payment Error: ' . $data->gateway_response);
                }

            }
            else
            {
                return redirect()->route('account.loans.repayment')->with('error', 'Payment Error: ' . $res['message']);
            }
        }
        else
        {
            return redirect()->route('account.loans.repayment')->with('error', 'Amount is too low');
        }
    }


    public function loanRepaymentSave(Request $request, $paymentId)
    {

      $user = $request->user();
        $payment = LoanPayment::where('status', 'scheduled')
                                    ->whereHas('loan', function($query) use ($user) {
                                       $query->where('user_id', $user->id)
                                                ->where('status', 'approved');
                                    })->find($paymentId);

        if(!$payment) return back()->with('error', 'Payment not found or access denied');

        if(LoanPaymentState::where('action', 'payment_changed')
                                ->where('loan_payment_id', $payment->id)
                                ->where('user_id', $user->id)->count())
        {
            return back()->with('error', 'You can not modify loan payment any more.');
        }

        $newAmount = doubleval(str_ireplace(',', '', $request->input('newAmount')));
        $oldAmount = doubleval($payment->amount);

        if($newAmount < $oldAmount && $payment->payment_month == '1')
        {
            if(!$payment) return back()->with('error', 'You can not reduce the first payment, instead use the pause option');
        }

        $loanMin = Setting::v('loan_min');
        if($newAmount < $loanMin)
        {
            return back()->with('error', 'You can not set amount less than NGN' . number_format($loanMin,2));
        }

        $total =  LoanApplication::getTotalPendingPayment($payment->loan_application_id);
        if($total == 0)
        {
            if(!$payment) return back()->with('error', 'Unable to get loan total payment');
        }

        $otherPayments = LoanPayment::where('loan_application_id', $payment->loan_application_id)
                                        ->whereNotIn('id', [$payment->id])->get();

        // if the user change the first month, sub from total and divide by remaining months
        if($otherPayments) {
            $remainingBalance = ($total - $newAmount);
            $chunkedPayment = $remainingBalance / count($otherPayments);

            LoanPayment::where('loan_application_id', $payment->loan_application_id)
                ->whereNotIn('id', [$payment->id])->update(['amount' => $chunkedPayment]);
        }

        $payment->amount = $newAmount;
        $payment->save();

        LoanPaymentState::create(['action' => 'payment_changed', 'loan_payment_id' => $payment->id, 'user_id' => $user->id]);

        return back()->with('success', 'Payment updated');

    }

    public function postLoanPause(Request $request)
    {
        $user = auth()->user();

        $loan = $user->applications()
            ->where('status', 'approved')
            ->with('payments')
            ->whereHas('payments', function($query){
                $query->where('status', 'scheduled')->orderBy('payment_month');
            })->first();

        if (! $loan) {
            return back()->with('error', 'No loan re-payment');
        }

        $application = new Application($loan);

        if (! $card = UserCard::find($request->get('card'))) {
            return back()->with('error', 'Please, select a card');
        }

        try {
            $pauseHandler = new LoanPause($application);
            $paused = $pauseHandler->pauseByUserUsingCard($card);
        }
        catch (CardDoesNotBelongsToUserException $exception) {
            return back()->with('error', 'You can not use this card. Please link a new card under your profile.');
        }
        catch (ApplicationHasNoPaymentException $exception) {
            return back()->with('error', 'No loan re-payment');
        }
        catch (ApplicationPauseException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        catch (\Exception $exception) {
            $paused = false;
        }

        if (! $paused) {
            return back()->with('error', 'Payment error. Be sure that you have enough balance.');
        }

        $message = Message::create([
            'message' => 'Loan paused for a month and all payment moved forward.',
            'message_type' => 'email',
            'subject' => 'Loan Paused For A Month',
            'user_id' => $user->id,
            'sender' => $user->id
        ]);

        NodLog::write($user, 'Loan paused', json_encode($message->toArray()));

        event(new SendMessage($message));

        return back()->with('success', 'Loan paused for a month and all payment moved forward');
    }

    public function loanRepaymentPause(Request $request)
    {
        $user = $request->user();

        if($request->input('action') == 'pauseLoan')
        {

            $loan = $request->user()->applications()->where('status', 'approved')->with('payments')
                ->whereHas('payments', function($query){
                    $query->where('status', 'scheduled')->orderBy('payment_month');
                })->first();

            if(!$loan || !isset($loan->payments))
            {
                return back()->with('error', 'No loan re-payment');
            }

            $application = new Application($loan);

            if (! $application->canPauseByUser()) {
                return back()->with('error', 'You can not pause Loan');
            }

            $payment  = $loan->payments[0];
            if( $request->input('payment') != $payment->id)
            {
                return back()->with('error', 'Do not try to modify the request');
            }

            $card = auth()->user()->cards()->find(request('card'));

            if(!$card || !$card->reusable)
            {
                return back()->with('error', 'You can not use this card. Please link a new card under your profile.');
            }

            // 15% loan repayment plan
            $chargeAmount = ($payment->amount * .15) * 100;

            $fields = [
                'email' =>  $card->email ? $card->email : $loan->owner->email,
                'amount' => $chargeAmount,
                'authorization_code' => $card->auth_code,
                'metadata' => ['custom_fields' => ['payment_for' => 'pausing_of_loan', 'payment_id' => $payment->id]]
            ];

            $res = makePaystackPostRequest('https://api.paystack.co/transaction/charge_authorization', $fields);

            $status = 'failed';
            $amount = 0;
            $payload = '';
            $message = '';
            $success = false;

            if($res['status'] == 'ok') {
                $data = $res['data'];

                if($data->status && $data->data->status == 'success') {
                    $status = 'successful';
                    $success = true;
                    $amount = $data->data->amount / 100;
                    $payload = serialize($data->data);
                } else {
                    $defaultMsg = 'Payment error. Be sure that you have enough balance.';

                    if (property_exists($data, 'data')) {
                        $message = $data->data->gateway_response ?: $defaultMsg;
                    }
                    else {
                        $message = $defaultMsg;
                    }

                    $status = 'failed';
                }

            } else {
                $message = 'Error connecting to payStack';
            }

            TransactionLog::create([
                    'trans_type' => 'debit',
                    'payload' => $payload,
                    'amount' => $amount,
                    'performed_by' => $request->user()->id,
                    'user_id' => $request->user()->id,
                    'card_id' => $card->id,
                    'status' => $status,
                    'model' => 'LoanPayment',
                    'model_id' => $payment->id,
                    'pay_for' => 'Charge for loan pause',
                    'response_message' => $message
                ]);

            if($success) {

                // shift loan a month
                foreach($loan->payments as  $p) {
                    $p->due_at = $p->due_at->addMonth();
                    $p->save();
                }

                $application->setPausedByUser();

                $message = Message::create([
                    'message' => 'Loan paused for a month and all payment moved forward.',
                    'message_type' => 'email',
                    'subject' => 'Loan Paused For A Month',
                    'user_id' => $user->id,
                    'sender' => $user->id
                ]);

                NodLog::write(auth()->user(), 'Loan paused', json_encode($message->toArray()));

                event(new SendMessage($message));

                return back()->with('success', 'Loan paused for a month and all payment moved forward');
            }
            return back()->with('error', $message);

        }

        return back()->with('error', 'Action not valid');
    }

    public function liquidate(Request $request, $id)
    {
        $investment = $request->user()->payments()->investments()->findOrFail($id);

        return view('account.liquidate')
                    ->with('investment', $investment)
                    ->withTitle('Liquidate investment');
    }

    public function liquidateProcess(Request $request, $id)
    {
        $request->validate([
            'liquidate_reason' => 'required|min:10'
        ]);

        $investment = $request->user()->payments()->investments()->findOrFail($id);
        if($investment->investment_ended)
        {
            return back()->with('error', 'Bad request');
        }

       $res = $investment->calculateLiquidation($request->input('liquidate_reason'), auth()->id());

        if($res)
        {
            event(new InvestmentLiquidated($investment));

            //TODO: Move this to event handler
            $admins = User::where('role', 'admin')->get();
            Mail::to($request->user())->bcc($admins)->send(new InvestmentLiquidateEmail($request->user(), $investment));
            return redirect()->route('account.profile.invest')->with('success', 'Investment was successfully liquidated');
        }

        return redirect()->route('account.profile.invest')->with('error', 'Unknown error. please contact the admin.');
    }

    public function switchShadowAccount($id)
    {
        if(!session()->has('shadowedBy'))
        {
            abort(403);
        }

        $user = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPPORT])->findOrFail(session('shadowedBy'));

        $url = session()->get('beforeSwitchUrl', route('mainframe.dashboard'));
        session()->flush();
        \Auth::login($user, true);
        session()->forget(['shadowedBy', 'beforeSwitchUrl']);

        return redirect($url);
    }

    // TRASH. COPIED FROM APIFinanceController, because that controller works using token
    public function cardLinkInit(Request $request)
    {
        $user = auth()->user();

        $email = $request->input('email');
        $investmentData = [];

        if ($email) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'email' => 'email|unique:users|max:150'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }
        } else {
            $email = $user->email;
        }

        if($request->input('action') == 'invest')
        {

            $reason = 'investment';
            $amount = doubleval(str_replace(',','',$request->input('amount')));
            $investmentType = $request->input('investmentType');

            $maxInvest = Setting::v('investment_max_amount');
            $minInvest = Setting::v('investment_min_amount');
            $investmentTypes = json_decode(Setting::v('investmentConfig'));

            $selectedInvestmentType = null;
            if($amount > $maxInvest)
            {
                return $this->errorResponse('The maximum amount you can invest is ' . number_format($maxInvest));
            }

            if($amount < $minInvest)
            {
                return $this->errorResponse('The minimum amount you can invest is ' . number_format($minInvest));
            }

            foreach($investmentTypes as $_investmentType)
            {
                if($_investmentType->value === $investmentType)
                {
                    $selectedInvestmentType = $_investmentType;
                    break;
                }
            }

            if(!$selectedInvestmentType)
            {
                return $this->errorResponse('Please select a valid investment tenor');
            }

            $investmentData = [
                'is_investment' => true,
                'investment_tenor' => $selectedInvestmentType->value
            ];

        }
        else
        {
            $amount = 20;
            $reason = 'card-link';
        }

        $payload = [
            'amount' => $amount * 100, // this amount is in kobo 100kobo = 1 Naira
            'email' => $email,
            'reusable' => true
        ];

        if ($request->input('callback_url')) {
            $payload['callback_url'] = $request->input('callback_url');
        }

        $response = makePaystackPostRequest('https://api.paystack.co/transaction/initialize', $payload);

        if ($response['status'] !== 'ok') {
            return $this->errorResponse($response['message']);
        }

        if ($response['data']->status) {

            $insertData = [
                'amount' => $amount,
                'reason' => $reason,
                'payment_reference' => $response['data']->data->reference
            ];

            $insertData = array_merge($insertData, $investmentData);
            $user->payments()->create($insertData);
            return $this->successResponse('success', (array)$response['data']->data);
        }

        return $this->errorResponse($response['data']->message);

    }


    public function getSuspended(AccountUser $accountUser)
    {
        return view('account.suspended', [
            'accountUser' => $accountUser
        ]);
    }

    public function getDownloads(Settings $settings)
    {
        return view('account.downloads', [
            'settings' => $settings
        ]);
    }

    public function getAppInstallSkip(AccountUser $accountUser)
    {
        $accountUser->skipAppInstall();

        return back()->with('success', 'App install requirement successfully skipped');
    }
}
