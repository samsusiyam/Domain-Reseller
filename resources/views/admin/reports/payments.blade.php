@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Payment Gateways Performance</h1>
        <p class="text-xs text-slate-500 mt-1">Audit conversion rates, successful checkouts, and volume processed across bKash, Stripe, Binance, and Manual transfers.</p>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold overflow-x-auto">
        <a href="{{ route('admin.reports.revenue') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Revenue</a>
        <a href="{{ route('admin.reports.domains') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Domains</a>
        <a href="{{ route('admin.reports.clients') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Clients</a>
        <a href="{{ route('admin.reports.resellers') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Resellers</a>
        <a href="{{ route('admin.reports.tlds') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">TLD Margins</a>
        <a href="{{ route('admin.reports.payments') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600 whitespace-nowrap">Gateways</a>
    </div>

    <!-- Gateways Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Payment Gateway</th>
                        <th class="px-6 py-3.5 text-center">Attempted Checkouts</th>
                        <th class="px-6 py-3.5 text-center">Successful Payments</th>
                        <th class="px-6 py-3.5 text-center">Success Rate</th>
                        <th class="px-6 py-3.5 text-right">Settled Volume (USD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($gateways as $gw)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-bold uppercase text-slate-900 text-xs">
                                {{ $gw->gateway ?? $gw->payment_method ?? 'Wallet Balance' }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700">
                                {{ number_format($gw->total_attempts) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-emerald-600">
                                {{ number_format($gw->successful) }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs">
                                {{ $gw->total_attempts > 0 ? number_format(($gw->successful / $gw->total_attempts) * 100, 1) : 0 }}%
                            </td>
                            <td class="px-6 py-4 text-right font-black text-slate-900 text-base">
                                ${{ number_format((float)$gw->volume, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-400">No gateway transaction data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
