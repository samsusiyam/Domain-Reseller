@extends('layouts.guest')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden pt-20 pb-28 px-4 sm:px-6 lg:px-8 border-b border-white/5">
    <!-- Ambient Glow Backgrounds -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[400px] bg-gradient-to-tr from-indigo-600/20 via-purple-600/15 to-transparent rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 left-1/4 w-[350px] h-[250px] bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto text-center relative z-10">
        <!-- Badge Pill -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-400/20 text-indigo-300 text-xs font-semibold mb-8 backdrop-blur shadow-inner">
            <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
            Automated Multi-Registrar Wholesale Domain Engine
            <span class="text-indigo-400">/</span>
            <span class="text-white font-mono">WHMCS v8.x Ready</span>
        </div>

        <!-- Headline -->
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6">
            Find, Register & Resell <br class="hidden sm:block">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-sky-300 to-violet-400">
                Domains at Direct Wholesale
            </span>
        </h1>

        <p class="text-base sm:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
            Direct upstream registry connections with <span class="text-white font-semibold">Namecheap, Spaceship, Dynadot, AtakDomain & NameSilo</span>. Instant WHMCS module integration with automated billing in <span class="text-emerald-400 font-semibold">USD & BDT</span>.
        </p>

        <!-- Interactive Domain Search Engine (Alpine.js powered) -->
        <div class="max-w-3xl mx-auto" x-data="{
            searchQuery: '',
            selectedTld: '',
            checking: false,
            result: null,
            error: null,
            selectExtension(ext) {
                if (this.searchQuery.includes('.')) {
                    this.searchQuery = this.searchQuery.split('.')[0] + ext;
                } else {
                    this.searchQuery = this.searchQuery + ext;
                }
                this.checkDomain();
            },
            checkDomain() {
                let q = this.searchQuery.trim();
                if (!q) return;
                if (!q.includes('.')) {
                    q = q + '.com';
                    this.searchQuery = q;
                }
                this.checking = true;
                this.result = null;
                this.error = null;
                fetch('{{ route('domain.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ domain: q })
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
                    this.error = 'Failed to connect to registrar search engine.';
                });
            }
        }">
            <!-- Main Search Form -->
            <form @submit.prevent="checkDomain()" class="relative flex flex-col sm:flex-row items-stretch p-2.5 bg-obsidian-900/90 rounded-2xl shadow-2xl shadow-indigo-950/60 border border-white/15 backdrop-blur-xl">
                <div class="relative flex-1 flex items-center">
                    <svg class="w-6 h-6 text-indigo-400 ml-4 absolute pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Search your ideal domain (e.g. mycompany.com)"
                        class="w-full pl-14 pr-4 py-4 text-base sm:text-lg text-white placeholder:text-slate-500 bg-transparent rounded-xl focus:outline-none border-0 font-medium" required>
                </div>

                <button type="submit" :disabled="checking"
                    class="mt-2 sm:mt-0 px-8 py-4 bg-gradient-to-r from-indigo-500 via-indigo-600 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-xl transition-all duration-150 flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-75">
                    <span x-show="!checking" class="flex items-center gap-2">
                        <span>Search</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                    <span x-show="checking" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Checking Registry...
                    </span>
                </button>
            </form>

            <!-- Quick TLD Extension Pills -->
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs">
                <span class="text-slate-400 font-medium mr-1">Popular Extensions:</span>
                @php
                    $quickTlds = [
                        ['tld' => '.com', 'price' => '$10.49'],
                        ['tld' => '.net', 'price' => '$12.49'],
                        ['tld' => '.org', 'price' => '$11.49'],
                        ['tld' => '.xyz', 'price' => '$2.49'],
                        ['tld' => '.io', 'price' => '$37.99'],
                        ['tld' => '.co', 'price' => '$12.99'],
                    ];
                @endphp
                @foreach($quickTlds as $qt)
                    <button type="button" @click="selectExtension('{{ $qt['tld'] }}')"
                        class="px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-indigo-500/50 text-slate-300 font-mono transition flex items-center gap-1.5 group">
                        <span class="font-bold text-white group-hover:text-indigo-300">{{ $qt['tld'] }}</span>
                        <span class="text-[10px] text-emerald-400 font-bold">{{ $qt['price'] }}</span>
                    </button>
                @endforeach
            </div>

            <!-- Dynamic Search Result Card -->
            <div x-show="result" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-6 p-6 rounded-2xl glass-card text-left transition-all duration-200 shadow-2xl border border-white/15">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="text-2xl font-extrabold text-white font-mono" x-text="result?.domain"></span>
                            <template x-if="result?.is_available">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 flex items-center gap-1.5 shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Available Now
                                </span>
                            </template>
                            <template x-if="!result?.is_available">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/40 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                    Registered / Taken
                                </span>
                            </template>
                        </div>

                        <template x-if="result?.is_available">
                            <p class="text-xs sm:text-sm text-slate-300 mt-2">
                                🎉 Outstanding choice! This domain is available for immediate wholesale registration.
                            </p>
                        </template>
                        <template x-if="!result?.is_available">
                            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                                This domain is already registered. You can inspect its ownership records via WHOIS or initiate a transfer.
                            </p>
                        </template>
                    </div>

                    <div class="flex items-center gap-5 shrink-0 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 border-white/10 pt-4 sm:pt-0">
                        <template x-if="result?.is_available">
                            <div class="text-right">
                                <span class="text-2xl font-black text-indigo-400 font-mono" x-text="'$' + Number(result?.price_usd).toFixed(2)"></span>
                                <span class="block text-xs text-slate-400 font-mono" x-text="'~ ৳' + Number(result?.price_bdt).toFixed(2) + ' BDT'"></span>
                            </div>
                        </template>

                        <template x-if="result?.is_available">
                            <a :href="'{{ route('login') }}?redirect=' + encodeURIComponent('{{ route('client.domains.register') }}?domain=' + result?.domain)"
                                class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-emerald-600/30 hover:scale-[1.02]">
                                Register Now →
                            </a>
                        </template>
                        <template x-if="!result?.is_available">
                            <a :href="'{{ route('whois.index') }}?domain=' + encodeURIComponent(result?.domain)"
                                class="px-5 py-3 bg-white/10 hover:bg-white/15 text-white font-semibold text-sm rounded-xl transition border border-white/10">
                                View WHOIS Record
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Error Banner -->
            <div x-show="error" x-cloak class="mt-4 p-4 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-sm text-left backdrop-blur">
                <span x-text="error"></span>
            </div>
        </div>
    </div>
