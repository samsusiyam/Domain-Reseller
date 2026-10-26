<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Domain;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResellerReportController extends Controller
{
    /**
     * Domain portfolio report.
     */
    public function domains(Request $request)
    {
        $reseller = $request->user();
        $now = Carbon::now();

        $tldBreakdown = Domain::where('user_id', $reseller->id)
            ->join('tld_pricing', 'domains.tld_id', '=', 'tld_pricing.id')
            ->selectRaw('tld_pricing.tld, count(domains.id) as count')
            ->groupBy('tld_pricing.tld')
            ->get();

        $expiring30 = Domain::where('user_id', $reseller->id)
            ->where('status', 'active')
            ->whereBetween('expires_at', [$now, $now->copy()->addDays(30)])
            ->count();

        $expiring60 = Domain::where('user_id', $reseller->id)
            ->where('status', 'active')
            ->whereBetween('expires_at', [$now->copy()->addDays(31), $now->copy()->addDays(60)])
            ->count();

        $expiring90 = Domain::where('user_id', $reseller->id)
            ->where('status', 'active')
            ->whereBetween('expires_at', [$now->copy()->addDays(61), $now->copy()->addDays(90)])
            ->count();

        return view('reseller.reports.domains', compact('tldBreakdown', 'expiring30', 'expiring60', 'expiring90'));
    }

    /**
     * Wholesale expenditure and deposit history report.
     */
    public function revenue(Request $request)
    {
        $reseller = $request->user();

        $monthlySpend = Transaction::where('user_id', $reseller->id)
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, sum(amount) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $totalDeposited = (float) Transaction::where('user_id', $reseller->id)
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        $totalSpent = (float) Transaction::where('user_id', $reseller->id)
            ->where('type', 'debit')
            ->where('status', 'completed')
            ->sum('amount');

        return view('reseller.reports.revenue', compact('monthlySpend', 'totalDeposited', 'totalSpent'));
    }

    /**
     * API endpoint throughput and latency analytics.
     */
    public function apiUsage(Request $request)
    {
        $reseller = $request->user();

        $totalCalls = ActivityLog::where('user_id', $reseller->id)
            ->where('log_name', 'api_request')
            ->count();

        $recentLogs = ActivityLog::where('user_id', $reseller->id)
            ->where('log_name', 'api_request')
            ->latest('created_at')
            ->take(20)
            ->get();

        return view('reseller.reports.api-usage', compact('totalCalls', 'recentLogs'));
    }
}
