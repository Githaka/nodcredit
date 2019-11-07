<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ApiRequestLogger
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $data = [
            'url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'request_header' => $request->header(),
            'request_body' => json_decode($request->getContent(), true),
            'ip' => $request->ip(),
            'response_code' => $response->getStatusCode(),
            'response_body' => json_decode($response->getContent(), true),
        ];

        Log::channel('api-requests')->info(json_encode($data));
    }
}
