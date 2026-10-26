@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Wholesale Reseller Performance</h1>
        <p class="text-xs text-slate-500 mt-1">Review reseller domain volumes, API order throughput, and lifetime wholesale revenue.</p>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold overflow-x-auto">
        <a href="{{ route('admin.reports.revenue') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Revenue</a>
        <a href="{{ route('admin.reports.domains') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Domains</a>
        <a href="{{ route('admin.reports.clients') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Clients</a>
        <a href="{{ route('admin.reports.resellers') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600 whitespace-nowrap">Resellers</a>
        <a href="{{ route('admin.reports.tlds') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">TLD Margins</a>
        <a href="{{ route('admin.reports.payments') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Gateways</a>
    </div>

    <!-- Resellers Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Reseller / Company</th>
                        <th class="px-6 py-3.5 text-center">Tier</th>
                        <th class="px-6 py-3.5 text-center">Domains</th>
                        <th class="px-6 py-3.5 text-center">Orders</th>
                        <th class="px-6 py-3.5 text-right">Wholesale Volume</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($resellerStats as $reseller)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 block">
                                    {{ $reseller->company_name ?: $reseller->name }}
                                </a>
                                <span class="text-xs text-slate-400 font-mono">{{ $reseller->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $reseller->pricing_tier === 'platinum' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $reseller->pricing_tier ?? 'standard' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-900">
                                {{ number_format($reseller->domains_count) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700">
                                {{ number_format($reseller->orders_count) }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-indigo-700 text-base">
                                ${{ number_format((float)$reseller->wholesale_volume, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">
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
