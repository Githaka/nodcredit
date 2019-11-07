<?php

namespace App\Http\Middleware;

use Closure;

class UserPhoneIsVerified
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
        if (! auth()->user()) {
            return redirect(route('auth.login'));
        }

        if (! auth()->user()->phone_verified) {
            return redirect(route('auth.phone.verify'));
        }

        return $next($request);
    }
}
