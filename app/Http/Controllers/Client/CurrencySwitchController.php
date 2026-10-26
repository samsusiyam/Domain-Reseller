<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrencySwitchController extends Controller
{
    public function switch(Request $request)
    {
        $currency = strtoupper($request->input('currency', 'USD'));

        if (in_array($currency, ['USD', 'BDT'], true)) {
            session(['currency' => $currency]);
        }

        return back();
    }
}
