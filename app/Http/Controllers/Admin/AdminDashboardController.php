<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\RegistrarModule;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Currency\CurrencyConverter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected CurrencyConverter $currencyConverter
    ) {}

    /**
     * Display the Admin Dashboard Overview.
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $range = $request->query('range', '30d');

        // Date range boundaries
        [$startDate, $prevStartDate, $prevEndDate, $chartDays] = match($range) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                1
            ],
            '7d' => [
                $now->copy()->subDays(7)->startOfDay(),
                $now->copy()->subDays(14)->startOfDay(),
                $now->copy()->subDays(7)->endOfDay(),
                7
            ],
            'mtd' => [
                $now->copy()->startOfMonth(),
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                $now->day
            ],
            'ytd' => [
                $now->copy()->startOfYear(),
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
                $now->dayOfYear
            ],
            default => [ // 30d
                $now->copy()->subDays(30)->startOfDay(),
                $now->copy()->subDays(60)->startOfDay(),
                $now->copy()->subDays(30)->endOfDay(),
                30
            ],
        };

        // Revenue calculations
        $revenueUsd = (float) Transaction::where('type', 'credit')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $prevRevenueUsd = (float) Transaction::where('type', 'credit')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        $revenueDelta = $prevRevenueUsd > 0
            ? round((($revenueUsd - $prevRevenueUsd) / $prevRevenueUsd) * 100, 1)
            : ($revenueUsd > 0 ? 100.0 : 0.0);

        $revenueBdt = $this->currencyConverter->usdToBdt($revenueUsd);

        // Revenue chart series data
        $chartPoints = [];
        $daysCount = min($chartDays, 30);
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $daySum = (float) Transaction::where('type', 'credit')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('amount');

            $chartPoints[] = [
                'date' => $day->format('M d'),
                'amount' => round($daySum, 2),
            ];
        }

        // Domain counts
        $totalActiveDomains = Domain::where('status', 'active')->count();
        $expiringIn30Days = Domain::where('status', 'active')
            ->where('expires_at', '<=', $now->copy()->addDays(30))
            ->where('expires_at', '>=', $now)
            ->count();

        // Clients & Resellers
        $totalClients = User::where('role', 'client')->count();
        $totalResellers = User::where('role', 'reseller')->count();

        // Registrar Balances & Statuses
        $registrars = RegistrarModule::where('is_active', true)->get();
        $totalRegistrarBalance = (float) $registrars->sum('balance');
        $totalRegistrarBalanceBdt = $this->currencyConverter->usdToBdt($totalRegistrarBalance);

        // Alert conditions
        $lowBalanceRegistrars = $registrars->filter(function ($r) {
            $threshold = (float) ($r->low_balance_threshold ?? 50.00);
            return (float) $r->balance < $threshold;
        });

        $pendingManualPaymentsCount = Invoice::where('status', 'unpaid')
            ->whereNotNull('notes')
            ->where('notes', 'like', '%manual%')
            ->count();

        $failedOrdersCount = Order::where('status', 'failed')->count();
        $openTicketsCount = SupportTicket::whereIn('status', ['open', 'client_reply'])->count();

        // Recent Orders
        $recentOrders = Order::with('user', 'items')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'range',
            'revenueUsd',
            'revenueBdt',
            'revenueDelta',
            'chartPoints',
            'totalActiveDomains',
            'expiringIn30Days',
            'totalClients',
            'totalResellers',
            'registrars',
            'totalRegistrarBalance',
            'totalRegistrarBalanceBdt',
            'lowBalanceRegistrars',
            'pendingManualPaymentsCount',
            'failedOrdersCount',
            'openTicketsCount',
            'recentOrders'
        ));
    }
}
