<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\TldPricing;

class HomeController extends Controller
{
    public function index()
    {
        $popularTlds = TldPricing::where('is_active', true)
            ->orderByRaw("CASE 
                WHEN tld = '.com' THEN 1 
                WHEN tld = '.net' THEN 2 
                WHEN tld = '.org' THEN 3 
                WHEN tld = '.xyz' THEN 4 
                WHEN tld = '.io' THEN 5 
                WHEN tld = '.co' THEN 6 
                WHEN tld = '.info' THEN 7 
                WHEN tld = '.biz' THEN 8 
                WHEN tld = '.me' THEN 9 
                WHEN tld = '.online' THEN 10 
                ELSE 11 END")
            ->take(10)
            ->get();

        if ($popularTlds->isEmpty()) {
            $popularTlds = TldPricing::where('is_active', true)->take(10)->get();
        }

        $featuredTlds = $popularTlds->take(6);
        $recentAnnouncements = Announcement::published()->forAudience('client')->latest('published_at')->take(3)->get();

        return view('welcome', compact('popularTlds', 'featuredTlds', 'recentAnnouncements'));
    }
}
