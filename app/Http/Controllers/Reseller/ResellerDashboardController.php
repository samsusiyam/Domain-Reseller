<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\Order;
use App\Models\User;
use App\Services\Currency\CurrencyConverter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResellerDashboardController extends Controller
{
    public function __construct(
        protected CurrencyConverter $converter
    ) {}

    /**
     * Reseller portal dashboard.
     */
    public function index(Request $request)
    {
        $reseller = $request->user() ?? Auth::user();

        $balanceUsd = (float) $reseller->wallet_balance;
        $balanceBdt = $this->converter->convert($balanceUsd, 'USD', 'BDT');

        $activeDomainsCount = Domain::where('user_id', $reseller->id)
            ->where('status', 'active')
            ->count();

        $expiringCount = Domain::where('user_id', $reseller->id)
            ->where('status', 'active')
            ->where('expires_at', '<=', Carbon::now()->addDays(30))
            ->count();

        $apiCalls24h = ActivityLog::where('user_id', $reseller->id)
            ->where('log_name', 'api_request')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();

        $recentOrders = Order::where('user_id', $reseller->id)
            ->with('items')
            ->latest()
            ->take(8)
            ->get();

        return view('reseller.dashboard', compact(
            'reseller',
            'balanceUsd',
            'balanceBdt',
            'activeDomainsCount',
            'expiringCount',
            'apiCalls24h',
            'recentOrders'
        ));
    }
}
