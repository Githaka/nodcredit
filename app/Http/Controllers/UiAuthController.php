<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use App\NodCredit\Message\MessageSender;
use App\Score;
use Illuminate\Http\Request;
use App\User;
use App\OTP;
use Hash;
use App\Message;

class UiAuthController extends Controller
{
    public function login()
    {
        \Auth::logout();
        return view('user.login')
                ->with('title', 'Login');
    }

    public function verifyMobile(Request $request)
    {

        if(!$request->session()->has('loanInfo'))
        {
            return redirect('/')->with('error', 'No verification data in session');
        }

        $loanInfo = $request->session()->get('loanInfo');
        $user = User::find($loanInfo['user']);


        if(!$user)
        {
            $request->session()->remove('loanInfo');
            return redirect('/')->with('error', 'Data is not valid');
        }

        $lastOtp = OTP::where('phone', auth()->user()->phone)->orderBy('created_at', 'DESC')->first();
        $validDate = now()->subSeconds(60);

        if($request->input('resend') && $request->input('resend') == 'otp')
        {
            if (!$lastOtp OR $validDate->gt($lastOtp->created_at)) {
                $user->sendOTP();
                return redirect('/verify-mobile')->with('otpInfo', 'Check your phone for new OTP');
            }

            return redirect('/verify-mobile')->with('error', 'Please, wait while the resend button is available.');
        }

        return view('user.mobile-verify')
                ->with('user', $user)
            ->with('title', 'Login');
    }


    public function verifyMobilePost(Request $request)
    {

        if(!$request->session()->has('loanInfo'))
        {
            return back()->with('error', 'No verification data in session');
        }

        $loanInfo = $request->session()->get('loanInfo');
        $user = User::find($loanInfo['user']);


        if(!$user)
        {
            $request->session()->remove('loanInfo');
            return  back()->with('error', 'Data is not valid');
        }

        $otp  = OTP::where('otp', $request->input('otp'))
                        ->where('expire_at', '>', now())
                        ->where('phone', $user->phone)->first();

        if(!$otp)
        {
            return back()->with('error', 'OTP is not valid/expired');
        }


        $user->phone_verified = now();
        $user->save();

        \Auth::login($user, true);
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
        $otp->delete();


        // Check if user has register score
        if (! $hasRegisterScore = Score::where('user_id', $user->id)->where('info', 'SUCCESSFUL_USER_REGISTERATION')->first()) {
            // give user some score
            $user->getScoreInfo('SUCCESSFUL_USER_REGISTERATION');
        }

        return redirect()->route('account.profile')->with('Please update your password');
    }


    public function processLogin(Request $request)
    {
        $request->validate([
            'identity' => 'required',
            'password' => 'required'
        ]);

        $phone = formatPhone($request->input('identity'));

        $user = User::whereEmail($request->input('identity'))
                    ->orWhere('phone', $phone)->first();

        if(!$user) return back()->with('error', 'Unable to login');

        if(!Hash::check($request->password, $user->password))
        {
            return back()->with('error', 'Unable to login');
        }

        \Auth::login($user, true);
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        $goto = $user->role === 'partner' ? '/account/me/invest' : route('account.home');

        return redirect($goto)->with('success', 'Welcome ' . e($user->name));

    }

    public function register()
    {

        return view('user.register')
            ->with('title', 'Register');
    }

    public function registerProcess(Request $request)
    {

        $INVESTOR_TYPE = '2';
        $USER_TYPE = '1';

        $messages = [
            'phone' => 'The :attribute field is required.',
        ];

        $request['phone'] = formatPhone($request->input('phone'));

        $rules = [
            'email' => 'required|email|unique:users',
            'want' => 'required|numeric|min:1|max:2',
            'phone' => 'required|min:10:max:14|unique:users',
            'agree' => 'required|accepted'
        ];

        if(request('want') !== $INVESTOR_TYPE) {
            $bvnInfo = getBVNInfoFromPayStack($request->input('bvn'));
            $rules['bvn'] = [
                'required',
                function($attribute, $value, $fail) use ($bvnInfo) {
                    if (!$bvnInfo) {
                        return $fail($attribute.' is invalid.');
                    }
                },
                'unique:users'
            ];
        }

        $request->validate($rules, $messages);
        $data = [
            'name' => request('want') !== $INVESTOR_TYPE ? sprintf('%s %s', $bvnInfo->first_name, $bvnInfo->last_name) : '',
            'phone' => $request->input('phone'),
            'bvn' => $request->input('bvn'),
            'bvn_phone' => request('want') !== $INVESTOR_TYPE ? $bvnInfo->mobile : request('phone'),
            'email' => $request->input('email'),
            'dob'  => request('want') !== $INVESTOR_TYPE  ? $bvnInfo->formatted_dob : null,
            'password' => str_random(16),
        ];

        $user = User::create($data);

        \Auth::login($user, true);

        $user->last_login = date('Y-m-d H:i:s');
        $user->force_change_pwd = 1;
        $user->role =  request('want') == $INVESTOR_TYPE ? 'partner' : 'user';
        $user->save();

        $user->sendOTP();

        $loanInfo = ['loanAmount' => 0, 'loanType' => '', 'user' => $user->id];
        $request->session()->put('loanInfo', $loanInfo);

        $accountUser = new \App\NodCredit\Account\User($user);

        // Send welcome message
        if ($user->isPartner()) {
            MessageSender::send('welcome-investor-registered-by-himself', $accountUser);
        }
        else {
            MessageSender::send('welcome-customer', $accountUser);
        }

        return redirect()->route('verify.mobile');
    }

    public function getLoan()
    {

        return view('frontend.get-loan');
    }
}
