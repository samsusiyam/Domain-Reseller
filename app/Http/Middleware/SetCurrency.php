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
        if ($request->has('currency')) {
            $currency = strtoupper($request->query('currency'));
            if (in_array($currency, ['USD', 'BDT'], true)) {
                session(['currency' => $currency]);
            }
        }

        if (!session()->has('currency')) {
            session(['currency' => config('currencies.default', 'USD')]);
        }

        return $next($request);
    }
}
