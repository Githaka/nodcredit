<?php

namespace App\Http\Middleware;

use Closure;
use Response;


class AdminCheckMiddleware
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
        if (! $request->user() || ! $request->user()->isAdmin()) {
            return abort(401);
        }

        return $next($request);
    }
}
