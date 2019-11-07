<?php

namespace App\Http\Middleware;

use Closure;

class ForcePasswordChange
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
        if($request->user() && $request->user()->force_change_pwd)
        {

            if(!ends_with(url()->current(), 'account/change-password'))
            {
                return redirect()->route('user.change.password')->with('error', 'Change your password to continue');
            }

        }
        return $next($request);
    }
}
