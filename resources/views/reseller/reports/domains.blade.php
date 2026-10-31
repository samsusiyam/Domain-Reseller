@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Domain Portfolio Analytics</h1>
            <p class="text-sm text-slate-500 mt-1">Breakdown of registered TLD extensions and upcoming domain expiration timelines.</p>
        </div>
    </div>

    <!-- Expiration Windows Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Expiring in &le; 30 Days</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($expiring30) }}</p>
                <p class="text-xs text-slate-400 mt-1">Urgent renewals required</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                30d
            </div>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Expiring in 31 - 60 Days</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($expiring60) }}</p>
                <p class="text-xs text-slate-400 mt-1">Upcoming renewal cycle</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                60d
            </div>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#F6821F]">Expiring in 61 - 90 Days</p>
                <p class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($expiring90) }}</p>
                <p class="text-xs text-slate-400 mt-1">Quarterly forecast</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-950/30 text-[#F6821F] flex items-center justify-center font-bold">
                90d
            </div>
        </div>
    </div>

    <!-- TLD Distribution Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Portfolio Breakdown by TLD</h3>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                {{ $tldBreakdown->count() }} Unique Extensions
            </span>
        </div>

        @if($tldBreakdown->isEmpty())
            <div class="text-center py-12 px-4">
                <p class="text-sm text-slate-500">No active domains registered in this account yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">TLD Extension</th>
                            <th class="px-6 py-3.5 text-right">Domain Count</th>
                            <th class="px-6 py-3.5 text-right">Share of Portfolio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $totalDomains = $tldBreakdown->sum('count'); @endphp
                        @foreach($tldBreakdown as $row)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">{{ $row->tld }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-slate-200">{{ number_format($row->count) }}</td>
                                <td class="px-6 py-4 text-right text-slate-500 font-mono text-xs">
                                    {{ $totalDomains > 0 ? number_format(($row->count / $totalDomains) * 100, 1) : 0 }}%
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
