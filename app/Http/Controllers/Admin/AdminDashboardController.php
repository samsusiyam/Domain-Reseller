<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\RegistrarModule;
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
     * Display the Admin Dashboard.
     */
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        // Revenue metrics
        $revenueMtdUsd = (float) Transaction::where('type', 'credit')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        $revenueYtdUsd = (float) Transaction::where('type', 'credit')
            ->where('created_at', '>=', $startOfYear)
            ->sum('amount');

        $revenueMtdBdt = $this->currencyConverter->convert($revenueMtdUsd, 'USD', 'BDT');
        $revenueYtdBdt = $this->currencyConverter->convert($revenueYtdUsd, 'USD', 'BDT');

        // Domain counts
        $totalActiveDomains = Domain::where('status', 'active')->count();
        $expiringIn30Days = Domain::where('status', 'active')
            ->where('expires_at', '<=', $now->copy()->addDays(30))
            ->where('expires_at', '>=', $now)
            ->count();

        // Clients & Resellers
        $totalClients = User::where('role', 'client')->count();
        $totalResellers = User::where('role', 'reseller')->count();

        // Invoices
        $pendingInvoicesCount = Invoice::where('status', 'unpaid')->count();
        $pendingInvoicesAmount = (float) Invoice::where('status', 'unpaid')->sum('balance_due');

        // Registrar Balances
        $registrars = RegistrarModule::where('is_active', true)->get();
        $totalRegistrarBalance = (float) $registrars->sum('balance');

        // Recent Orders
        $recentOrders = Order::with('user', 'items')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'revenueMtdUsd',
            'revenueYtdUsd',
            'revenueMtdBdt',
            'revenueYtdBdt',
            'totalActiveDomains',
            'expiringIn30Days',
            'totalClients',
            'totalResellers',
            'pendingInvoicesCount',
            'pendingInvoicesAmount',
            'registrars',
            'totalRegistrarBalance',
            'recentOrders'
        ));
    }
}
