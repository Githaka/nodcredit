<?php

namespace App\Http\Middleware;

use App\NodCredit\Account\User;
use Closure;

class UserIsActive
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
        $user = auth()->user();


        if (! $user) {
            return redirect('/');
        }

        $accountUser = new User($user);

        if ($accountUser->isBanned()) {
            return redirect(route('account.suspended'));
        }

        return $next($request);
    }
}
