<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use App\NodCredit\Message\MessageSender;
use App\TransactionLog;
use Illuminate\Http\Request;

use App\User;
use App\Message;

class UiAdminAccountsController extends AdminController
{
    public function accounts(Request $request)
    {

        $builder = (new User)->newQuery();

        $filterByKeyword = $request->input('q');
        $filterByRole = $request->input('role');

        if ($filterByKeyword) {
            $builder
                ->where('name', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('email', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('phone', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('bvn_phone', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('bvn', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('account_number', 'like', '%'.$filterByKeyword.'%');
        }

        if ($filterByRole) {
            $builder->where('role', $filterByRole);
        }

        $builder
            ->withCount('applications')
            ->orderBy('name');

        $accounts = $builder->paginate(15);

        $accounts->appends([
            'q' => $filterByKeyword,
            'role' => $filterByRole
        ]);

        $downloadLink = route('admin.accounts.download', [
            'role' => $filterByRole,
            'q' => $filterByKeyword
        ]);

        return view('admin.accounts')
                    ->with('accounts', $accounts)
                    ->with('totalAccounts', User::count())
                    ->with('title', 'Accounts')
                    ->with('downloadLink', $downloadLink)
            ;
    }

    public function downloadAccounts(Request $request)
    {
        $builder = User::orderBy('name');

        $filterByKeyword = $request->input('q');
        $filterByRole = $request->input('role');

        if ($filterByKeyword) {
            $builder
                ->where('name', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('email', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('phone', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('bvn', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('bvn_phone', 'like', '%'.$filterByKeyword.'%')
                ->orWhere('account_number', 'like', '%'.$filterByKeyword.'%');
        }

        if ($filterByRole) {
            $builder->where('role', $filterByRole);
        }

        $builder->withCount('applications');

        $users = $builder->get();

        $filepath = storage_path('users-' . time() . '.csv');
        $file = fopen($filepath, 'w');

        // Headers
        fputcsv($file, ['Name', 'Email', 'Phone', 'BVN Phone', 'Gender', 'Birth Date', 'Loans']);

        // Data
        foreach ($users as $user) {
            fputcsv($file, [
                str_replace(',', ' ', $user->name),
                $user->email,
                $user->phone,
                $user->bvn_phone,
                $user->gender,
                $user->dob,
                $user->applications_count
            ]);
        }

        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function shadowAccount($id)
    {

            $user = User::where('id', '!=', auth()->id())
                        ->where('role', '!=', 'admin')
                    ->findOrFail($id);

            session()->flush();

            session()->put('shadowedBy', auth()->id());
            session()->put('beforeSwitchUrl', url()->previous());
            \Auth::login($user, true);

            return redirect()->route('account.home');
    }

    public function changePassword($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin() AND !auth()->user()->isAdmin()) {
            return back()->with('error', 'Access denied.');
        }

        return view('admin.accounts-change-password')
                        ->with('user', $user)
                        ->with('title', 'Change Password');
    }

    public function changePasswordStore(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|min:8',
        ]);

        $user->password = $request->input('password');
        $user->save();

        if($request->input('emailPassword'))
        {
            $message = Message::create([
                'message' => sprintf("Dear %s, Your new NodCredit password is: %s\n\nRemember: Password is secret and case sensitive", e($user->name), $request->input('password')),
                'subject' => 'NodCredit Password Reset',
                'message_type' => 'email',
                'sender' => $request->user()->id,
                'user_id' => $user->id
            ]);

            event(new SendMessage($message));
        }

        return redirect()->route('admin.accounts')->with('success', 'You have change password for ' . e($user->name));
    }

    public function showAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return back();
    }

    public function transactions(Request $request)
    {

        $transactions = TransactionLog::with('owner', 'user', 'card')->latest()->paginate(20);
        return view('admin.transactions')
                    ->with('transactions', $transactions)
                    ->with('Transactions');
    }

    public function message(Request $request, $id)
    {
        $user = User::find($id);
        return view('admin.message')
            ->with('user', $user)
            ->with('Send message');
    }

    public function sendMessage(Request $request, $id)
    {

        if($request->input('message_type') == 'sms')
        {
            $request->validate([
                'message' => 'required|max:160',
                'message_type' => 'required'
            ]);
        }
        else
        {
            $request->validate([
                'message' => 'required|max:2000',
                'subject' => 'required',
                'message_type' => 'required|in:sms,both,email'
            ]);
        }

        $message = Message::create([
            'message' => $request->input('message'),
            'subject' => $request->input('subject'),
            'message_type' => $request->input('message_type'),
            'sender' => $request->user()->id,
            'user_id' => $id
        ]);

        event(new SendMessage($message));

        return back()->with('success', 'Message queued for sending');
    }

    public function getBannedAccounts()
    {
        $accounts = User::whereNotNull('banned_at')
            ->orderBy('banned_at', 'DESC')
            ->paginate(15);

        return view('admin.accounts-banned', [
            'accounts' => $accounts
        ]);
    }

    public function getAccountUnban(string $id)
    {
        try {
            /** @var \App\NodCredit\Account\User $accountUser */
            $accountUser = \App\NodCredit\Account\User::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        if (! $accountUser->isBanned()) {
            return redirect()->back()->with('error', "{$accountUser->getName()}'s account is not banned.");
        }

        if (! $accountUser->unban()) {
            return redirect()->back()->with('error', "Error during unbanning {$accountUser->getName()}'s account. Please, contact admin.");
        }

        return redirect()->back()->with('success', "{$accountUser->getName()}'s account successfully unbanned.");
    }

    public function postAccountBan(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required'
        ]);

        try {
            /** @var \App\NodCredit\Account\User $accountUser */
            $accountUser = \App\NodCredit\Account\User::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        if (! $accountUser->ban($request->get('reason', ''))) {
            return response()->json([
                'errors' => [
                    'error' => ["Error during banning {$accountUser->getName()}'s account. Please, contact admin."]
                ]
            ], 422);
        }

        return response()->json();
    }

    public function getAccountContacts(string $id)
    {
        try {
            /** @var \App\NodCredit\Account\User $accountUser */
            $accountUser = \App\NodCredit\Account\User::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        return view('admin.accounts.contacts', [
            'accountUser' => $accountUser,
            'contacts' => $accountUser->getContacts()
        ]);
    }

    public function getAccountLocations(string $id)
    {
        try {
            /** @var \App\NodCredit\Account\User $accountUser */
            $accountUser = \App\NodCredit\Account\User::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        return view('admin.accounts.locations', [
            'accountUser' => $accountUser,
            'locations' => $accountUser->getLocations()
        ]);
    }

    public function postInvestorAdd(Request $request)
    {
        $request['phone'] = formatPhone($request->input('phone'));

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|min:10|max:14|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $investor = new User();
        $investor->name = $request->get('name');
        $investor->email = $request->get('email');
        $investor->phone = formatPhone($request->get('phone'));
        $investor->force_change_pwd = 0;
        $investor->password = $request->get('password');
        $investor->role = User::ROLE_PARTNER;
        $investor->save();

        if (! $investor->exists) {
            return response()->json([
                'errors' => [
                    'error' => ['Error. Please, try again or contact administrator.']
                ]
            ], 422);
        }

        $accountUser = new \App\NodCredit\Account\User($investor);

        MessageSender::send('welcome-investor-registered-by-admin', $accountUser, [
            '#PASSWORD#' => $request->get('password')
        ]);

        return response()->json();
    }

    public function getAccount(string $id)
    {
        try {
            /** @var \App\NodCredit\Account\User $accountUser */
            $accountUser = \App\NodCredit\Account\User::find($id);
        }
        catch (\Exception $exception) {
            abort(404);
        }

        return response()->json([
            'account' => $accountUser->getModel()
        ]);
    }
}
