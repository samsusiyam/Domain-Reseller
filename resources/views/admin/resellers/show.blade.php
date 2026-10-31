@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.resellers.index') }}" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $reseller->company_name ?: $reseller->name }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $reseller->pricing_tier === 'platinum' ? 'bg-purple-100 text-purple-800' :
                           ($reseller->pricing_tier === 'gold' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') }}">
                        {{ $reseller->pricing_tier ?? 'standard' }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $reseller->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ ucfirst($reseller->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Reseller #{{ $reseller->id }} &bull; Contact: {{ $reseller->name }} ({{ $reseller->email }}) &bull; Since {{ $reseller->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.resellers.api-keys', $reseller->id) }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                Manage API Keys ({{ $reseller->api_keys_count }})
            </a>

            <a href="{{ route('admin.resellers.edit', $reseller->id) }}" class="px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white hover:bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold shadow-sm transition">
                Edit Reseller
            </a>

            <!-- Toggle Status -->
            <form action="{{ route('admin.resellers.toggle-status', $reseller->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition
                    {{ $reseller->status === 'active' ? 'border border-rose-200 text-rose-600 hover:bg-rose-50' : 'border border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                    {{ $reseller->status === 'active' ? 'Suspend Reseller' : 'Activate Reseller' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Reseller Tier & Wholesale Balance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tier Switcher & Info -->
        <div class="lg:col-span-2 bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Pricing Tier Configuration</h3>
            <form action="{{ route('admin.resellers.update-tier', $reseller->id) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Assigned Wholesale Tier</label>
                    <select name="pricing_tier" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        <option value="standard" {{ $reseller->pricing_tier === 'standard' ? 'selected' : '' }}>Standard (Level 1)</option>
                        <option value="silver" {{ $reseller->pricing_tier === 'silver' ? 'selected' : '' }}>Silver (Level 2)</option>
                        <option value="gold" {{ $reseller->pricing_tier === 'gold' ? 'selected' : '' }}>Gold (Level 3)</option>
                        <option value="platinum" {{ $reseller->pricing_tier === 'platinum' ? 'selected' : '' }}>Platinum (VIP Wholesale)</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto mt-0 sm:mt-5 px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition">
                    Update Tier
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Phone</span>
                    <span class="font-mono text-slate-800 dark:text-white text-sm">{{ $reseller->phone ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Country</span>
                    <span class="font-bold text-slate-800 dark:text-white text-sm uppercase">{{ $reseller->country ?: 'BD' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Total Domains</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $reseller->domains_count }}</span>
                </div>
            </div>
        </div>

        <!-- Wallet Balance Card & Adjustment -->
        <div class="lg:col-span-1 bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Wholesale Credit Balance</span>
                <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">
                    ${{ number_format($reseller->walletBalance, 2) }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pre-funded balance used for automated API provisioning.</p>
            </div>

            <!-- Balance Adjustment Form -->
            <form action="{{ route('admin.resellers.adjust-balance', $reseller->id) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <select name="type" required class="px-2 py-1.5 text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg">
                        <option value="credit">Add Credit (+)</option>
                        <option value="debit">Deduct Credit (-)</option>
                    </select>
                    <input type="number" step="0.01" name="amount" min="0.01" required placeholder="Amount"
                        class="px-2 py-1.5 text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg">
                </div>
                <input type="text" name="description" required placeholder="Reason for adjustment"
                    class="w-full px-2 py-1.5 text-xs border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition">
                    Apply Wholesale Credit
                </button>
            </form>
        </div>
    </div>

    <!-- Reseller Domains Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Provisioned Domains ({{ $domains->count() }})</h3>
        </div>
        @if($domains->isEmpty())
            <div class="text-center py-10 text-slate-400 text-sm">No domains provisioned by this reseller yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3">Domain</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3">Registered</th>
                            <th class="px-6 py-3">Expires</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($domains as $dom)
                            <tr>
                                <td class="px-6 py-3 font-mono font-bold text-slate-900 dark:text-white">{{ $dom->domain_name }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $dom->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700 dark:text-slate-300' }}">
                                        {{ ucfirst($dom->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400 text-xs">{{ $dom->registered_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-700 dark:text-slate-300 text-xs font-semibold">{{ $dom->expires_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('admin.domains.show', $dom->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Manage &rarr;</a>
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
