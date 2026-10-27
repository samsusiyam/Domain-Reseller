<?php

namespace App\Services\Currency;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyConverter
{
    /**
     * Convert USD amount to target currency.
     */
    public function convert(float $amountInUsd, string $targetCurrency = 'USD'): float
    {
        if (strtoupper($targetCurrency) === 'USD') {
            return $amountInUsd;
        }

        $rate = $this->getRate($targetCurrency);
        return round($amountInUsd * $rate, 2);
    }

    /**
     * Convert USD to BDT directly.
     */
    public function usdToBdt(float $usd): float
    {
        return $this->convert($usd, 'BDT');
    }

    /**
     * Convert BDT to USD directly.
     */
    public function bdtToUsd(float $bdt): float
    {
        $rate = $this->getRate('BDT');
        return $rate > 0 ? round($bdt / $rate, 4) : 0.0;
    }

    /**
     * Get live or cached exchange rate for a currency code.
     */
    public function getRate(string $code): float
    {
        $code = strtoupper(trim($code));

        return Cache::remember("currency_rate_{$code}", 3600, function () use ($code) {
            try {
                $currency = Currency::where('code', $code)->first();
                if ($currency) {
                    return (float) $currency->exchange_rate;
                }
            } catch (\Throwable $e) {
            }

            if ($code === 'BDT') {
                return (float) config('currencies.fallback_usd_to_bdt', 120.00);
            }

            return 1.0;
        });
    }

    /**
     * Format price in active user currency or specified currency.
     */
    public function format(float $amountInUsd, ?string $currencyCode = null): string
    {
        $code = strtoupper($currencyCode ?: session('currency', config('currencies.default', 'USD')));
        $currency = Currency::where('code', $code)->first();

        if (!$currency) {
            return '$' . number_format($amountInUsd, 2);
        }

        return $currency->format($amountInUsd);
    }

    /**
     * Fetch live exchange rates from open exchange rate API and sync currencies table.
     */
    public function syncRates(): bool
    {
        try {
            $response = Http::timeout(10)->get('https://open.er-api.com/v6/latest/USD');

            if (!$response->successful()) {
                // Secondary fallback API
                $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');
            }

            if (!$response->successful()) {
                throw new \Exception('Exchange rate API returned status ' . $response->status());
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

            if (empty($rates)) {
                throw new \Exception('Exchange rate payload did not contain rates list.');
            }

            $currencies = Currency::where('is_primary', false)->get();
            $updatedCount = 0;

            foreach ($currencies as $curr) {
                $code = strtoupper($curr->code);
                if (isset($rates[$code]) && is_numeric($rates[$code])) {
                    $newRate = (float) $rates[$code];
                    $curr->update([
                        'exchange_rate' => $newRate,
                        'last_updated_at' => now(),
                    ]);
                    Cache::forget("currency_rate_{$code}");
                    $updatedCount++;
                }
            }

            Log::info("Synchronized exchange rates for {$updatedCount} secondary currencies.");
            return true;
        } catch (\Throwable $e) {
            Log::error('Currency sync failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
