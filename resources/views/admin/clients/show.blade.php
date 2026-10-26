@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $client->name }}</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $client->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Client #{{ $client->id }} &bull; {{ $client->email }} &bull; Member since {{ $client->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Login As -->
            <form action="{{ route('admin.clients.impersonate', $client->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Login As Client
                </button>
            </form>

            <a href="{{ route('admin.clients.edit', $client->id) }}" class="px-3.5 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm transition">
                Edit Account
            </a>

            <!-- Toggle Status -->
            <form action="{{ route('admin.clients.toggle-status', $client->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition
                    {{ $client->status === 'active' ? 'border border-rose-200 text-rose-600 hover:bg-rose-50' : 'border border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                    {{ $client->status === 'active' ? 'Suspend Account' : 'Activate Account' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Client Info & Balance Adjustment -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Account Profile</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Company</span>
                    <span class="font-medium text-slate-800 text-sm">{{ $client->company_name ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Phone</span>
                    <span class="font-mono text-slate-800 text-sm">{{ $client->phone ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Country</span>
                    <span class="font-bold text-slate-800 text-sm uppercase">{{ $client->country ?: 'BD' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 block font-semibold uppercase">Address</span>
                    <span class="text-slate-800 text-sm">{{ $client->address_1 ?: '—' }} {{ $client->city }} {{ $client->state }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-semibold uppercase">Email Verified</span>
                    <span class="text-sm font-semibold {{ $client->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $client->email_verified_at ? 'Verified' : 'Unverified' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Wallet Balance Card & Adjustment -->
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Account Balance</span>
                <div class="text-3xl font-black text-slate-900 mt-2">
                    ${{ number_format($client->walletBalance, 2) }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Available credit for registrations & renewals.</p>
            </div>

            <!-- Balance Adjustment Form -->
            <form action="{{ route('admin.clients.adjust-balance', $client->id) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <select name="type" required class="px-2 py-1.5 text-xs border border-slate-300 rounded-lg">
                        <option value="credit">Add Funds (+)</option>
                        <option value="debit">Deduct Funds (-)</option>
                    </select>
                    <input type="number" step="0.01" name="amount" min="0.01" required placeholder="Amount"
                        class="px-2 py-1.5 text-xs border border-slate-300 rounded-lg">
                </div>
                <input type="text" name="description" required placeholder="Reason for adjustment"
                    class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded-lg">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition">
                    Apply Balance Adjustment
                </button>
            </form>
        </div>
    </div>

    <!-- Client Domains Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">Domains ({{ $domains->count() }})</h3>
        </div>
        @if($domains->isEmpty())
            <div class="text-center py-10 text-slate-400 text-sm">No domains owned by this client.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Domain</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3">Registered</th>
                            <th class="px-6 py-3">Expires</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($domains as $dom)
                            <tr>
                                <td class="px-6 py-3 font-mono font-bold text-slate-900">{{ $dom->domain_name }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $dom->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($dom->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-slate-500 text-xs">{{ $dom->registered_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-700 text-xs font-semibold">{{ $dom->expires_at?->format('M d, Y') ?? '—' }}</td>
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
