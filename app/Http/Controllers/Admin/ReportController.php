<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\TldPricing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Revenue & Profit Report.
     */
    public function revenue(Request $request)
    {
        $monthlyRevenue = Transaction::where('type', 'payment')
            ->where('status', 'completed')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, sum(amount) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $gatewayStats = Transaction::where('status', 'completed')
            ->selectRaw("payment_method, sum(amount) as total, count(*) as count")
            ->groupBy('payment_method')
            ->get();

        return view('admin.reports.revenue', compact('monthlyRevenue', 'gatewayStats'));
    }

    /**
     * Domain Volume and Expiration Analytics.
     */
    public function domains(Request $request)
    {
        $statusBreakdown = Domain::selectRaw("status, count(*) as count")
            ->groupBy('status')
            ->get();

        $tldBreakdown = Domain::join('tld_pricing', 'domains.tld_id', '=', 'tld_pricing.id')
            ->selectRaw("tld_pricing.tld, count(domains.id) as count")
            ->groupBy('tld_pricing.tld')
            ->orderByDesc('count')
            ->get();

        $expiringMonthly = Domain::where('status', 'active')
            ->selectRaw("DATE_FORMAT(expires_at, '%Y-%m') as month, count(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->take(6)
            ->get();

        return view('admin.reports.domains', compact('statusBreakdown', 'tldBreakdown', 'expiringMonthly'));
    }

    /**
     * Client Growth & Top Spenders.
     */
    public function clients(Request $request)
    {
        $topSpenders = User::where('role', 'client')
            ->withSum(['transactions as total_spent' => function ($q) {
                $q->where('type', 'payment')->where('status', 'completed');
            }], 'amount')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        return view('admin.reports.clients', compact('topSpenders'));
    }

    /**
     * Reseller Wholesale Volume & Tier Performance.
     */
    public function resellers(Request $request)
    {
        $resellerStats = User::where('role', 'reseller')
            ->withCount('domains', 'orders')
            ->withSum(['transactions as wholesale_volume' => function ($q) {
                $q->where('type', 'payment')->where('status', 'completed');
            }], 'amount')
            ->orderByDesc('wholesale_volume')
            ->take(15)
            ->get();

        return view('admin.reports.resellers', compact('resellerStats'));
    }

    /**
     * TLD Profitability Report.
     */
    public function tlds(Request $request)
    {
        $tlds = TldPricing::withCount('domains')->get()->map(function ($tld) {
            $tld->retail_margin = $tld->client_register - $tld->cost_register;
            $tld->reseller_margin = $tld->default_reseller_register - $tld->cost_register;
            return $tld;
        });

        return view('admin.reports.tlds', compact('tlds'));
    }

    /**
     * Payment Gateway breakdown and throughput.
     */
    public function payments(Request $request)
    {
        $gateways = Transaction::selectRaw("payment_method, count(*) as total_attempts, sum(case when status='completed' then 1 else 0 end) as successful, sum(case when status='completed' then amount else 0 end) as volume")
            ->groupBy('payment_method')
            ->get();

        return view('admin.reports.payments', compact('gateways'));
    }
}
