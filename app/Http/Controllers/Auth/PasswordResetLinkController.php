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

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(64);

            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                ['token' => hash('sha256', $token), 'created_at' => now()]
            );

            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            SendEmailJob::dispatch($user->email, 'welcome_email', [
                'user_name' => $user->name,
                'login_url' => $resetUrl,
            ], $user->id);
        }

        return back()->with('status', 'We have emailed your password reset link if the address exists in our database.');
    }
}
