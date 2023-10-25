<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        /**
         * @var LogManager $log
         */
        $log = app('log');
        $context = $request->all();
        if (isset($context['password'])) {
            $context['password'] = 'hidden';
        }

        $log->debug("Request Captured: " . $request->method() . ' ' . $request->url(), $context);

        return $response;
    }
}
