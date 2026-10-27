<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Currency;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['nullable', 'in:client,reseller'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:2'],
        ]);

        $defaultCurrency = Currency::where('is_primary', true)->first();
        $role = $request->input('role', 'client');
        $isReseller = ($role === 'reseller');

        $user = User::create([
            'role' => $role,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'country' => $request->country ?? 'BD',
            'currency_id' => $defaultCurrency?->id,
            'balance' => 0.0000,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        try {
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                $user->sendEmailVerificationNotification();
            }
        } catch (\Throwable $e) {
            Log::warning('Email verification dispatch ignored: ' . $e->getMessage());
        }

        try {
            SendEmailJob::dispatch($user->email, 'welcome_email', [
                'user_name' => $user->name,
                'login_url' => route('login'),
            ], $user->id);
        } catch (\Throwable $e) {
            Log::warning('Welcome email dispatch ignored: ' . $e->getMessage());
        }

        Auth::login($user);

        // Merge persistent guest cart into user session
        try {
            app(\App\Services\Cart\CartService::class)->mergeOnLogin();
        } catch (\Throwable $e) {
            Log::warning('Cart merge on register ignored: ' . $e->getMessage());
        }

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

        if ($isReseller) {
            return redirect()->route('reseller.dashboard')->with('success', 'Welcome to HostNibo Reseller Portal! Your account is active.');
        }

        return redirect()->route('client.dashboard')->with('success', 'Welcome to HostNibo Domains!');
    }
}
