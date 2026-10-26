<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SystemSetting;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAccountController extends BaseApiController
{
    public function __construct(
        protected CurrencyConverter $currencyConverter
    ) {}

    /**
     * Reseller profile and white-label info.
     */
    public function info(Request $request): JsonResponse
    {
        $user = $request->user();
        $prefix = "reseller_{$user->id}_";

        return $this->sendResponse([
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'company'      => $user->company_name,
            'tier'         => $user->pricing_tier ?? 'standard',
            'country'      => $user->country,
            'whitelabel'   => [
                'ns1'           => SystemSetting::get($prefix . 'ns1', config('dns_templates.default_nameservers.ns1')),
                'ns2'           => SystemSetting::get($prefix . 'ns2', config('dns_templates.default_nameservers.ns2')),
                'brand_name'    => SystemSetting::get($prefix . 'brand_name', $user->company_name),
                'support_email' => SystemSetting::get($prefix . 'support_email', $user->email),
            ],
            'created_at'   => $user->created_at?->toIso8601String(),
        ], 'Account information retrieved.');
    }

    /**
     * Reseller live wallet credit balance.
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();
        $usd = (float) $user->balance;
        $bdt = $this->currencyConverter->usdToBdt($usd);

        return $this->sendResponse([
            'currency_primary' => 'USD',
            'balance_usd'      => $usd,
            'balance_formatted_usd' => '$' . number_format($usd, 2),
            'balance_bdt'      => $bdt,
            'balance_formatted_bdt' => '৳' . number_format($bdt, 2),
        ], 'Account balance retrieved.');
    }
}
