@extends('layouts.guest')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 text-white pt-20 pb-28 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto text-center relative z-10">
        <!-- Badge Pill -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-400/20 text-indigo-300 text-xs font-semibold mb-8 backdrop-blur">
            <span class="flex h-2 w-2 rounded-full bg-indigo-400 animate-ping"></span>
            Automated Multi-Registrar Wholesale Domain Infrastructure
        </div>

        <!-- Headline -->
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight leading-[1.1] mb-6">
            Find, Register & Resell <br class="hidden sm:block">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-sky-300 to-violet-400">
                Domains at Direct Wholesale
            </span>
        </h1>

        <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
            Direct registry integration with Namecheap, Spaceship, Dynadot, AtakDomain & NameSilo. Instant WHMCS module integration with automated billing in USD and BDT.
        </p>

        <!-- Instant Domain Search Box (Alpine.js powered) -->
        <div class="max-w-3xl mx-auto" x-data="{
            searchQuery: '',
            checking: false,
            result: null,
            error: null,
            checkDomain() {
                if (!this.searchQuery.trim()) return;
                this.checking = true;
                this.result = null;
                this.error = null;
                fetch('{{ route('domain.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ domain: this.searchQuery })
                })
                .then(res => res.json())
                .then(data => {
                    this.checking = false;
                    if (data.success) {
                        this.result = data.data;
                    } else {
                        this.error = data.message || 'Domain check failed.';
                    }
                })
                .catch(err => {
                    this.checking = false;
                    this.error = 'Failed to connect to search engine.';
                });
            }
        }">
            <form @submit.prevent="checkDomain()" class="relative flex flex-col sm:flex-row items-stretch p-2 bg-white rounded-2xl shadow-2xl shadow-indigo-950/50 border border-slate-200/20">
                <div class="relative flex-1 flex items-center">
                    <svg class="w-6 h-6 text-slate-400 ml-4 absolute pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Search your ideal domain (e.g. yourcompany.com)"
                        class="w-full pl-13 pr-4 py-4 text-base sm:text-lg text-slate-900 placeholder:text-slate-400 bg-transparent rounded-xl focus:outline-none focus:ring-0 border-0" required>
                </div>
                <button type="submit" :disabled="checking"
                    class="mt-2 sm:mt-0 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-md hover:shadow-indigo-500/25 disabled:opacity-75">
                    <span x-show="!checking">Search Domain</span>
                    <span x-show="checking" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Checking...
                    </span>
                </button>
            </form>

            <!-- Search Result Banner -->
            <div x-show="result" x-cloak class="mt-4 p-5 rounded-2xl bg-slate-900/90 border border-slate-700 text-left transition backdrop-blur">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl font-extrabold text-white" x-text="result?.domain"></span>
                            <template x-if="result?.is_available">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Available</span>
                            </template>
                            <template x-if="!result?.is_available">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-rose-500/20 text-rose-400 border border-rose-500/30">Taken</span>
                            </template>
                        </div>
                        <template x-if="result?.is_available">
                            <p class="text-sm text-slate-300 mt-1">
                                Great choice! This domain is ready for immediate registration.
                            </p>
                        </template>
                        <template x-if="!result?.is_available">
                            <p class="text-sm text-slate-400 mt-1">
                                This domain is already registered. You can initiate a transfer or check WHOIS details.
                            </p>
                        </template>
                    </div>

                    <div class="flex items-center gap-4">
                        <template x-if="result?.is_available">
                            <div class="text-right">
                                <span class="text-2xl font-black text-indigo-400" x-text="'$' + Number(result?.price_usd).toFixed(2)"></span>
                                <span class="block text-xs text-slate-400" x-text="'~ ৳' + Number(result?.price_bdt).toFixed(2) + ' BDT'"></span>
                            </div>
                        </template>

                        <template x-if="result?.is_available">
                            <a :href="'{{ route('login') }}?redirect=' + encodeURIComponent('{{ route('client.domains.register') }}?domain=' + result?.domain)"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl transition shadow">
                                Register Now
                            </a>
                        </template>
                        <template x-if="!result?.is_available">
                            <a :href="'{{ route('whois.index') }}?domain=' + encodeURIComponent(result?.domain)"
                                class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm rounded-xl transition border border-slate-700">
                                View WHOIS
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Error Banner -->
            <div x-show="error" x-cloak class="mt-4 p-4 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-sm text-left">
                <span x-text="error"></span>
            </div>

            <!-- Featured TLDs Badges -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                @foreach($featuredTlds as $tld)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs backdrop-blur hover:bg-white/10 transition">
                        <span class="font-mono font-bold text-indigo-300">{{ $tld->tld }}</span>
                        <span class="text-slate-300 font-bold">${{ number_format((float)$tld->client_register, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-widest mb-2">Designed For Scale</h2>
            <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Everything You Need To Resell Domains Effortlessly</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Multi-Registrar Engine</h4>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Integrated with Namecheap, Spaceship, Dynadot, AtakDomain, and NameSilo. Route individual TLDs automatically to the registrar offering the lowest wholesale cost.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">WHMCS & REST API</h4>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Turnkey official registrar module for WHMCS and Blesta. 44 fully documented REST endpoints allowing your resellers to sell domains directly from their billing portal.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Dual Currency: USD & BDT</h4>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Base wholesale ledger in USD with automated real-time BDT conversion. Built-in support for bKash Checkout, Stripe Cards, Binance Pay, and manual offline bank transfers.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-16 bg-slate-900 text-white border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold tracking-tight mb-4">Ready to start your domain reseller business?</h2>
        <p class="text-slate-400 max-w-xl mx-auto mb-8 text-sm">
            Join hundreds of hosting companies and web development agencies using our automated wholesale domain infrastructure.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 font-bold rounded-xl transition text-sm shadow-lg shadow-indigo-600/30">
                Create Reseller Account
            </a>
            <a href="{{ route('pricing') }}" class="w-full sm:w-auto px-8 py-3.5 bg-slate-800 hover:bg-slate-700 font-bold rounded-xl transition text-sm border border-slate-700 text-slate-200">
                View Wholesale Pricing
            </a>
        </div>
    </div>
</section>
@endsection
