<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiRequestLog;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = (int) round((microtime(true) - $startTime) * 1000);

        try {
            $user = $request->user();
            $apiKey = $request->attributes->get('api_key');

            $payload = $request->except(['password', 'api_secret', 'secret']);
            $resContent = $response->getContent();
            if (strlen($resContent) > 2000) {
                $resContent = substr($resContent, 0, 2000) . '... [truncated]';
            }

            ApiRequestLog::create([
                'user_id' => $user?->id,
                'api_key_id' => $apiKey?->id,
                'method' => $request->method(),
                'path' => $request->path(),
                'ip_address' => $request->ip(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'payload' => !empty($payload) ? json_encode($payload) : null,
                'response_body' => $resContent,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently continue so logging never breaks live API requests
        }

        return $response;
    }
}
