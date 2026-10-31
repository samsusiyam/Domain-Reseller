@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">TLD Profitability & Margins</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Audit gross profit margins between upstream supplier costs, retail pricing, and wholesale tier baselines.</p>
    </div>

    <x-admin-reports-tabs active="tlds" />

    <!-- TLDs Profitability Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">TLD Extension</th>
                        <th class="px-6 py-3.5 text-center">Active Domains</th>
                        <th class="px-6 py-3.5 text-right">Raw Cost</th>
                        <th class="px-6 py-3.5 text-right">Retail Price</th>
                        <th class="px-6 py-3.5 text-right">Retail Gross Margin</th>
                        <th class="px-6 py-3.5 text-right">Wholesale Baseline Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono text-xs">
                    @foreach($tlds as $tld)
                        <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white text-sm">{{ $tld->tld }}</td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300 font-sans">{{ number_format($tld->domains_count) }}</td>
                            <td class="px-6 py-4 text-right text-slate-500 dark:text-slate-400">${{ number_format($tld->cost_register, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">${{ number_format($tld->client_register, 2) }}</td>
                            <td class="px-6 py-4 text-right font-black {{ $tld->retail_margin > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                +${{ number_format($tld->retail_margin, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-[#F6821F]">
                                +${{ number_format($tld->reseller_margin, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
