<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SmsTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $secretToken = env('SMS_MASTER_TOKEN', 'changeme-secret-token-2026');
        $token       = $request->route('token');

        if (!$token || !hash_equals($secretToken, $token)) {
            abort(404); // Show 404 so no one knows this route exists
        }

        return $next($request);
    }
}
