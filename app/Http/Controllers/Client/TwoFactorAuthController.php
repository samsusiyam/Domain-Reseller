<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorAuthController extends Controller
{
    /**
     * Show 2FA settings and status.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return view('client.two-factor.index', compact('user'));
    }

    /**
     * Generate 2FA secret key and show QR setup.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('client.two-factor.index')->with('info', 'Two-Factor Authentication is already enabled.');
        }

        // Generate 16-character base32 secret if not already set
        $secret = $user->two_factor_secret ?: $this->generateBase32Secret();
        $user->forceFill(['two_factor_secret' => $secret])->save();

        $appName = urlencode(config('app.name', 'DomainReseller'));
        $qrUrl = "otpauth://totp/{$appName}:" . urlencode($user->email) . "?secret={$secret}&issuer={$appName}";

        return view('client.two-factor.enable', compact('user', 'secret', 'qrUrl'));
    }

    /**
     * Confirm 2FA code and activate protection.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:8'],
        ]);

        $user = $request->user();
        $secret = $user->two_factor_secret;

        if (!$secret || !$this->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
        }

        // Generate 8 emergency recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ])->save();

        session(['2fa_verified' => true]);

        return redirect()->route('client.two-factor.index')
            ->with('success', 'Two-Factor Authentication has been enabled successfully.')
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
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

        return redirect()->route('client.two-factor.index')->with('success', 'Two-Factor Authentication has been disabled.');
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
     * Verify standard TOTP code.
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
            $pos = strpos($chars, $b32[$i]);
            if ($pos !== false) {
                $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }
        $bytes = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            if ($i + 8 <= strlen($binary)) {
                $bytes .= chr(bindec(substr($binary, $i, 8)));
            }
        }
        return $bytes;
    }
}
