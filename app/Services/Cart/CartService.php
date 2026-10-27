<?php

namespace App\Services\Cart;

use App\Models\PromoCode;
use App\Models\TldPricing;
use App\Models\User;
use App\Services\Domain\DomainAvailabilityService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const COOKIE_NAME = 'dr_cart_payload';
    protected const COOKIE_LIFETIME_MINUTES = 10080; // 7 days

    public function __construct(
        protected DomainAvailabilityService $availabilityService
    ) {}

    /**
     * Retrieve all cart items.
     */
    public function getItems(): array
    {
        $sessionItems = Session::get('cart_items', null);
        if ($sessionItems !== null && is_array($sessionItems)) {
            return $sessionItems;
        }

        // Check persistent cookie if session empty
        $cookiePayload = Cookie::get(self::COOKIE_NAME);
        if ($cookiePayload) {
            $decoded = json_decode($cookiePayload, true);
            if (is_array($decoded)) {
                Session::put('cart_items', $decoded);
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Add or update an item in cart.
     */
    public function addItem(string $domain, string $action = 'register', int $years = 1, ?string $eppCode = null, ?User $user = null): array
    {
        $domain = strtolower(trim($domain));
        $years = max(1, min(10, $years));
        $items = $this->getItems();

        $tld = $this->availabilityService->extractTld($domain);
        $tldPricing = TldPricing::where('tld', $tld)->where('is_active', true)->first();

        $unitPrice = 10.49;
        if ($tldPricing) {
            $unitPrice = match ($action) {
                'renew' => (float) $tldPricing->client_renew,
                'transfer' => (float) $tldPricing->client_transfer,
                default => (float) $tldPricing->client_register,
            };
        }

        $items[$domain] = [
            'domain' => $domain,
            'tld' => $tld,
            'action' => $action,
            'years' => $years,
            'unit_price' => round($unitPrice, 2),
            'total_price' => round($unitPrice * $years, 2),
            'epp_code' => $eppCode,
            'added_at' => now()->toIso8601String(),
        ];

        $this->saveItems($items);
        return $items[$domain];
    }

    /**
     * Update period (years) for a domain item.
     */
    public function updateYears(string $domain, int $years): ?array
    {
        $domain = strtolower(trim($domain));
        $years = max(1, min(10, $years));
        $items = $this->getItems();

        if (!isset($items[$domain])) {
            return null;
        }

        $items[$domain]['years'] = $years;
        $items[$domain]['total_price'] = round($items[$domain]['unit_price'] * $years, 2);

        $this->saveItems($items);
        return $items[$domain];
    }

    /**
     * Remove an item from cart.
     */
    public function removeItem(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        $items = $this->getItems();

        if (isset($items[$domain])) {
            unset($items[$domain]);
            $this->saveItems($items);
            return true;
        }

        return false;
    }

    /**
     * Clear all cart contents.
     */
    public function clear(): void
    {
        Session::forget('cart_items');
        Session::forget('cart_promo');
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    /**
     * Apply promo code to cart.
     */
    public function applyPromoCode(string $code): array
    {
        $code = strtoupper(trim($code));
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo || !$promo->is_active) {
            return ['success' => false, 'message' => 'Invalid or inactive promotional code.'];
        }

        if ($promo->valid_from && now()->lt($promo->valid_from)) {
            return ['success' => false, 'message' => 'This promo code is not active yet.'];
        }

        if ($promo->valid_to && now()->gt($promo->valid_to)) {
            return ['success' => false, 'message' => 'This promo code has expired.'];
        }

        if ($promo->max_uses && $promo->current_uses >= $promo->max_uses) {
            return ['success' => false, 'message' => 'This promo code has reached its maximum usage limit.'];
        }

        Session::put('cart_promo', [
            'id' => $promo->id,
            'code' => $promo->code,
            'discount_type' => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
        ]);

        return ['success' => true, 'promo' => $promo];
    }

    public function removePromoCode(): void
    {
        Session::forget('cart_promo');
    }

    public function getPromo(): ?array
    {
        return Session::get('cart_promo', null);
    }

    /**
     * Calculate cart subtotal, discount, and grand total in USD.
     */
    public function getTotals(): array
    {
        $items = $this->getItems();
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) ($item['total_price'] ?? 0);
        }

        $discount = 0.0;
        $promo = $this->getPromo();
        if ($promo && $subtotal > 0) {
            if (($promo['discount_type'] ?? '') === 'percentage') {
                $discount = round(($subtotal * ($promo['discount_value'] / 100)), 2);
            } else {
                $discount = round(min($subtotal, (float) $promo['discount_value']), 2);
            }
        }

        $total = max(0.0, round($subtotal - $discount, 2));

        return [
            'item_count' => count($items),
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
            'promo' => $promo,
        ];
    }

    /**
     * Re-validate all cart items for live availability and catalog price integrity.
     */
    public function validateCart(): array
    {
        $items = $this->getItems();
        $warnings = [];
        $hasUnavailable = false;

        foreach ($items as $domain => &$item) {
            $tld = $this->availabilityService->extractTld($domain);
            $tldPricing = TldPricing::where('tld', $tld)->where('is_active', true)->first();

            if (!$tldPricing) {
                $warnings[] = "TLD {$tld} for domain {$domain} is currently not available for purchase.";
                $hasUnavailable = true;
                continue;
            }

            $currentUnitPrice = match ($item['action']) {
                'renew' => (float) $tldPricing->client_renew,
                'transfer' => (float) $tldPricing->client_transfer,
                default => (float) $tldPricing->client_register,
            };

            if (abs($currentUnitPrice - (float) $item['unit_price']) > 0.01) {
                $item['unit_price'] = $currentUnitPrice;
                $item['total_price'] = round($currentUnitPrice * $item['years'], 2);
                $warnings[] = "Price for {$domain} was updated to current wholesale rate: \${$currentUnitPrice}/yr.";
            }

            // If action is register, verify not registered yet
            if ($item['action'] === 'register' && !config('registrars.test_mode', false)) {
                $check = $this->availabilityService->check($domain);
                if (!$check->isAvailable && $check->status === 'taken') {
                    $warnings[] = "Domain {$domain} is already registered and no longer available.";
                    $hasUnavailable = true;
                }
            }
        }

        $this->saveItems($items);

        return [
            'is_valid' => !$hasUnavailable,
            'warnings' => $warnings,
            'items' => $items,
        ];
    }

    /**
     * Merge guest cart cookie on login.
     */
    public function mergeOnLogin(): void
    {
        $cookiePayload = Cookie::get(self::COOKIE_NAME);
        if ($cookiePayload) {
            $cookieItems = json_decode($cookiePayload, true);
            if (is_array($cookieItems) && !empty($cookieItems)) {
                $sessionItems = Session::get('cart_items', []);
                $merged = array_merge($cookieItems, $sessionItems);
                $this->saveItems($merged);
            }
        }
    }

    protected function saveItems(array $items): void
    {
        Session::put('cart_items', $items);
        Session::put('cart', $items); // alias for backwards compatibility
        Cookie::queue(self::COOKIE_NAME, json_encode($items), self::COOKIE_LIFETIME_MINUTES);
    }
}
