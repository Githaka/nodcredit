<?php

namespace App\Http\Middleware;

use Closure;

class RoleAccess
{
    /**
     * @param $request
     * @param Closure $next
     * @param null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $roles = explode('|', $guard);

        foreach ($roles as $role) {
            if (\App\NodCredit\Account\RoleAccess::hasAccess($request->user()->role, $role)) {
                return $next($request);
            }
        }

        return abort(401, 'Access denied.');
    }
}
