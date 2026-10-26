<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if (!$user->isAdmin()) {
            if ($user->isReseller()) {
                return redirect()->route('reseller.dashboard');
            }
            if ($user->isClient()) {
                return redirect()->route('client.dashboard');
            }
            abort(403, 'Unauthorized access to administration area.');
        }

        return $next($request);
    }
}
