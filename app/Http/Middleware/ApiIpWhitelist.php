<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiIpWhitelist
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('api_key');

        if ($apiKey && is_array($apiKey->ip_whitelist) && count($apiKey->ip_whitelist) > 0) {
            $clientIp = $request->ip();

            if (!in_array($clientIp, $apiKey->ip_whitelist, true)) {
                return response()->json([
                    'status' => 'error',
                    'code' => 403,
                    'message' => "Unauthorized client IP address: {$clientIp}",
                ], 403);
            }
        }

        return $next($request);
    }
}
