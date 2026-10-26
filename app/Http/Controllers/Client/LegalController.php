<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Show Terms of Service.
     */
    public function terms()
    {
        return view('legal.terms');
    }

    /**
     * Show Privacy Policy.
     */
    public function privacy()
    {
        return view('legal.privacy');
    }
}
