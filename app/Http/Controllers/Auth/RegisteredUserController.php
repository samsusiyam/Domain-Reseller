<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Currency;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()->symbols()],
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
            'status' => $isReseller ? 'pending' : 'active',
            'email_verified_at' => null, // requires email verification
        ]);

        // Send verification notification
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }

        // Send welcome email in background
        SendEmailJob::dispatch($user->email, 'welcome_email', [
            'user_name' => $user->name,
            'login_url' => route('login'),
        ], $user->id);

        if ($isReseller) {
            return redirect()->route('login')->with('success', 'Your reseller application has been submitted and is pending administrator review. Please check your email to verify your address.');
        }

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
