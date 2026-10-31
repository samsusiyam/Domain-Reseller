<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    /**
     * Show admin profile edit form, password change, and passkeys.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $passkeys = $user->passkeys()->latest()->get();

        return view('admin.profile.edit', compact('user', 'passkeys'));
    }

    /**
     * Update admin personal details.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.profile.edit')->with('success', 'Profile details updated successfully.');
    }

    /**
     * Update admin account password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.profile.edit')->with('success', 'Password updated successfully.');
    }

    /**
     * View Two-Factor Authentication status & setup.
     */
    public function twoFactor(Request $request)
    {
        $user = $request->user();

        $secret = null;
        $qrUrl = null;

        if (!$user->two_factor_confirmed_at) {
            $secret = $user->two_factor_secret ?: $this->generateBase32Secret();
            $user->forceFill(['two_factor_secret' => $secret])->save();

            $appName = urlencode(config('app.name', 'HostNibo Domain Hub'));
            $qrUrl = "otpauth://totp/{$appName}:" . urlencode($user->email) . "?secret={$secret}&issuer={$appName}";
        }

        return view('admin.profile.two-factor', compact('user', 'secret', 'qrUrl'));
    }

    /**
     * Confirm TOTP code and activate 2FA for admin.
     */
    public function confirmTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:8'],
        ]);

        $user = $request->user();
        $secret = $user->two_factor_secret;

        if (!$secret || !$this->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided two-factor authentication code is invalid.']);
        }

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ])->save();

        session(['2fa_verified' => true]);

        return redirect()->route('admin.two-factor.index')
            ->with('success', 'Two-Factor Authentication activated successfully.')
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Disable 2FA for admin.
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->forget('2fa_verified');

        return redirect()->route('admin.two-factor.index')->with('success', 'Two-Factor Authentication has been disabled.');
    }

    /**
     * View active browser and device sessions for admin.
     */
    public function sessions(Request $request)
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                return (object)[
                    'id'                => $session->id,
                    'ip_address'        => $session->ip_address,
                    'user_agent'        => $session->user_agent,
                    'last_activity'     => \Carbon\Carbon::createFromTimestamp($session->last_activity),
                    'is_current_device' => $session->id === $currentSessionId,
                ];
            });

        return view('admin.profile.sessions', compact('sessions', 'user'));
    }

    /**
     * Terminate a specific admin session.
     */
    public function destroySession(Request $request, string $id)
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        if ($id === $currentSessionId) {
            return back()->with('error', 'Cannot terminate current active session from here.');
        }

        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Session terminated successfully.');
    }

    /**
     * Terminate all other sessions except current.
     */
    public function destroyOtherSessions(Request $request)
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return back()->with('success', 'All other browser sessions have been logged out.');
    }

    /**
     * Generate random Base32 secret string.
     */
    private function generateBase32Secret(int $length = 16): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify TOTP code.
     */
    private function verifyCode(string $secret, string $code): bool
    {
        $code = trim($code);
        $timeSlice = floor(time() / 30);

        for ($i = -1; $i <= 1; $i++) {
            $calculated = $this->calculateTotp($secret, (int)($timeSlice + $i));
            if (hash_equals($calculated, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate 6-digit TOTP for timeslice.
     */
    private function calculateTotp(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashPart = substr($hash, $offset, 4);
        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $pin = $value % 1000000;
        return str_pad((string)$pin, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode base32 string to binary.
     */
    private function base32Decode(string $b32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper($b32);
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            $pos = strpos($chars, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        $len = strlen($binary);
        for ($i = 0; $i + 8 <= $len; $i += 8) {
            $bytes .= chr(bindec(substr($binary, $i, 8)));
        }

        return $bytes;
    }
}
