<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TldPricing;
use Illuminate\Http\Request;

class PricingPageController extends Controller
{
    public function index()
    {
        $tlds = TldPricing::where('is_active', true)->orderBy('tld')->get();
        return view('pricing', compact('tlds'));
    }

    public function resellerInfo()
    {
        $tlds = TldPricing::where('is_active', true)->orderBy('tld')->get();
        return view('reseller-program', compact('tlds'));
    }
}