</section>

<!-- Live TLD Ticker Bar -->
<section class="py-6 border-b border-white/10 bg-obsidian-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                Featured Wholesale TLDs:
            </span>
            <div class="flex flex-wrap items-center gap-3">
                @foreach($featuredTlds as $tld)
                    <a href="{{ route('domain.search') }}?tld={{ $tld->tld }}"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/5 border border-white/10 hover:border-indigo-500/50 hover:bg-white/10 transition group">
                        <span class="font-mono font-bold text-white group-hover:text-indigo-300 text-sm">{{ $tld->tld }}</span>
                        <span class="text-emerald-400 font-mono font-bold text-xs">${{ number_format((float)$tld->client_register, 2) }}</span>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('pricing') }}" class="text-xs text-indigo-400 hover:text-white font-bold transition">All Extensions ({{ count($featuredTlds) }}+) →</a>
        </div>
    </div>
</section>

<!-- Features Grid (Spaceship Standard) -->
<section class="py-24 px-4 sm:px-6 lg:px-8 relative">
    <div class="max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-3">Enterprise Wholesale Infrastructure</h2>
            <h3 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">Built for Web Hosts, Agencies & Resellers</h3>
            <p class="text-slate-400 mt-4 text-base">
                Everything you need to automate your domain business with zero overhead, multi-registrar redundancy, and automated WHMCS integration.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1: Multi-Registrar Failover -->
            <div class="p-8 rounded-3xl glass-card hover:border-indigo-500/40 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">5 Upstream Registrars</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Integrated directly with Namecheap, Spaceship, Dynadot, AtakDomain, and NameSilo with automated failover and live account balance monitoring.
                </p>
            </div>

            <!-- Feature 2: WHMCS 1-Click Module -->
            <div class="p-8 rounded-3xl glass-card hover:border-purple-500/40 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">Plug-and-Play WHMCS Module</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Connect your WHMCS in under 2 minutes. Full registrar functionality: Register, Renew, Transfer, Nameservers, DNS management, EPP code, and Lock.
                </p>
            </div>

            <!-- Feature 3: Dual Currency USD & BDT -->
            <div class="p-8 rounded-3xl glass-card hover:border-emerald-500/40 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">Dual Currency USD / BDT</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Rock-solid USD base ledger for accounting with live or fallback exchange rates into Bangladeshi Taka (BDT) with bKash and local payments.
                </p>
            </div>

            <!-- Feature 4: 44 REST API Endpoints -->
            <div class="p-8 rounded-3xl glass-card hover:border-sky-500/40 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-sky-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">44 REST API Endpoints</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Build custom billing panels, automated provisioning pipelines, or integrations with HMAC SHA-256 signatures, IP whitelisting, and rate limits.
                </p>
            </div>

            <!-- Feature 5: Multi-Payment Gateways -->
            <div class="p-8 rounded-3xl glass-card hover:border-amber-500/40 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">Multi-Gateway Checkout</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Automated top-ups via bKash Checkout, Stripe (Visa / Mastercard), Binance Pay API for crypto, and manual bank transfer verification.
                </p>
            </div>

            <!-- Feature 6: Complete DNS & Child NS -->
            <div class="p-8 rounded-3xl glass-card hover:border-rose-500/40 transition-all duration-200 group">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-rose-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h4 class="text-xl font-bold text-white mb-2">Advanced DNS & White-Label</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Custom child nameservers (glue records), free WHOIS privacy protection, DNSSEC, URL forwarding, and email forwarding controls.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Reseller Program Callout Section -->
