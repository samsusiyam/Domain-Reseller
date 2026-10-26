<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\TldPricing;
use App\Services\Currency\CurrencyConverter;
use App\Services\Domain\DomainPricingService;
use Illuminate\Http\Request;

class ResellerPricingController extends Controller
{
    public function __construct(
        protected DomainPricingService $pricingService,
        protected CurrencyConverter $converter
    ) {}

    /**
     * View wholesale TLD pricing matrix for the reseller.
     */
    public function index(Request $request)
    {
        $reseller = $request->user();
        $tlds = TldPricing::where('is_active', true)->orderBy('tld')->get();

        $pricingMatrix = $tlds->map(function ($tld) use ($reseller) {
            $regPrice = $this->pricingService->calculateItemPrice($tld->tld, 'register', 1, $reseller);
            $renewPrice = $this->pricingService->calculateItemPrice($tld->tld, 'renew', 1, $reseller);
            $transferPrice = $this->pricingService->calculateItemPrice($tld->tld, 'transfer', 1, $reseller);

            return [
                'tld'                  => $tld->tld,
                'min_years'            => $tld->min_years,
                'max_years'            => $tld->max_years,
                'register_usd'         => $regPrice['unit_price_usd'],
                'register_bdt'         => $regPrice['unit_price_bdt'],
                'renew_usd'            => $renewPrice['unit_price_usd'],
                'renew_bdt'            => $renewPrice['unit_price_bdt'],
                'transfer_usd'         => $transferPrice['unit_price_usd'],
                'transfer_bdt'         => $transferPrice['unit_price_bdt'],
                'id_protection_cost'   => (float)$tld->id_protection_cost,
                'dns_management'       => $tld->dns_management_available,
                'email_forwarding'     => $tld->email_forwarding_available,
                'epp_required'         => $tld->epp_code_required,
            ];
        });

        return view('reseller.pricing.index', compact('pricingMatrix', 'reseller'));
    }
}
