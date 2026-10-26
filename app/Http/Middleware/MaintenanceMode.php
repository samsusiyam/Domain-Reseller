<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenance = SystemSetting::get('maintenance_mode', '0');

        if ($isMaintenance === '1' || $isMaintenance === true) {
            // Allow administrators to bypass maintenance
            if (Auth::check() && Auth::user()->isAdmin()) {
                return $next($request);
            }

            if ($request->is('admin*') || $request->is('login')) {
                return $next($request);
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'code' => 503,
                    'message' => 'Platform is currently undergoing scheduled maintenance. Please try again shortly.',
                ], 503);
            }

            return response()->view('errors.503', [], 503);
        }

        return $next($request);
    }
}
