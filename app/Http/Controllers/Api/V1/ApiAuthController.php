<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAuthController extends BaseApiController
{
    /**
     * Verify API key connection.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendResponse([
            'status'       => 'connected',
            'reseller_id'  => $user->id,
            'reseller_name'=> $user->name,
            'company'      => $user->company_name,
            'tier'         => $user->pricing_tier ?? 'standard',
            'timestamp'    => now()->toIso8601String(),
        ], 'API authentication verified successfully.');
    }
}
