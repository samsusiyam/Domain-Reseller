@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Top Retail Clients by Spend</h1>
        <p class="text-xs text-slate-500 mt-1">Audit high-value direct retail customers by lifetime settled payment volume.</p>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold overflow-x-auto">
        <a href="{{ route('admin.reports.revenue') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Revenue</a>
        <a href="{{ route('admin.reports.domains') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Domains</a>
        <a href="{{ route('admin.reports.clients') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600 whitespace-nowrap">Clients</a>
        <a href="{{ route('admin.reports.resellers') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Resellers</a>
        <a href="{{ route('admin.reports.tlds') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">TLD Margins</a>
        <a href="{{ route('admin.reports.payments') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800 whitespace-nowrap">Gateways</a>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Client Details</th>
                        <th class="px-6 py-3.5">Company / Org</th>
                        <th class="px-6 py-3.5">Registration Date</th>
                        <th class="px-6 py-3.5 text-right">Lifetime Settled (USD)</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topSpenders as $client)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 block">
                                    {{ $client->name }}
                                </a>
                                <span class="text-xs text-slate-400 font-mono">{{ $client->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $client->company_name ?: '—' }}</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">{{ $client->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 text-base">
                                ${{ number_format((float)$client->total_spent, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                                    Profile &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-400">No client records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
