<?php

namespace App\Services\Domain;

use App\Models\User;
use App\Models\TldPricing;
use App\Models\PromoCode;
use App\Services\Currency\CurrencyConverter;

class DomainPricingService
{
    public function __construct(
        protected CurrencyConverter $currencyConverter
    ) {}

    /**
     * Get price in USD for user based on role, overrides, and action.
     */
    public function calculateItemPrice(
        string $tld,
        string $action, // register, renew, transfer
        int $years,
        ?User $user = null
    ): array {
        $tldModel = TldPricing::where('tld', $tld)->firstOrFail();
        $unitPriceUsd = $tldModel->getPriceForUser($user, $action);
        $totalUsd = $unitPriceUsd * $years;
        $unitPriceBdt = $this->currencyConverter->usdToBdt($unitPriceUsd);
        $totalBdt = $unitPriceBdt * $years;

        return [
            'unit_price_usd' => $unitPriceUsd,
            'total_usd' => $totalUsd,
            'unit_price_bdt' => $unitPriceBdt,
            'total_bdt' => $totalBdt,
            'registrar_cost_usd' => match ($action) {
                'renew' => (float) $tldModel->cost_renew * $years,
                'transfer' => (float) $tldModel->cost_transfer,
                default => (float) $tldModel->cost_register * $years,
            },
        ];
    }

    /**
     * Calculate cart / order total with optional promo code and tax.
     */
    public function calculateOrderTotals(array $items, ?User $user = null, ?string $promoCodeStr = null): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $tld = $item['tld'] ?? (str_contains($item['domain'] ?? '', '.') ? '.' . ltrim(substr(strrchr($item['domain'], '.'), 1), '.') : '');
            $pricing = $this->calculateItemPrice(
                $tld,
                $item['action'] ?? 'register',
                $item['years'] ?? 1,
                $user
            );
            $subtotal += $pricing['total_usd'];

            // Add optional addons if enabled and priced
            if (!empty($tld)) {
                $tldModel = TldPricing::where('tld', $tld)->first();
                $term = (int) ($item['years'] ?? 1);
                if ($tldModel) {
                    if (!empty($item['id_protection']) && $tldModel->id_protection_available && (float) $tldModel->id_protection_cost > 0) {
                        $subtotal += ((float) $tldModel->id_protection_cost * $term);
                    }
                    if (!empty($item['dns_management']) && $tldModel->dns_management_available && (float) $tldModel->dns_management_cost > 0) {
                        $subtotal += ((float) $tldModel->dns_management_cost * $term);
                    }
                    if (!empty($item['email_forwarding']) && $tldModel->email_forwarding_available && (float) $tldModel->email_forwarding_cost > 0) {
                        $subtotal += ((float) $tldModel->email_forwarding_cost * $term);
                    }
                }
            }
        }

        $discount = 0.0;
        $promoModel = null;

        if ($promoCodeStr && $user) {
            $promo = PromoCode::where('code', strtoupper(trim($promoCodeStr)))->first();
            if ($promo && $promo->isValidFor($subtotal, $user)) {
                $discount = $promo->calculateDiscount($subtotal);
                $promoModel = $promo;
            }
        }

        $total = max(0.0, $subtotal - $discount);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'promo_code' => $promoModel,
        ];
    }
}
