@extends('layouts.reseller')

@section('content')
<div class="space-y-6" x-data="{ search: '' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Wholesale TLD Pricing Matrix</h1>
            <p class="text-sm text-slate-500 mt-1">Special tier wholesale rates applied to your reseller account for registrations, renewals, and incoming transfers.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold uppercase tracking-wide">
                {{ strtoupper($reseller->pricing_tier ?? 'Level 1') }} Pricing Tier
            </span>
            <a href="{{ url('/api/v1/tld-pricing') }}" target="_blank" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                View JSON API
            </a>
        </div>
    </div>

    <!-- Search Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" x-model="search" placeholder="Search TLD (e.g. .com, .net, .io)..."
                class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="text-xs text-slate-500">
            Total TLDs Available: <strong class="text-slate-800">{{ count($pricingMatrix) }}</strong>
        </div>
    </div>

    <!-- Pricing Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">TLD Extension</th>
                        <th class="px-6 py-3.5 text-right">Register (1 Yr)</th>
                        <th class="px-6 py-3.5 text-right">Renew (1 Yr)</th>
                        <th class="px-6 py-3.5 text-right">Transfer</th>
                        <th class="px-6 py-3.5 text-center">ID Protection</th>
                        <th class="px-6 py-3.5 text-center">DNS Zone</th>
                        <th class="px-6 py-3.5 text-center">EPP Req</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($pricingMatrix as $item)
                        <tr class="hover:bg-slate-50/60 transition" x-show="!search || '{{ strtolower($item['tld']) }}'.includes(search.toLowerCase())">
                            <td class="px-6 py-4 font-mono font-bold text-base text-slate-900">
                                {{ $item['tld'] }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-bold text-slate-900">${{ number_format($item['register_usd'], 2) }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">৳{{ number_format($item['register_bdt'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-bold text-slate-900">${{ number_format($item['renew_usd'], 2) }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">৳{{ number_format($item['renew_bdt'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-bold text-slate-900">${{ number_format($item['transfer_usd'], 2) }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">৳{{ number_format($item['transfer_bdt'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item['id_protection_cost'] == 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">FREE</span>
                                @else
                                    <span class="text-xs font-mono font-medium text-slate-700">${{ number_format($item['id_protection_cost'], 2) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item['dns_management'])
                                    <span class="text-emerald-600 font-bold text-xs inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Included
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($item['epp_required'])
                                    <span class="text-amber-600 text-xs font-semibold">Required</span>
                                @else
                                    <span class="text-slate-400 text-xs">Optional</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
