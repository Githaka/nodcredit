<?php

namespace App\Http\Controllers;

use App\Events\OnPasswordResetRequest;
use App\NodCredit\Account\Exceptions\UserFactoryException;
use App\NodCredit\Account\Factories\UserFactory;
use App\NodCredit\Message\MessageSender;
use App\OTP;
use App\NodCredit\Account\User as AccountUser;
use App\PasswordReset;
use App\Score;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function getLogin()
    {
        return view('frontend-v2.auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'identity' => 'required',
            'password' => 'required'
        ]);

        $phone = formatPhone($request->input('identity'));

        /** @var User $user */
        $user = User::where('email', $request->input('identity'))->orWhere('phone', $phone)->first();

        if (! $user OR ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => [
                    'error' => ['Unable to login']
                ]
            ], 422);
        }

        Auth::login($user, true);

        $user->last_login = now();
        $user->save();

        return response()->json([
            'redirect_to' => $user->isPartner() ? route('account.profile.invest') : route('account.home')
        ]);
    }

    public function getLogout()
    {
        Auth::logout();

        return redirect(route('frontend.home'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postRegisterCustomer(Request $request)
    {
        $request['phone'] = formatPhone($request->get('phone'));

        try {
            $user = UserFactory::createCustomer($request->only(['bvn', 'email', 'phone', 'agree']));
        }
        catch (UserFactoryException $exception) {
            return response()->json([
                'errors' => $exception->getErrors()->toArray()
            ], 422);
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => ['error' => 'Error. Please, try again later.']
            ], 422);
        }


        $user->getModel()->sendOTP();

        // Send welcome message
        MessageSender::send('welcome-customer', $user);

        \Auth::login($user->getModel(), true);

        return response()->json([
            'redirect_to' => route('auth.phone.verify')
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postRegisterInvestor(Request $request)
    {
        $request['phone'] = formatPhone($request->get('phone'));

        try {
            $user = UserFactory::createInvestor($request->only(['email', 'phone', 'agree']));
        }
        catch (UserFactoryException $exception) {
            return response()->json([
                'errors' => $exception->getErrors()->toArray()
            ], 422);
        }
        catch (\Exception $exception) {
            return response()->json([
                'errors' => ['error' => 'Error. Please, try again later.']
            ], 422);
        }

        $user->getModel()->sendOTP();

        // Send welcome message
        MessageSender::send('welcome-investor-registered-by-himself', $user);

        \Auth::login($user->getModel(), true);

        return response()->json([
            'redirect_to' => route('auth.phone.verify')
        ]);
    }

    public function getPhoneVerify(AccountUser $user)
    {
        $lastOtp = OTP::where('phone', $user->getPhone())->orderBy('created_at', 'DESC')->first();

        $validDate = now()->subSeconds(60);

        if (! $lastOtp OR $validDate->gt($lastOtp->created_at)) {
            $user->getModel()->sendOTP();
        }

        $lastOtp = OTP::where('phone', $user->getPhone())->orderBy('created_at', 'DESC')->first();
       
        return view('frontend-v2.auth.phone-verify', [
            'sentAt' => $lastOtp->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    public function postPhoneVerify(Request $request, AccountUser $user)
    {
        $this->validate($request, [
            'code' => 'required|digits:4'
        ]);

        $otp = OTP::where('otp', $request->get('code'))
            ->where('expire_at', '>', now())
            ->where('phone', $user->getPhone())
            ->first();

        if (! $otp) {
            return response()->json([
                'errors' => ['error' => ['Code is not valid']]
            ], 422);
        }

        $user->getModel()->phone_verified = now();
        $user->getModel()->save();

        $otp->delete();

        // Check if user has register score
        if (! $hasRegisterScore = Score::where('user_id', $user->getId())->where('info', 'SUCCESSFUL_USER_REGISTERATION')->first()) {
            // give user some score
            $user->getModel()->getScoreInfo('SUCCESSFUL_USER_REGISTERATION');
        }

        return response()->json([
            'redirect_to' => route('account.home')
        ]);
    }

    public function getForgotPassword()
    {
        return view('frontend-v2.auth.forgot-password');
    }

    public function postForgotPassword(Request $request)
    {
        $request->validate([
            'identity' => 'required',
        ]);

        $phone = formatPhone($request->input('identity'));

        /** @var User $user */
        $user = User::where('email', $request->input('identity'))->orWhere('phone', $phone)->first();

        if (! $user) {
            return response()->json([
                'errors' => [
                    'error' => ['Account is not valid']
                ]
            ], 422);
        }

        event(new OnPasswordResetRequest($user));


        return response()->json();
    }

    public function getResetPassword(string $token)
    {
        return view('frontend-v2.auth.reset-password', [
            'token' => $token
        ]);
    }

    public function postResetPassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'token' => 'required|exists:password_resets,token'
        ]);

        $reset = PasswordReset::where('token', $request->get('token'))->first();

        if (! $user = User::where('email', $reset->email)->first()) {
            return response()->json([
                'errors' => [
                    'error' => ['Unable to reset']
                ]
            ], 422);
        }

        $user->password = $request->input('password');
        $user->save();

        PasswordReset::cleanUp($user->email);

        return response()->json();

    }

}