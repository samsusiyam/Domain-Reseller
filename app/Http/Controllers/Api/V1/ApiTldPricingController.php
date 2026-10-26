<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TldPricing;
use App\Services\Currency\CurrencyConverter;
use App\Services\Domain\DomainPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTldPricingController extends BaseApiController
{
    public function __construct(
        protected DomainPricingService $pricingService,
        protected CurrencyConverter $currencyConverter
    ) {}

    /**
     * Complete wholesale TLD pricing matrix.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tlds = TldPricing::where('is_active', true)->orderBy('tld')->get();

        $data = $tlds->map(function ($tld) use ($user) {
            $regPrice = $this->pricingService->calculateItemPrice($tld->tld, 'register', 1, $user);
            $renewPrice = $this->pricingService->calculateItemPrice($tld->tld, 'renew', 1, $user);
            $transferPrice = $this->pricingService->calculateItemPrice($tld->tld, 'transfer', 1, $user);

            return [
                'tld'                 => $tld->tld,
                'min_years'           => $tld->min_years,
                'max_years'           => $tld->max_years,
                'register_price_usd'  => $regPrice['unit_price_usd'],
                'renew_price_usd'     => $renewPrice['unit_price_usd'],
                'transfer_price_usd'  => $transferPrice['unit_price_usd'],
                'register_price_bdt'  => $regPrice['unit_price_bdt'],
                'renew_price_bdt'     => $renewPrice['unit_price_bdt'],
                'transfer_price_bdt'  => $transferPrice['unit_price_bdt'],
                'id_protection_price' => (float)$tld->id_protection_cost,
                'dns_management'      => $tld->dns_management_available,
                'email_forwarding'    => $tld->email_forwarding_available,
                'epp_required'        => $tld->epp_code_required,
                'redemption_fee'      => (float)$tld->redemption_fee,
            ];
        });

        return $this->sendResponse($data, 'TLD wholesale pricing list retrieved.');
    }

    /**
     * Pricing and rules for a specific TLD.
     */
    public function show(Request $request, string $tld): JsonResponse
    {
        $tld = strtolower(trim($tld));
        if (!str_starts_with($tld, '.')) {
            $tld = '.' . $tld;
        }

        $tldModel = TldPricing::where('tld', $tld)->where('is_active', true)->first();

        if (!$tldModel) {
            return $this->sendError("TLD '{$tld}' is not supported or active.", [], 404);
        }

        $user = $request->user();
        $regPrice = $this->pricingService->calculateItemPrice($tld, 'register', 1, $user);
        $renewPrice = $this->pricingService->calculateItemPrice($tld, 'renew', 1, $user);
        $transferPrice = $this->pricingService->calculateItemPrice($tld, 'transfer', 1, $user);

        return $this->sendResponse([
            'tld'                 => $tldModel->tld,
            'min_years'           => $tldModel->min_years,
            'max_years'           => $tldModel->max_years,
            'register_price_usd'  => $regPrice['unit_price_usd'],
            'renew_price_usd'     => $renewPrice['unit_price_usd'],
            'transfer_price_usd'  => $transferPrice['unit_price_usd'],
            'register_price_bdt'  => $regPrice['unit_price_bdt'],
            'renew_price_bdt'     => $renewPrice['unit_price_bdt'],
            'transfer_price_bdt'  => $transferPrice['unit_price_bdt'],
            'id_protection_price' => (float)$tldModel->id_protection_cost,
            'dns_management'      => $tldModel->dns_management_available,
            'email_forwarding'    => $tldModel->email_forwarding_available,
            'epp_required'        => $tldModel->epp_code_required,
        ], "Wholesale pricing for {$tld} retrieved.");
    }
}
