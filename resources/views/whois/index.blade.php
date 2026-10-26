@extends('layouts.guest')

@section('content')
<div class="py-12 bg-white" x-data="{
    domain: '{{ request('domain', '') }}',
    loading: false,
    whoisData: null,
    error: null,
    queryWhois() {
        if (!this.domain.trim()) return;
        this.loading = true;
        this.whoisData = null;
        this.error = null;

        fetch('{{ route('whois.query') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ domain: this.domain })
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">WHOIS Domain Lookup</h1>
            <p class="mt-2 text-sm text-slate-600">
                Inspect public registration records, nameservers, and expiration dates for any domain.
            </p>
        </div>

        <!-- Query Form -->
        <form @submit.prevent="queryWhois()" class="flex flex-col sm:flex-row gap-3 max-w-2xl mx-auto mb-10">
            <input type="text" x-model="domain" placeholder="Enter domain name (e.g. example.com)" required
                class="flex-1 px-4 py-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <button type="submit" :disabled="loading"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 disabled:opacity-75">
                <span x-show="!loading">Lookup WHOIS</span>
                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Querying...
                </span>
            </button>
        </form>

        <!-- Error Card -->
        <div x-show="error" x-cloak class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm mb-6">
            <span x-text="error"></span>
        </div>

        <!-- WHOIS Record Details Card -->
        <div x-show="whoisData" x-cloak class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" x-text="whoisData?.domain_name"></h3>
                    <span class="text-xs text-slate-500">Registry WHOIS Record</span>
                </div>
                <template x-if="whoisData?.is_private">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                        WHOIS Privacy Protected
                    </span>
                </template>
            </div>

            <div class="p-6 space-y-6">
                <!-- Meta Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Status</span>
                        <span class="font-semibold text-slate-800 uppercase" x-text="whoisData?.status || 'Active'"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Created Date</span>
                        <span class="font-semibold text-slate-800" x-text="whoisData?.created_date || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Expiration Date</span>
                        <span class="font-semibold text-slate-800" x-text="whoisData?.expires_date || 'N/A'"></span>
                    </div>
                </div>

                <!-- Nameservers -->
                <div class="pt-4 border-t border-slate-100">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Authoritative Nameservers</span>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="ns in (whoisData?.nameservers || [])" :key="ns">
                            <span class="px-3 py-1 bg-slate-100 text-slate-800 font-mono text-xs rounded-lg border border-slate-200" x-text="ns"></span>
                        </template>
                    </div>
                </div>

                <!-- Raw output if available -->
                <template x-if="whoisData?.raw_text">
                    <div class="pt-4 border-t border-slate-100">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Raw WHOIS Output</span>
                        <pre class="bg-slate-900 text-slate-200 p-4 rounded-xl text-xs font-mono overflow-x-auto max-h-72" x-text="whoisData?.raw_text"></pre>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
