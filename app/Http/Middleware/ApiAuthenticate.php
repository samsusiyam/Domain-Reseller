<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key') ?? $request->input('api_key');
        $apiSecret = $request->header('X-Api-Secret') ?? $request->input('api_secret');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'API Key missing in request headers (X-Api-Key).',
            ], 401);
        }

        // Hash provided API Key to find in database
        $hashedKey = hash('sha256', $apiKey);
        $keyRecord = ApiKey::where('api_key', $hashedKey)->where('status', 'active')->first();

        // Also fallback to direct match if stored unhashed in local testing
        if (!$keyRecord) {
            $keyRecord = ApiKey::where('api_key', $apiKey)->where('status', 'active')->first();
        }

        if (!$keyRecord) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Invalid or inactive API Key.',
            ], 401);
        }

        $user = $keyRecord->user;
        if (!$user || $user->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'code' => 403,
                'message' => 'Account is suspended or invalid.',
            ], 403);
        }

        $keyRecord->update(['last_used_at' => now()]);
        Auth::setUser($user);
        $request->attributes->set('api_key', $keyRecord);

        return $next($request);
    }
}
