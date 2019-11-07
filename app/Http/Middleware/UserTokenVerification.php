<?php

namespace App\Http\Middleware;

use App\Token;
use Closure;

class UserTokenVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tokenHeader = $request->header('Auth-Token');

        $token = Token::where('expire_at', '>=', date('Y-m-d H:i:s'))->where('token', $tokenHeader)->first();

        if (! $token) {
            return \Illuminate\Support\Facades\Response::json([
                'data' => null,
                'message' => 'Token is expired or invalid.',
                'status' => 'error'
            ], 401);
        }

        auth()->setUser($token->user);

        // OLD
        $request->user = $token->user;

        return $next($request);
    }
}
