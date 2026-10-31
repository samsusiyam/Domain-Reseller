@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Wholesale Reseller Performance</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Review reseller domain volumes, API order throughput, and lifetime wholesale revenue.</p>
    </div>

    <x-admin-reports-tabs active="resellers" />

    <!-- Resellers Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Reseller / Company</th>
                        <th class="px-6 py-3.5 text-center">Tier</th>
                        <th class="px-6 py-3.5 text-center">Domains</th>
                        <th class="px-6 py-3.5 text-center">Orders</th>
                        <th class="px-6 py-3.5 text-right">Wholesale Volume</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($resellerStats as $reseller)
                        <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-[#F6821F] block">
                                    {{ $reseller->company_name ?: $reseller->name }}
                                </a>
                                <span class="text-xs text-slate-400 font-mono">{{ $reseller->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $reseller->pricing_tier === 'platinum' ? 'bg-purple-100 text-purple-800' : 'bg-[#F6821F]/10 text-[#F6821F]' }}">
                                    {{ $reseller->pricing_tier ?? 'standard' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900 dark:text-white">
                                {{ number_format($reseller->domains_count) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                {{ number_format($reseller->orders_count) }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-[#F6821F] text-base">
                                ${{ number_format((float)$reseller->wholesale_volume, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="text-xs font-bold text-[#F6821F] hover:underline">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-slate-400">No reseller activity recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
