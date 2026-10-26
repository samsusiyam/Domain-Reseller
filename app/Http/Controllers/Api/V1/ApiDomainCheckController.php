<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\TldPricing;
use App\Services\Currency\CurrencyConverter;
use App\Services\Domain\DomainAvailabilityService;
use App\Services\Domain\DomainPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDomainCheckController extends BaseApiController
{
    public function __construct(
        protected DomainAvailabilityService $availabilityService,
        protected DomainPricingService $pricingService,
        protected CurrencyConverter $converter
    ) {}

    /**
     * Check availability for a single domain name.
     */
    public function check(Request $request): JsonResponse
    {
        $domain = strtolower(trim($request->query('domain', '')));

        if (empty($domain)) {
            return $this->sendError('Domain parameter is required.', [], 422);
        }

        $reseller = $request->user();
        $tld = $this->availabilityService->extractTld($domain);
        $tldModel = TldPricing::where('tld', $tld)->where('is_active', true)->first();

        if (!$tldModel) {
            return $this->sendError("TLD '{$tld}' is not currently supported.", [], 400);
        }

        try {
            $dto = $this->availabilityService->check($domain);
            $pricing = $this->pricingService->calculateItemPrice($tld, 'register', 1, $reseller);

            return $this->sendResponse([
                'domain'         => $domain,
                'tld'            => $tld,
                'is_available'   => $dto->isAvailable,
                'status'         => $dto->isAvailable ? 'available' : 'taken',
                'is_premium'     => $dto->isPremium,
                'price_usd'      => $pricing['unit_price_usd'],
                'price_bdt'      => $pricing['unit_price_bdt'],
                'currency'       => 'USD',
            ], 'Domain check completed.');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to check domain: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Bulk domain availability check (up to 50 domains).
     */
    public function checkBulk(Request $request): JsonResponse
    {
        $domains = $request->input('domains', []);

        if (!is_array($domains) || empty($domains)) {
            return $this->sendError('Domains array is required.', [], 422);
        }

        if (count($domains) > 50) {
            return $this->sendError('Maximum 50 domains allowed per bulk check request.', [], 422);
        }

        $reseller = $request->user();
        $results = [];

        foreach ($domains as $domain) {
            $domain = strtolower(trim($domain));
            if (empty($domain)) continue;

            $tld = $this->availabilityService->extractTld($domain);
            $tldModel = TldPricing::where('tld', $tld)->where('is_active', true)->first();

            if (!$tldModel) {
                $results[] = [
                    'domain'       => $domain,
                    'is_available' => false,
                    'status'       => 'unsupported_tld',
                ];
                continue;
            }

            try {
                $dto = $this->availabilityService->check($domain);
                $pricing = $this->pricingService->calculateItemPrice($tld, 'register', 1, $reseller);

                $results[] = [
                    'domain'       => $domain,
                    'tld'          => $tld,
                    'is_available' => $dto->isAvailable,
                    'status'       => $dto->isAvailable ? 'available' : 'taken',
                    'price_usd'    => $pricing['unit_price_usd'],
                    'price_bdt'    => $pricing['unit_price_bdt'],
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'domain'       => $domain,
                    'is_available' => false,
                    'status'       => 'error',
                ];
            }
        }

        return $this->sendResponse($results, 'Bulk availability check completed.');
    }

    /**
     * Domain name suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $keyword = preg_replace('/[^a-zA-Z0-9-]/', '', strtolower(trim($request->query('keyword', ''))));

        if (empty($keyword)) {
            return $this->sendError('Keyword parameter is required.', [], 422);
        }

        $tlds = ['.com', '.net', '.org', '.xyz', '.co', '.io'];
        $suggestions = [];

        foreach ($tlds as $tld) {
            $domain = $keyword . $tld;
            $tldModel = TldPricing::where('tld', $tld)->where('is_active', true)->first();
            if ($tldModel) {
                $pricing = $this->pricingService->calculateItemPrice($tld, 'register', 1, $request->user());
                $suggestions[] = [
                    'domain'    => $domain,
                    'tld'       => $tld,
                    'price_usd' => $pricing['unit_price_usd'],
                    'price_bdt' => $pricing['unit_price_bdt'],
                ];
            }
        }

        return $this->sendResponse($suggestions, 'Domain suggestions generated.');
    }
}
