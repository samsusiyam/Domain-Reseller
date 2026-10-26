@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" x-data="{
    searchTab: 'single',
    query: '{{ request('query', request('domain', '')) }}',
    bulkDomains: '',
    checking: false,
    singleResult: null,
    bulkResults: [],
    error: null,
    selectedCategory: 'all',
    
    checkSingle() {
        let q = this.query.trim();
        if (!q) return;
        if (!q.includes('.')) {
            q = q + '{{ request('tld', '.com') }}';
            this.query = q;
        }
        this.checking = true;
        this.singleResult = null;
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
                this.singleResult = data.data;
            } else {
                this.error = data.message || 'Availability check failed.';
            }
        })
        .catch(err => {
            this.checking = false;
            this.error = 'Failed to connect to registrar search engine.';
        });
    },

    checkBulk() {
        let lines = this.bulkDomains.split('\n').map(d => d.trim()).filter(d => d.length > 0);
        if (lines.length === 0) return;
        if (lines.length > 20) {
            this.error = 'Please enter at most 20 domains at a time.';
            return;
        }
        this.checking = true;
        this.bulkResults = [];
        this.error = null;

        fetch('{{ route('domain.check.bulk') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ domains: lines })
        })
        .then(res => res.json())
        .then(data => {
            this.checking = false;
            if (data.success) {
                this.bulkResults = data.data;
            } else {
                this.error = data.message || 'Bulk lookup failed.';
            }
        })
        .catch(err => {
            this.checking = false;
            this.error = 'Bulk search failed.';
        });
    }
}" x-init="if (query) { checkSingle(); }">

    <!-- Header Banner -->
    <div class="text-center max-w-3xl mx-auto mb-12">
        <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
            Real-Time Registry Lookup
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">
            Domain Availability Search
        </h1>
        <p class="text-slate-400 mt-3 text-base">
            Check real-time availability across all 5 upstream registrars. Wholesale rates with instant checkout in USD and BDT.
        </p>
    </div>

    <!-- Search Tabs Container -->
    <div class="max-w-4xl mx-auto glass-card rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl">
        <!-- Tabs -->
        <div class="flex items-center gap-2 border-b border-white/10 pb-4 mb-6">
            <button @click="searchTab = 'single'" type="button"
                :class="searchTab === 'single' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Single Domain Search
            </button>
            <button @click="searchTab = 'bulk'" type="button"
                :class="searchTab === 'bulk' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Bulk Search (Up to 20)
            </button>
        </div>

        <!-- Single Search Form -->
        <div x-show="searchTab === 'single'">
            <form @submit.prevent="checkSingle()" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" x-model="query" placeholder="Enter domain name (e.g. awesomebrand.com)" required
                        class="w-full px-5 py-4 bg-obsidian-900 border border-white/15 rounded-2xl text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 text-base font-medium">
                </div>
                <button type="submit" :disabled="checking"
                    class="px-8 py-4 bg-gradient-to-r from-indigo-500 via-indigo-600 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-2xl transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 disabled:opacity-75">
                    <span x-show="!checking">Search Domain</span>
                    <span x-show="checking" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Checking...
                    </span>
                </button>
            </form>

            <!-- Quick TLD shortcuts -->
            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                <span>Filter by extension:</span>
                @foreach(['.com', '.net', '.org', '.xyz', '.io', '.co'] as $ext)
                    <button type="button" @click="query = (query.includes('.') ? query.split('.')[0] : (query || 'example')) + '{{ $ext }}'; checkSingle();"
                        class="px-2 py-0.5 rounded bg-white/5 border border-white/10 hover:border-indigo-400 text-slate-300 font-mono transition">
                        {{ $ext }}
                    </button>
                @endforeach
            </div>

            <!-- Single Result Card -->
            <div x-show="singleResult" x-cloak class="mt-6 p-6 rounded-2xl bg-obsidian-900 border border-white/10">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-black text-white font-mono" x-text="singleResult?.domain"></span>
                            <template x-if="singleResult?.is_available">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">Available</span>
                            </template>
                            <template x-if="!singleResult?.is_available">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/40">Registered</span>
                            </template>
                        </div>
                        <template x-if="singleResult?.is_available">
                            <p class="text-xs text-slate-300 mt-1">Ready for instant registration at direct wholesale pricing.</p>
                        </template>
                        <template x-if="!singleResult?.is_available">
                            <p class="text-xs text-slate-400 mt-1">This domain is registered. You can initiate a transfer or query WHOIS records.</p>
                        </template>
                    </div>

                    <div class="flex items-center gap-4">
                        <template x-if="singleResult?.is_available">
                            <div class="text-right">
                                <span class="text-2xl font-black text-indigo-400 font-mono" x-text="'$' + Number(singleResult?.price_usd).toFixed(2)"></span>
                                <span class="block text-xs text-slate-400 font-mono" x-text="'~ ৳' + Number(singleResult?.price_bdt).toFixed(2) + ' BDT'"></span>
                            </div>
                        </template>

                        <template x-if="singleResult?.is_available">
                            <a :href="'{{ route('login') }}?redirect=' + encodeURIComponent('{{ route('client.domains.register') }}?domain=' + singleResult?.domain)"
                                class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm rounded-xl transition shadow">
                                Register Now →
                            </a>
                        </template>
                        <template x-if="!singleResult?.is_available">
                            <a :href="'{{ route('whois.index') }}?domain=' + encodeURIComponent(singleResult?.domain)"
                                class="px-5 py-2.5 bg-white/10 hover:bg-white/15 text-white font-semibold text-sm rounded-xl transition border border-white/10">
                                View WHOIS Record
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Search Form -->
        <div x-show="searchTab === 'bulk'" x-cloak>
            <form @submit.prevent="checkBulk()" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Enter domain names (one per line, up to 20):</label>
                    <textarea x-model="bulkDomains" rows="6" placeholder="mybrand.com&#10;mybrand.net&#10;mybrand.org&#10;mybrand.xyz" required
                        class="w-full p-4 bg-obsidian-900 border border-white/15 rounded-2xl text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 text-sm font-mono"></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Separate with enter/newline</span>
                    <button type="submit" :disabled="checking"
                        class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition flex items-center gap-2 shadow-lg shadow-indigo-600/30 disabled:opacity-75">
                        <span x-show="!checking">Check All Domains</span>
                        <span x-show="checking" x-cloak>Checking Registry...</span>
                    </button>
                </div>
            </form>

            <!-- Bulk Results Table -->
            <div x-show="bulkResults.length > 0" x-cloak class="mt-8 overflow-x-auto rounded-2xl border border-white/10">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white/5 border-b border-white/10 text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5 pl-4">Domain Name</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5">Wholesale Price (USD)</th>
                            <th class="p-3.5">Converted (BDT)</th>
                            <th class="p-3.5 pr-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-200">
                        <template x-for="item in bulkResults" :key="item.domain">
                            <tr class="hover:bg-white/5 transition">
                                <td class="p-3.5 pl-4 font-mono font-bold text-white text-sm" x-text="item.domain"></td>
                                <td class="p-3.5">
                                    <template x-if="item.is_available">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Available</span>
                                    </template>
                                    <template x-if="!item.is_available">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">Taken</span>
                                    </template>
                                </td>
                                <td class="p-3.5 font-mono font-bold text-indigo-400 text-sm" x-text="'$' + Number(item.price_usd).toFixed(2)"></td>
                                <td class="p-3.5 font-mono text-slate-300" x-text="'৳' + Number(item.price_bdt).toFixed(2)"></td>
                                <td class="p-3.5 pr-4 text-right">
                                    <template x-if="item.is_available">
                                        <a :href="'{{ route('login') }}?redirect=' + encodeURIComponent('{{ route('client.domains.register') }}?domain=' + item.domain)"
                                            class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition inline-block">
                                            Register
                                        </a>
                                    </template>
                                    <template x-if="!item.is_available">
                                        <a :href="'{{ route('whois.index') }}?domain=' + encodeURIComponent(item.domain)"
                                            class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 font-semibold text-xs transition inline-block">
                                            WHOIS
                                        </a>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Error Message -->
        <div x-show="error" x-cloak class="mt-4 p-4 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-sm">
            <span x-text="error"></span>
        </div>
    </div>

    <!-- Supported TLDs Matrix Preview -->
    <div class="max-w-6xl mx-auto mt-20">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Active TLD Registry Matrix</h2>
                <p class="text-xs text-slate-400 mt-1">Direct upstream connections and wholesale pricing tiers</p>
            </div>
            <a href="{{ route('pricing') }}" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-bold text-indigo-400 transition">
                View Full Pricing Matrix →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($allTlds as $tld)
                <div class="p-5 rounded-2xl glass-card border border-white/10 hover:border-indigo-500/40 transition">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-extrabold text-white font-mono">{{ $tld->tld }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-white/5 border border-white/10 text-slate-400">
                            {{ $tld->min_years }} - {{ $tld->max_years }} Yrs
                        </span>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-slate-300">
                            <span>Register:</span>
                            <span class="font-bold text-white font-mono">${{ number_format((float)$tld->client_register, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Renew:</span>
                            <span class="font-mono">${{ number_format((float)$tld->client_renew, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Transfer:</span>
                            <span class="font-mono">${{ number_format((float)$tld->client_transfer, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $tld->dns_management_available ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            DNS
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $tld->email_forwarding_available ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            Email Fwd
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $tld->id_protection_available ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            Privacy
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
