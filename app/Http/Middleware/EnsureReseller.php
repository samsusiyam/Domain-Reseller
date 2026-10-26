<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureReseller
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

        // Admins are allowed to inspect reseller portal
        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->isClient()) {
            return redirect()->route('client.dashboard')->with('info', 'Apply for a reseller account to access wholesale pricing and API integration.');
        }

        if (!$user->isReseller()) {
            abort(403, 'Unauthorized access to reseller portal.');
        }

        return $next($request);
    }
}
