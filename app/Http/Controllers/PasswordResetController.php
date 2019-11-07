<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Events\OnPasswordResetRequest;
use App\PasswordReset;

class PasswordResetController extends Controller
{
    public function logout()
    {
        \Auth::logout();
        return redirect()->route('login');
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password')->withTitle('Forgot password');

    }

    // send out new password reset token
    public function forgotPasswordPost(Request $request)
    {
        $this->validate($request, ['identity' => 'required|max:150']);

        $account = User::where('email', $request->input('identity'))->first();

        if(!$account) return back()->with('error', 'Account not valid.');

        event(new OnPasswordResetRequest($account));

        return back()->with('success', 'Please check your email for password reset instruction. You may want to check your junk/spam folder in case and remember to mark the email NOT Junk in that case.');
    }

    // when the user click on password reset, execute this method, validate token and ask for a new password if token is valid
    public function resetPassword($token)
    {
        try
        {
            $data = explode('::', base64_decode($token));

            if(count($data) !== 2)
            {
                return redirect(route('auth.forgot-password'))->with('error', 'Invalid data.');
            }

            $passRequestInfo = PasswordReset::where('token', $token)
                ->where('email', $data[1])->first();
            if(!$passRequestInfo)
            {
                return redirect(route('auth.forgot-password'))->with('error', 'Invalid data.');
            }

            session()->put('passResetUser', $passRequestInfo->email);

            return view('auth.reset-password')
                ->with('token', $token)
                ->withTitle('Reset password');
        }
        catch(\Exception $e)
        {
            return redirect(route('auth.forgot-password'))->with('error', 'Unable to parse the token, please request for a new token.');
        }
    }

    public function setNewPassword(Request $request, $token)
    {
        $this->validate($request, [
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation'  => 'required'
        ]);

        if($request->input('password') === 'A@12bwe3')
        {
            return back()->with('error', 'You can not use the sample password');
        }

        $user = User::where('email', session()->get('passResetUser'))
            ->first();

        if(!$user)
        {
            return redirect(route('auth.forgot-password'))->with('error', 'Request a new token');
        }

        session()->forget('passResetUser');

        $user->password = $request->input('password');
        $user->save();
        PasswordReset::cleanUp($user->email);
        return redirect(route('login'))->with('success', 'Please login with your new password.');
    }
}
