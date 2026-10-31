@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Revenue & Financial Analytics</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Multi-month financial intake breakdown and payment gateway volume distribution.</p>
    </div>

    <x-admin-reports-tabs active="revenue" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Revenue Table -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Monthly Settled Revenue (USD)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Month</th>
                            <th class="px-6 py-3.5 text-right">Intake Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($monthlyRevenue as $rev)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $rev->month)->format('F Y') }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white">
                                    ${{ number_format($rev->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="p-6 text-center text-slate-400">No monthly revenue data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Gateway Stats -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Processed Volume by Gateway</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Payment Method</th>
                            <th class="px-6 py-3.5 text-center">Transactions</th>
                            <th class="px-6 py-3.5 text-right">Total Volume</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($gatewayStats as $gw)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-4 font-bold uppercase text-xs text-slate-900 dark:text-white">
                                    {{ $gw->gateway ?? $gw->payment_method ?? 'Wallet / Balance' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                    {{ number_format($gw->count) }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">
                                    ${{ number_format($gw->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="p-6 text-center text-slate-400">No gateway transaction data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
