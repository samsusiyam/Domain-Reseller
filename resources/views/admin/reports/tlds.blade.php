@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">TLD Profitability & Margins</h1>
        <p class="text-xs text-slate-500 mt-1">Audit gross profit margins between upstream supplier costs, retail pricing, and wholesale tier baselines.</p>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold overflow-x-auto">
        <a href="{{ route('admin.reports.revenue') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Revenue</a>
        <a href="{{ route('admin.reports.domains') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Domains</a>
        <a href="{{ route('admin.reports.clients') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Clients</a>
        <a href="{{ route('admin.reports.resellers') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Resellers</a>
        <a href="{{ route('admin.reports.tlds') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600 whitespace-nowrap">TLD Margins</a>
        <a href="{{ route('admin.reports.payments') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Gateways</a>
    </div>

    <!-- TLDs Profitability Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">TLD Extension</th>
                        <th class="px-6 py-3.5 text-center">Active Domains</th>
                        <th class="px-6 py-3.5 text-right">Raw Cost</th>
                        <th class="px-6 py-3.5 text-right">Retail Price</th>
                        <th class="px-6 py-3.5 text-right">Retail Gross Margin</th>
                        <th class="px-6 py-3.5 text-right">Wholesale Baseline Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @foreach($tlds as $tld)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-bold text-slate-900 text-sm">{{ $tld->tld }}</td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700 font-sans">{{ number_format($tld->domains_count) }}</td>
                            <td class="px-6 py-4 text-right text-slate-500">${{ number_format($tld->cost_register, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">${{ number_format($tld->client_register, 2) }}</td>
                            <td class="px-6 py-4 text-right font-black {{ $tld->retail_margin > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                +${{ number_format($tld->retail_margin, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-indigo-700">
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
