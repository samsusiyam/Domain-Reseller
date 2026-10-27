<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check query parameter first
        if ($request->has('currency')) {
            $currency = strtoupper($request->input('currency'));
            if (in_array($currency, ['USD', 'BDT'], true)) {
                session(['currency' => $currency]);
            }
        }
        // 2. Check unencrypted cookie
        elseif ($request->hasCookie('currency')) {
            $cookieCurrency = strtoupper($request->cookie('currency'));
            if (in_array($cookieCurrency, ['USD', 'BDT'], true)) {
                session(['currency' => $cookieCurrency]);
            }
        }

        // 3. Fallback to default USD
        if (!session()->has('currency')) {
            session(['currency' => config('currencies.default', 'USD')]);
        }

        $response = $next($request);

        // Ensure response sends the cookie to client
        $current = session('currency', 'USD');
        if ($request->cookie('currency') !== $current && method_exists($response, 'withCookie')) {
            $response->withCookie(cookie('currency', $current, 525600, '/', null, false, false));
        }

        return $response;
    }
}
