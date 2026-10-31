<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();
        if ($user) {
            $token = Str::random(64);

            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                ['token' => hash('sha256', $token), 'created_at' => now()]
            );

            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            SendEmailJob::dispatch($user->email, 'password_reset', [
                'user_name'      => $user->name,
                'reset_url'      => $resetUrl,
                'expiry_minutes' => 60,
                'company_name'   => config('app.name', 'HostNibo Domain Hub'),
            ], $user->id);
        }

        return back()->with('status', 'We have emailed your password reset link if the address exists in our database.');
    }
}
