<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PasskeyController extends Controller
{
    /**
     * Provide registration options for navigator.credentials.create()
     */
    public function registerOptions(Request $request)
    {
        $user = $request->user();
        $challenge = Str::random(32);
        session(['passkey_register_challenge' => $challenge]);

        return response()->json([
            'challenge' => base64_encode($challenge),
            'rp' => [
                'name' => config('app.name', 'HostNibo Domain Hub'),
                'id' => parse_url(config('app.url'), PHP_URL_HOST) ?? $request->getHost(),
            ],
            'user' => [
                'id' => base64_encode((string)$user->id),
                'name' => $user->email,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7], // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'timeout' => 60000,
            'attestation' => 'none',
        ]);
    }

    /**
     * Store registered passkey credential
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'credential_id' => ['required', 'string'],
            'public_key' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();

        $passkey = Passkey::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'credential_id' => $request->credential_id,
            'public_key' => $request->public_key,
            'device_type' => $request->device_type ?? 'platform',
            'last_used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Passkey registered successfully.',
            'passkey' => $passkey,
        ]);
    }

    /**
     * Delete passkey
     */
    public function destroy(Request $request, Passkey $passkey)
    {
        if ($passkey->user_id !== $request->user()->id) {
            abort(403);
        }

        $passkey->delete();

        return back()->with('success', 'Passkey removed successfully.');
    }

    /**
     * Provide authentication options for login navigator.credentials.get()
     */
    public function loginOptions(Request $request)
    {
        $challenge = Str::random(32);
        session(['passkey_login_challenge' => $challenge]);

        return response()->json([
            'challenge' => base64_encode($challenge),
            'rpId' => parse_url(config('app.url'), PHP_URL_HOST) ?? $request->getHost(),
            'timeout' => 60000,
            'userVerification' => 'preferred',
        ]);
    }

    /**
     * Verify passkey authentication and login
     */
    public function loginVerify(Request $request)
    {
        $request->validate([
            'credential_id' => ['required', 'string'],
        ]);

        $passkey = Passkey::where('credential_id', $request->credential_id)->first();

        if (!$passkey) {
            return response()->json([
                'success' => false,
                'message' => 'Passkey credential not recognized on this platform.',
            ], 422);
        }

        $passkey->update(['last_used_at' => now()]);

        $user = $passkey->user;
        Auth::login($user, true);

        // Merge persistent guest cart into user session
        try {
            app(\App\Services\Cart\CartService::class)->mergeOnLogin();
        } catch (\Throwable $e) {}

        $redirectUrl = route('client.dashboard');
        if ($user->isAdmin()) {
            $redirectUrl = route('admin.dashboard');
        } elseif ($user->isReseller()) {
            $redirectUrl = route('reseller.dashboard');
        }

        return response()->json([
            'success' => true,
            'redirect' => $redirectUrl,
        ]);
    }
}
