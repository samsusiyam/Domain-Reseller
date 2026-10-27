<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            LoginHistory::create([
                'user_id' => null,
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed',
                'failure_reason' => 'Invalid credentials',
                'login_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Merge persistent guest cart into user session
        app(\App\Services\Cart\CartService::class)->mergeOnLogin();

        LoginHistory::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'login_at' => now(),
        ]);

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('2fa_verified', false);
            return redirect()->route('two-factor.challenge');
        }

        // Support intended redirect or explicit ?next=/checkout parameter
        $next = $request->input('next');
        if ($next) {
            $parsed = parse_url($next);
            $path = $parsed['path'] ?? '/';
            if (isset($parsed['query'])) {
                $path .= '?' . $parsed['query'];
            }
            if (str_starts_with($path, '/') && !str_starts_with($path, '//')) {
                return redirect($path);
            }
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isReseller()) {
            return redirect()->intended(route('reseller.dashboard'));
        }

        return redirect()->intended(route('client.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