<section class="py-20 px-4 sm:px-6 lg:px-8 border-y border-white/10 bg-gradient-to-b from-obsidian-900 via-indigo-950/20 to-obsidian-900">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Reseller Program</span>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight leading-tight">
                    Start Reselling Domains with Your Own Brand & Billing
                </h3>
                <p class="text-slate-300 mt-4 leading-relaxed text-base">
                    Whether you use WHMCS, Blesta, or a custom hosting platform, our turnkey reseller program gives you instant access to wholesale tier pricing, automated order execution, and sub-client isolation.
                </p>

                <div class="mt-8 space-y-3">
                    <div class="flex items-center gap-3 text-sm text-slate-200">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Zero setup fees — deposit credit and start registering immediately</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-slate-200">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Pre-built WHMCS Registrar Module ready for drag-and-drop install</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-slate-200">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Automated webhook callbacks for domain transfers, expiry & sync</span>
                    </div>
                </div>

                <div class="mt-10 flex items-center gap-4 flex-wrap">
                    <a href="{{ route('register') }}"
                        class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-600/30">
                        Join Reseller Program
                    </a>
                    <a href="{{ route('reseller.program') }}"
                        class="px-6 py-3.5 bg-white/5 hover:bg-white/10 text-white font-semibold rounded-xl transition border border-white/10">
                        Explore WHMCS Module →
                    </a>
                </div>
            </div>

            <!-- Code Preview Box -->
            <div class="p-6 rounded-3xl bg-slate-950 border border-white/10 shadow-2xl font-mono text-xs text-slate-300 overflow-hidden">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/10 text-slate-400">
                    <span class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="ml-2 font-bold text-white">WHMCS API Request</span>
                    </span>
                    <span class="text-indigo-400">POST /api/v1/domains/register</span>
                </div>
                <pre class="overflow-x-auto text-indigo-300 leading-relaxed font-mono">
<span class="text-slate-500">// Header Authentication</span>
X-Api-Key: <span class="text-amber-300">"dr_live_demo1234567890abcdef"</span>
X-Signature: <span class="text-amber-300">"hmac_sha256_signature_hash"</span>

<span class="text-slate-500">// JSON Request Body</span>
{
  <span class="text-white">"domain"</span>: <span class="text-emerald-300">"examplecompany.com"</span>,
  <span class="text-white">"years"</span>: <span class="text-amber-300">1</span>,
  <span class="text-white">"nameservers"</span>: [<span class="text-emerald-300">"ns1.yourhost.com"</span>, <span class="text-emerald-300">"ns2.yourhost.com"</span>],
  <span class="text-white">"id_protection"</span>: <span class="text-purple-400">true</span>
}

<span class="text-slate-500">// Real-Time Registry Response (200 OK)</span>
{
  <span class="text-white">"success"</span>: <span class="text-purple-400">true</span>,
  <span class="text-white">"message"</span>: <span class="text-emerald-300">"Domain registered successfully."</span>,
  <span class="text-white">"data"</span>: {
    <span class="text-white">"domain"</span>: <span class="text-emerald-300">"examplecompany.com"</span>,
    <span class="text-white">"status"</span>: <span class="text-emerald-300">"active"</span>,
    <span class="text-white">"cost_usd"</span>: <span class="text-amber-300">10.49</span>,
    <span class="text-white">"cost_bdt"</span>: <span class="text-amber-300">1258.80</span>,
    <span class="text-white">"expiry_date"</span>: <span class="text-emerald-300">"{{ date('Y-m-d', strtotime('+1 year')) }}"</span>
  }
}</pre>
            </div>
        </div>
    </div>
</section>

<!-- Bottom CTA Banner -->
<section class="py-24 px-4 sm:px-6 lg:px-8 text-center relative overflow-hidden">
    <div class="max-w-4xl mx-auto glass-card p-12 sm:p-16 rounded-3xl border border-indigo-500/30 relative z-10 shadow-2xl">
        <h3 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
            Ready to Supercharge Your Domain Business?
        </h3>
        <p class="text-slate-300 mt-4 text-base sm:text-lg max-w-xl mx-auto leading-relaxed">
            Create your account today, download the official WHMCS module, and start registering domains at true wholesale pricing in minutes.
        </p>

        <div class="mt-8 flex items-center justify-center gap-4 flex-wrap">
            <a href="{{ route('register') }}"
                class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-xl transition shadow-xl shadow-indigo-600/30 text-base">
                Create Free Account →
            </a>
            <a href="{{ route('pricing') }}"
                class="px-6 py-4 bg-white/10 hover:bg-white/15 text-white font-bold rounded-xl transition border border-white/15 text-base">
                View Full Pricing Matrix
            </a>
        </div>
    </div>
</section>
@endsection
