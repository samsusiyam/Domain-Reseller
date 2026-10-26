<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('api_key');
        $keyIdentifier = $apiKey ? 'api_key:' . $apiKey->id : 'api_ip:' . $request->ip();

        $maxAttempts = (int) config('reseller.rate_limits.requests_per_minute', 60);

        if (RateLimiter::tooManyAttempts($keyIdentifier, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($keyIdentifier);

            return response()->json([
                'status' => 'error',
                'code' => 429,
                'message' => 'API rate limit exceeded. Please slow down your requests.',
                'retry_after_seconds' => $seconds,
            ], 429)->header('Retry-After', $seconds);
        }

        RateLimiter::hit($keyIdentifier, 60);

        $response = $next($request);

        $remaining = RateLimiter::remaining($keyIdentifier, $maxAttempts);
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));

        return $response;
    }
}
