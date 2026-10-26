@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Reseller Greeting Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-400/30">
                        {{ strtoupper($reseller->pricing_tier ?? 'Level 1') }} Wholesale Reseller
                    </span>
                    <span class="text-xs text-slate-400">Account ID: #{{ str_pad($reseller->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome, {{ $reseller->company_name ?? $reseller->name }}</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-xl">Manage your wholesale domain registrations, automated billing, API keys, and WHMCS client modules.</p>
            </div>

            <!-- Wholesale Balance Widget -->
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-5 border border-white/10 flex flex-col justify-between min-w-[240px]">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-300">Reseller Wallet Balance</span>
                <div class="mt-2">
                    <div class="text-3xl font-black text-white">
                        <x-price-display :amountUsd="$balanceUsd" />
                    </div>
                    <div class="text-xs text-indigo-300 mt-1 font-mono">
                        ≈ ৳{{ number_format($balanceBdt, 2) }} BDT
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                    <a href="{{ route('client.wallet.index') }}" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1">
                        + Add Balance
                    </a>
                    <a href="{{ route('reseller.reports.revenue') }}" class="text-xs text-slate-300 hover:text-white underline">
                        Billing Log
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Domains</p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($activeDomainsCount) }}</p>
                <a href="{{ route('client.domains.index') }}" class="text-xs text-indigo-600 hover:underline mt-0.5 block font-medium">Manage portfolio &rarr;</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Expiring in 30 Days</p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($expiringCount) }}</p>
                <a href="{{ route('reseller.reports.domains') }}" class="text-xs text-amber-600 hover:underline mt-0.5 block font-medium">View expiring list &rarr;</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">API Hits (24h)</p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">{{ number_format($apiCalls24h) }}</p>
                <a href="{{ route('reseller.api-logs') }}" class="text-xs text-purple-600 hover:underline mt-0.5 block font-medium">Live traffic logs &rarr;</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Wholesale Margin</p>
                <p class="text-2xl font-black text-slate-800 mt-0.5">Wholesale</p>
                <a href="{{ route('reseller.pricing') }}" class="text-xs text-emerald-600 hover:underline mt-0.5 block font-medium">View TLD rates &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Quick Integration & Module Setup Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    WHMCS & Billing Platform Integration
                </div>
                <h2 class="text-xl font-bold text-slate-900">Connect Your WHMCS or Custom Billing Store</h2>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl">Download our ready-to-use WHMCS Registrar Module or invoke our 44-endpoint REST API to sell domains directly under your own brand.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('reseller.api-docs') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Integration Documentation
                </a>
                <a href="{{ route('reseller.api-keys.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    Manage API Keys
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Wholesale Orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">Recent Wholesale Orders</h3>
            <a href="{{ route('client.invoices.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">View All Invoices &rarr;</a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="text-center py-12 px-4">
                <p class="text-sm text-slate-500">No domain orders placed yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Order #</th>
                            <th class="px-6 py-3.5">Type & Items</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                    #{{ $order->order_number ?? ('ORD-' . $order->id) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">
                                        @foreach($order->items as $item)
                                            <span class="inline-block mr-2">{{ $item->domain_name ?? $item->description }}</span>
                                        @endforeach
                                    </div>
                                    <span class="text-xs text-slate-400 capitalize">{{ $order->type ?? 'domain registration' }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900">
                                    <x-price-display :amountUsd="$order->total_amount" />
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($order->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                    @elseif($order->status === 'processing')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Processing</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($order->status) }}</span>
                                    @endif
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
