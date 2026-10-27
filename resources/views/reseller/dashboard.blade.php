@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Reseller Header Card (Clean Minimalist, No AI Gradients) -->
    <div class="bg-white dark:bg-[#111827] rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase tracking-wider bg-orange-50 text-[#F6821F] dark:bg-orange-950/40 border border-orange-200 dark:border-orange-900/40">
                    {{ strtoupper($reseller->pricing_tier ?? 'Level 1') }} Wholesale Tier
                </span>
                <span class="text-xs text-slate-400 font-mono">Account ID: #{{ str_pad($reseller->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome, {{ $reseller->company_name ?? $reseller->name }}</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 max-w-xl">Manage your wholesale domain portfolio, automated WHMCS provisioning, and REST API credentials.</p>
        </div>

        <!-- Wholesale Balance Widget -->
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4 border border-slate-200 dark:border-slate-700/60 flex flex-col justify-between min-w-[240px]">
            <span class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Wholesale Wallet Balance</span>
            <div class="mt-1.5">
                <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white tabular-nums">
                    <x-price-display :amountUsd="$balanceUsd" />
                </div>
                <div class="text-xs text-slate-400 mt-0.5 font-mono">
                    ≈ ৳{{ number_format($balanceBdt, 2) }} BDT
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <a href="{{ route('client.wallet.index') }}" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                    + Add Funds
                </a>
                <a href="{{ route('reseller.reports.revenue') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white underline">
                    Billing Log
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Active Domains</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($activeDomainsCount) }}</div>
            <a href="{{ route('client.domains.index') }}" class="text-xs text-[#F6821F] hover:underline mt-1 block font-medium">Manage portfolio &rarr;</a>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-amber-500 uppercase tracking-wider">Expiring in 30 Days</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($expiringCount) }}</div>
            <a href="{{ route('reseller.reports.domains') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 mt-1 block">View expiry report &rarr;</a>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">API Logs (Last 30d)</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($apiCalls24h) }}</div>
            <a href="{{ route('reseller.api-logs') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 mt-1 block">API usage report &rarr;</a>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs">
            <span class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider">Pricing Tier</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1">{{ ucfirst($reseller->pricing_tier ?? 'Wholesale') }}</div>
            <a href="{{ route('reseller.pricing') }}" class="text-xs text-emerald-600 hover:underline mt-1 block">View tier rates &rarr;</a>
        </div>
    </div>

    <!-- Quick Integration & Module Setup Card -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-mono uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 mb-2">
                    WHMCS & Billing Platform Integration
                </span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Connect Your Hosting Platform & WHMCS</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl">Use our turnkey WHMCS Registrar Module or 44 REST API endpoints for automated client domain ordering, renewals, and DNS sync.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reseller.api-docs') }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium cursor-pointer">
                    API Documentation
                </a>
                <a href="{{ route('reseller.api-keys.index') }}" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                    Manage API Credentials
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Wholesale Orders -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Recent Wholesale Orders</h3>
            <a href="{{ route('client.invoices.index') }}" class="text-xs text-[#F6821F] hover:underline font-medium">View all invoices &rarr;</a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="text-center py-10 px-4">
                <p class="text-xs text-slate-400">No domain orders placed yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                        <tr>
                            <th class="px-5 py-3">Order #</th>
                            <th class="px-5 py-3">Domain Items</th>
                            <th class="px-5 py-3 text-right">Wholesale Amount</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ $order->order_number ?? $order->id }}
                                </td>
                                <td class="px-5 py-3">
                                    @foreach($order->items as $item)
                                        <div class="font-mono text-xs">{{ $item->domain_name }} <span class="text-[10px] text-slate-400">({{ $item->years }} yr)</span></div>
                                    @endforeach
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                    ${{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ $order->status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-slate-400 font-mono text-[11px]">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
