<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $secretToken = config('services.sms_master_token');
        $token       = $request->query('token');

        if (!$token || !hash_equals($secretToken, $token)) {
            abort(404);
        }

        return $next($request);
    }
}
