@extends('layouts.app', ['title' => 'Client Dashboard', 'header' => 'Overview'])

@section('content')
<div class="space-y-6">
    <!-- Stat Cards Grid (Clean, Minimal, Sentence Case, Single Accent) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Active Domains -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Active domains</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $activeDomainsCount ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
            </div>
        </div>

        <!-- Card 2: Expiring Soon -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Expiring in 30 days</p>
                <p class="text-2xl font-bold {{ ($expiringCount ?? 0) > 0 ? 'text-[#F6821F]' : 'text-slate-900 dark:text-white' }} mt-1 tabular-nums">{{ $expiringCount ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Card 3: Unpaid Invoices -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Unpaid invoices</p>
                <p class="text-2xl font-bold {{ ($unpaidInvoicesCount ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} mt-1 tabular-nums">{{ $unpaidInvoicesCount ?? 0 }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
        </div>

        <!-- Card 4: Wallet Balance -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500">Available credit</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">
                    ${{ number_format((float)auth()->user()->balance, 2) }}
                </p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
            </div>
        </div>
    </div>

    <!-- Quick Domain Search (Minimal Surface Card, No AI Purple Gradient) -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-7 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="max-w-2xl">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Register a new domain</h2>
            <p class="text-xs text-slate-500 mt-1 mb-4">Instantly query real-time registry availability across 100+ wholesale extensions.</p>

            <form action="{{ route('client.domains.search') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <input type="text" name="domain" placeholder="Enter domain name (e.g. brand.com)" required
                        class="w-full h-10 px-3.5 text-sm rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F]">
                </div>
                <button type="submit" 
                        class="h-10 px-5 bg-[#F6821F] hover:bg-[#E07116] active:bg-[#C9620E] text-white font-medium text-xs rounded-lg transition-all shadow-sm flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <span>Check availability</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Two-column Activity & Quick Links -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Expiring Domains or Active Portfolio (8 cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Domain portfolio</h3>
                <a href="{{ route('client.domains.index') }}" class="text-xs font-semibold text-[#F6821F] hover:underline">View all &rarr;</a>
            </div>

            @php
                $recentDomains = \App\Models\Domain::where('user_id', auth()->id())->latest()->take(5)->get();
            @endphp

            @if($recentDomains->isEmpty())
                <div class="py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                    </div>
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200">No domains registered yet</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Register your first domain name or transfer an existing one to manage DNS, WHOIS privacy, and nameservers in one place.</p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <a href="{{ route('client.domains.search') }}" class="h-8 px-3.5 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg flex items-center gap-1.5 transition">
                            Register domain
                        </a>
                        <a href="{{ route('client.domains.transfer') }}" class="h-8 px-3.5 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition">
                            Transfer domain
                        </a>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recentDomains as $d)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <a href="{{ route('client.domains.show', $d->id) }}" class="text-xs font-semibold text-slate-900 dark:text-white hover:text-[#F6821F] font-mono">
                                    {{ $d->domain_name }}
                                </a>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Expires: {{ $d->expires_at ? $d->expires_at->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium {{ $d->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($d->status) }}
                                </span>
                                <a href="{{ route('client.domains.show', $d->id) }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    Manage &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Fast Actions & Shortcuts (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Quick operations</h3>
                
                <div class="space-y-1.5">
                    <a href="{{ route('client.domains.search') }}" class="flex items-center gap-2.5 p-2 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="w-6 h-6 rounded bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </span>
                        <span>Register a domain</span>
                    </a>

                    <a href="{{ route('client.domains.transfer') }}" class="flex items-center gap-2.5 p-2 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="w-6 h-6 rounded bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                        </span>
                        <span>Transfer an external domain</span>
                    </a>

                    <a href="{{ route('client.wallet.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="w-6 h-6 rounded bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span>Add balance to wallet</span>
                    </a>

                    <a href="{{ route('client.tickets.create') }}" class="flex items-center gap-2.5 p-2 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="w-6 h-6 rounded bg-[#F6821F]/10 text-[#F6821F] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                        </span>
                        <span>Open support ticket</span>
                    </a>
                </div>
            </div>

            <!-- HostNibo Security Status Note -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-xs text-slate-500 space-y-1.5">
                <div class="flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                    <span>Account Security</span>
                </div>
                <p class="text-[11px] leading-relaxed">Two-factor TOTP protection is {{ auth()->user()->two_factor_confirmed_at ? 'enabled' : 'recommended' }}. Keep your DNS registry auth codes secure.</p>
            </div>
        </div>

    </div>
</div>
@endsection
