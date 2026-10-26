@extends('layouts.guest')

@section('content')
<div class="py-16 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto" x-data="{
    domain: '{{ request('domain', '') }}',
    loading: false,
    whoisData: null,
    error: null,
    showRaw: false,
    queryWhois() {
        let d = this.domain.trim();
        if (!d) return;
        this.loading = true;
        this.whoisData = null;
        this.error = null;

        fetch('{{ route('whois.query') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ domain: d })
        })
        .then(res => res.json())
        .then(data => {
            this.loading = false;
            if (data.success) {
                this.whoisData = data.data;
            } else {
                this.error = data.message || 'WHOIS query failed.';
            }
        })
        .catch(err => {
            this.loading = false;
            this.error = 'Failed to connect to WHOIS database.';
        });
    }
}" x-init="if (domain) { queryWhois(); }">

    <!-- Title -->
    <div class="text-center max-w-3xl mx-auto mb-10">
        <span class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-violet-500/20 text-violet-300 border border-violet-500/30">
            Public Registry Inspector
        </span>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mt-4">
            WHOIS Domain Lookup
        </h1>
        <p class="mt-2 text-sm text-slate-400">
            Inspect public registration records, nameservers, status codes, and expiration dates for any domain.
        </p>
    </div>

    <!-- Query Form -->
    <div class="glass-card p-6 sm:p-8 rounded-3xl border border-white/10 shadow-2xl mb-10">
        <form @submit.prevent="queryWhois()" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" x-model="domain" placeholder="Enter domain name (e.g. google.com, laravel.com)" required
                    class="w-full px-5 py-4 bg-obsidian-900 border border-white/15 rounded-2xl text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 font-mono text-base">
            </div>
            <button type="submit" :disabled="loading"
                class="px-8 py-4 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold rounded-2xl transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 disabled:opacity-75">
                <span x-show="!loading">Lookup WHOIS</span>
                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Querying...
                </span>
            </button>
        </form>
    </div>

    <!-- Error Banner -->
    <div x-show="error" x-cloak class="p-4 rounded-2xl bg-rose-950/80 border border-rose-800 text-rose-300 text-sm mb-8 backdrop-blur">
        <span x-text="error"></span>
    </div>

    <!-- Formatted Results Cards -->
    <div x-show="whoisData" x-cloak class="space-y-6">
        <!-- Main Domain Card -->
        <div class="p-6 sm:p-8 rounded-3xl glass-card border border-white/10 shadow-2xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/10">
                <div>
                    <span class="text-2xl sm:text-3xl font-black text-white font-mono" x-text="whoisData?.domain"></span>
                    <p class="text-xs text-slate-400 mt-1 font-mono">
                        Registrar: <strong class="text-white" x-text="whoisData?.registrar || 'Not Disclosed'"></strong>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <template x-if="whoisData?.is_registered">
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/40">
                            Registered
                        </span>
                    </template>
                    <template x-if="!whoisData?.is_registered">
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                            Available for Registration
                        </span>
                    </template>
                </div>
            </div>

            <!-- Grid of Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 text-xs">
                <!-- Col 1: Dates -->
                <div class="p-4 rounded-2xl bg-obsidian-900 border border-white/5 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Key Registry Dates</span>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Created:</span>
                        <span class="font-mono text-white font-semibold" x-text="whoisData?.creation_date || 'N/A'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Expires:</span>
                        <span class="font-mono text-emerald-400 font-bold" x-text="whoisData?.expiration_date || 'N/A'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Updated:</span>
                        <span class="font-mono text-slate-300" x-text="whoisData?.updated_date || 'N/A'"></span>
                    </div>
                </div>

                <!-- Col 2: Nameservers -->
                <div class="p-4 rounded-2xl bg-obsidian-900 border border-white/5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Delegated Nameservers</span>
                    <template x-if="whoisData?.nameservers && whoisData.nameservers.length > 0">
                        <ul class="space-y-1 font-mono text-[11px] text-indigo-300">
                            <template x-for="ns in whoisData.nameservers" :key="ns">
                                <li class="truncate" x-text="ns"></li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="!whoisData?.nameservers || whoisData.nameservers.length === 0">
                        <p class="text-slate-500 italic">No public nameservers listed.</p>
                    </template>
                </div>

                <!-- Col 3: Status / Action -->
                <div class="p-4 rounded-2xl bg-obsidian-900 border border-white/5 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Domain Status</span>
                        <p class="font-mono text-[11px] text-amber-300 truncate" x-text="whoisData?.status || 'Active / Delegated'"></p>
                    </div>

                    <div class="mt-4">
                        <template x-if="!whoisData?.is_registered">
                            <a :href="'{{ route('login') }}?redirect=' + encodeURIComponent('{{ route('client.domains.register') }}?domain=' + whoisData?.domain)"
                                class="w-full text-center py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition block">
                                Register this Domain →
                            </a>
                        </template>
                        <template x-if="whoisData?.is_registered">
                            <a :href="'{{ route('domain.search') }}?query=' + whoisData?.domain"
                                class="w-full text-center py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition block">
                                Check Alternative TLDs
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raw WHOIS Accordion -->
        <div class="glass-card rounded-2xl border border-white/10 overflow-hidden">
            <button @click="showRaw = !showRaw" type="button"
                class="w-full p-4 text-left flex items-center justify-between text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition">
                <span>View Full Raw Registry WHOIS Output</span>
                <span x-text="showRaw ? 'Hide ▲' : 'Show ▼'" class="font-mono text-indigo-400"></span>
            </button>

            <div x-show="showRaw" x-cloak class="p-6 bg-slate-950 border-t border-white/10 text-xs font-mono text-slate-300 overflow-x-auto leading-relaxed">
                <pre x-text="whoisData?.raw || 'No raw output available for this record.'"></pre>
            </div>
        </div>
    </div>
</div>
@endsection
