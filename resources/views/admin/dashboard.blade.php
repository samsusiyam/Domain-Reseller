@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Top Greeting & Stat Summary -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">System Overview Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1">Platform operations, wholesale upstream registrar balances, and real-time ledger.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Services Operational
            </span>
        </div>
    </div>

    <!-- Revenue & Core Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Revenue MTD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Revenue (MTD)</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-slate-900">${{ number_format($revenueMtdUsd, 2) }}</div>
                <div class="text-xs text-slate-400 font-mono mt-0.5">≈ ৳{{ number_format($revenueMtdBdt, 2) }} BDT</div>
            </div>
        </div>

        <!-- Revenue YTD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Revenue (YTD)</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-slate-900">${{ number_format($revenueYtdUsd, 2) }}</div>
                <div class="text-xs text-slate-400 font-mono mt-0.5">≈ ৳{{ number_format($revenueYtdBdt, 2) }} BDT</div>
            </div>
        </div>

        <!-- Active Domains & Expirations -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Domains</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalActiveDomains) }}</div>
                <div class="text-xs font-semibold text-amber-600">{{ $expiringIn30Days }} Expiring < 30d</div>
            </div>
        </div>

        <!-- Users Breakdown -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Accounts</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between text-xs">
                <div><span class="text-2xl font-black text-slate-900">{{ $totalClients }}</span> <span class="text-slate-400 font-medium">Clients</span></div>
                <div><span class="text-2xl font-black text-indigo-600">{{ $totalResellers }}</span> <span class="text-slate-400 font-medium">Resellers</span></div>
            </div>
        </div>
    </div>

    <!-- Upstream Registrar Balances Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Upstream Registrar Module Balances</h3>
                <p class="text-xs text-slate-500">Live wholesale credit at your suppliers (Namecheap, Spaceship, Dynadot, AtakDomain, NameSilo).</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Upstream Balance:</span>
                <span class="text-xl font-black text-emerald-600">${{ number_format($totalRegistrarBalance, 2) }} USD</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @forelse($registrars as $reg)
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-slate-800">{{ $reg->name }}</span>
                        <span class="w-2 h-2 rounded-full {{ $reg->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                    </div>
                    <div class="mt-3">
                        <div class="text-lg font-black text-slate-900">${{ number_format((float)$reg->balance, 2) }}</div>
                        <span class="text-[10px] text-slate-400 capitalize">{{ $reg->environment }}</span>
                    </div>
                    <form action="{{ route('admin.registrars.sync-balance', $reg->id) }}" method="POST" class="mt-3 pt-2 border-t border-slate-200">
                        @csrf
                        <button type="submit" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Sync Balance
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full text-center py-6 text-sm text-slate-400">
                    No active registrar modules found. <a href="{{ route('admin.registrars.index') }}" class="text-indigo-600 underline font-semibold">Configure registrars</a>.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Management Action Shortcuts -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('admin.clients.create') }}" class="p-4 bg-white rounded-xl shadow-sm border border-slate-200 hover:border-indigo-400 transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block">Add Client</span>
                <span class="text-[11px] text-slate-400">Create client profile</span>
            </div>
        </a>

        <a href="{{ route('admin.resellers.create') }}" class="p-4 bg-white rounded-xl shadow-sm border border-slate-200 hover:border-indigo-400 transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block">Add Reseller</span>
                <span class="text-[11px] text-slate-400">Setup wholesale account</span>
            </div>
        </a>

        <a href="{{ route('admin.tlds.create') }}" class="p-4 bg-white rounded-xl shadow-sm border border-slate-200 hover:border-indigo-400 transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block">New TLD Extension</span>
                <span class="text-[11px] text-slate-400">Add pricing matrix</span>
            </div>
        </a>

        <a href="{{ route('admin.payments.manual.index') }}" class="p-4 bg-white rounded-xl shadow-sm border border-slate-200 hover:border-indigo-400 transition flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block">Manual Approvals</span>
                <span class="text-[11px] text-slate-400">Review offline deposits</span>
            </div>
        </a>
    </div>

    <!-- Recent Domain Orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Recent Domain Orders & Provisioning</h3>
                <p class="text-xs text-slate-400 mt-0.5">Real-time registrations and provisioning status.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold">All Orders &rarr;</a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="text-center py-12 px-4 text-slate-400 text-sm">No orders recorded yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Order #</th>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Domain Items</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        #{{ $order->order_number ?? ('ORD-' . $order->id) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $order->user?->name ?? 'Guest/Unknown' }}</div>
                                    <span class="text-xs text-slate-400">{{ $order->user?->email }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @foreach($order->items as $it)
                                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-800 text-xs font-mono rounded mr-1">
                                            {{ $it->domain_name ?? $it->description }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-900">
                                    ${{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($order->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                                    @elseif($order->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                    @elseif($order->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Failed</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">View &rarr;</a>
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
