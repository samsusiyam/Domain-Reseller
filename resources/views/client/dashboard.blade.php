@extends('layouts.app', ['title' => 'Client Dashboard', 'header' => 'Dashboard Overview'])

@section('content')
<div class="space-y-8">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Active Domains -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Domains</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $activeDomainsCount ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </div>
        </div>

        <!-- Card 2: Expiring Soon -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Expiring in 30 Days</p>
                <p class="text-2xl font-black {{ ($expiringCount ?? 0) > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">{{ $expiringCount ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 3: Unpaid Invoices -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unpaid Invoices</p>
                <p class="text-2xl font-black {{ ($unpaidInvoicesCount ?? 0) > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">{{ $unpaidInvoicesCount ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <!-- Card 4: Wallet Balance -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Available Credit</p>
                <p class="text-2xl font-black text-indigo-600 mt-1">${{ number_format((float)auth()->user()->balance, 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Quick Domain Search Section -->
    <div class="p-6 sm:p-8 rounded-2xl bg-gradient-to-r from-indigo-900 via-indigo-950 to-slate-950 text-white shadow-xl shadow-indigo-950/20">
        <h2 class="text-xl font-extrabold tracking-tight mb-2">Register a New Domain</h2>
        <p class="text-xs text-indigo-200 mb-6">Instantly check availability and secure your domain name across 100+ extensions.</p>

        <form action="{{ route('client.domains.search') }}" method="GET" class="flex flex-col sm:flex-row gap-2 max-w-2xl">
            <input type="text" name="domain" placeholder="Search a new domain (e.g. mybrand.com)" required
                class="flex-1 px-4 py-3 bg-white text-slate-900 placeholder:text-slate-400 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit" class="px-6 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-sm rounded-xl transition shadow">
                Check Availability
            </button>
        </form>
    </div>

    <!-- Two-column recent activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Expiring Domains Alert List -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-base">Domains Needing Attention</h3>
                <a href="{{ route('client.domains.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All &rarr;</a>
            </div>

            @php
                $expiringDomains = auth()->user()->domains()->where('status', 'active')->where('expires_at', '<=', now()->addDays(30))->orderBy('expires_at')->take(5)->get();
            @endphp

            @if($expiringDomains->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($expiringDomains as $domain)
                        <div class="py-3 flex items-center justify-between text-sm">
                            <div>
                                <span class="font-bold text-slate-900">{{ $domain->domain_name }}</span>
                                <span class="block text-xs text-rose-600 font-semibold">Expires: {{ $domain->expires_at->format('M d, Y') }} ({{ $domain->expires_at->diffForHumans() }})</span>
                            </div>
                            <form action="{{ route('client.domains.renew', $domain->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="years" value="1">
                                <button type="submit" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold text-xs rounded-lg transition">
                                    Renew 1 Yr
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-xs text-slate-400">
                    All your domains are healthy with no upcoming expirations.
                </div>
            @endif
        </div>

        <!-- Recent Invoices List -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 text-base">Recent Invoices</h3>
                <a href="{{ route('client.invoices.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All &rarr;</a>
            </div>

            @php
                $recentInvoices = auth()->user()->invoices()->latest()->take(5)->get();
            @endphp

            @if($recentInvoices->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($recentInvoices as $inv)
                        <div class="py-3 flex items-center justify-between text-sm">
                            <div>
                                <a href="{{ route('client.invoices.show', $inv->id) }}" class="font-bold text-slate-900 hover:text-indigo-600">
                                    {{ $inv->invoice_number }}
                                </a>
                                <span class="block text-xs text-slate-400">{{ $inv->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-900">${{ number_format($inv->total_amount, 2) }}</span>
                                <x-badge :color="$inv->status">{{ $inv->status }}</x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-xs text-slate-400">
                    No billing invoices generated yet.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
