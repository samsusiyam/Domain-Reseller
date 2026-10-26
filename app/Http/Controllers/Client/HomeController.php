<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TldPricing;
use App\Models\Announcement;

class HomeController extends Controller
{
    public function index()
    {
        $featuredTlds = TldPricing::where('is_active', true)
            ->whereIn('tld', ['.com', '.net', '.org', '.xyz', '.io', '.co'])
            ->get();

        if ($featuredTlds->isEmpty()) {
            $featuredTlds = TldPricing::where('is_active', true)->take(6)->get();
        }

        $recentAnnouncements = Announcement::published()->forAudience('client')->latest('published_at')->take(3)->get();

        return view('welcome', compact('featuredTlds', 'recentAnnouncements'));
    }
}
