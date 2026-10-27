<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Currency\CurrencyConverter;
use Illuminate\Http\Request;

class CurrencySwitchController extends Controller
{
    public function __construct(
        protected CurrencyConverter $currencyConverter
    ) {}

    /**
     * Switch currency across session and cookie.
     */
    public function switch(Request $request)
    {
        $currency = strtoupper($request->input('currency', 'USD'));

        if (!in_array($currency, ['USD', 'BDT'], true)) {
            $currency = 'USD';
        }

        session(['currency' => $currency]);

        $cookie = cookie('currency', $currency, 525600, '/', null, false, false);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'currency' => $currency,
                'rate' => $this->currencyConverter->getRate('BDT'),
            ])->withCookie($cookie);
        }

        return back()->withCookie($cookie);
    }

    /**
     * Return live exchange rates.
     */
    public function rates()
    {
        $bdtRate = $this->currencyConverter->getRate('BDT');

        return response()->json([
            'success' => true,
            'base' => 'USD',
            'rates' => [
                'USD' => 1.0,
                'BDT' => (float) $bdtRate,
            ],
            'rate' => (float) $bdtRate,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
