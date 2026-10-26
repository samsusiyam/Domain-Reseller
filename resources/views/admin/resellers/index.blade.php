@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Wholesale Resellers</h1>
            <p class="text-xs text-slate-500 mt-1">Manage wholesale domain partners, pricing tiers, API credentials, and bulk deposits.</p>
        </div>
        <div>
            <a href="{{ route('admin.resellers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition text-xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Reseller
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.resellers.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by reseller name, email, company..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-44">
                <select name="tier" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Tiers</option>
                    <option value="standard" {{ request('tier') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="silver" {{ request('tier') === 'silver' ? 'selected' : '' }}>Silver</option>
                    <option value="gold" {{ request('tier') === 'gold' ? 'selected' : '' }}>Gold</option>
                    <option value="platinum" {{ request('tier') === 'platinum' ? 'selected' : '' }}>Platinum</option>
                </select>
            </div>

            <div class="w-full sm:w-40">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('tier') || request('status'))
                <a href="{{ route('admin.resellers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Resellers Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($resellers->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No reseller partners found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Reseller & Company</th>
                            <th class="px-6 py-3.5 text-center">Tier</th>
                            <th class="px-6 py-3.5 text-center">Domains</th>
                            <th class="px-6 py-3.5 text-center">API Keys</th>
                            <th class="px-6 py-3.5 text-right">Wallet Balance</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($resellers as $r)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.resellers.show', $r->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 block">
                                        {{ $r->company_name ?: $r->name }}
                                    </a>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $r->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                        {{ $r->pricing_tier === 'platinum' ? 'bg-purple-100 text-purple-800' :
                                           ($r->pricing_tier === 'gold' ? 'bg-amber-100 text-amber-800' :
                                           ($r->pricing_tier === 'silver' ? 'bg-slate-200 text-slate-800' : 'bg-indigo-100 text-indigo-800')) }}">
                                        {{ $r->pricing_tier ?? 'standard' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-800">
                                    {{ $r->domains_count }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.resellers.api-keys', $r->id) }}" class="font-mono text-xs font-bold text-indigo-600 hover:underline">
                                        {{ $r->api_keys_count }} Keys
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    ${{ number_format($r->walletBalance, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $r->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ ucfirst($r->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.resellers.show', $r->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Manage</a>
                                    <a href="{{ route('admin.resellers.edit', $r->id) }}" class="text-slate-500 hover:text-slate-800 text-xs font-semibold">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($resellers->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $resellers->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
