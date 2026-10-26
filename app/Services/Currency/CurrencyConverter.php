<?php

namespace App\Services\Currency;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

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
}
