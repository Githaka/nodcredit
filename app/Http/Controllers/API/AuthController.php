<?php

namespace App\Http\Controllers\API;

use App\Events\OnPasswordResetRequest;
use App\Http\Requests\API\ForgotPasswordRequest;
use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\LogoutRequest;
use App\Http\Requests\API\ResetPasswordRequest;
use App\NodCredit\Account\UserToken;
use App\Token;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{

    public function postLogin(LoginRequest $request)
    {
        $identity = $request->input('identity');
        $password = $request->input('password');

        $phone = formatPhone($identity);

        /** @var User $user */
        $user = User::where('email', $identity)->orWhere('phone', $phone)->first();

        if (! $user OR ! Hash::check($password, $user->password)) {
            return $this->errorResponse('Login and/or password are incorrect.');
        }

        $token = UserToken::create($user);

        auth()->setUser($user);

        return $this->successResponseWithUser('OK', [
            'access_token' => $token->token,
            'expire_at' => $token->expire_at,
        ]);

    }

    public function postLogout(LogoutRequest $request)
    {

        $hash = $request->header('Auth-Token');

        $token = Token::where('token', $hash)->first();

        UserToken::delete($token);

        return $this->successResponse();
    }

    public function postForgotPassword(ForgotPasswordRequest $request)
    {
        $identity = $request->input('identity');
        $phone = formatPhone($identity);

        $user = User::where('email', $identity)->orWhere('phone', $phone)->first();

        if (! $user) {
            return $this->errorResponse('Account not valid.');
        }

        event(new OnPasswordResetRequest($user));

        $message = 'Please check your email for password reset instruction. You may want to check your junk/spam folder in case and remember to mark the email NOT Junk in that case.';

        return $this->successResponse($message);
    }

}