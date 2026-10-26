<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log mutative methods for authenticated users
        if (Auth::check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            try {
                $sanitized = $request->except(['password', 'password_confirmation', 'api_secret', 'card_number', 'cvv', '_token']);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'log_name' => 'http',
                    'action' => $request->route()?->getName() ?? $request->path(),
                    'description' => "{$request->method()} {$request->path()}",
                    'properties' => !empty($sanitized) ? $sanitized : null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore logging failures to prevent request crash
            }
        }

        return $response;
    }
}
