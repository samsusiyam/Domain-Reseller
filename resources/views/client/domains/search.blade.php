@extends('layouts.app', ['title' => 'Search Domains', 'header' => 'Domain Availability Search'])

@section('content')
<div class="max-w-4xl space-y-6" x-data="{
    domainQuery: '{{ request('domain', '') }}',
    checking: false,
    result: null,
    error: null,
    check() {
        if (!this.domainQuery.trim()) return;
        this.checking = true;
        this.result = null;
        this.error = null;

        fetch('{{ route('client.domains.check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ domain: this.domainQuery })
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
            this.error = 'Search engine connection error.';
        });
    }
}" x-init="if (domainQuery) { check(); }">

    <!-- Search Box Card -->
    <div class="bg-white dark:bg-[#111827] p-6 sm:p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Find & Register a Domain</h2>
        <p class="text-xs text-slate-500 mb-6">Real-time availability lookups across all active upstream registrars.</p>

        <form @submit.prevent="check()" class="flex flex-col sm:flex-row gap-3">
            <input type="text" x-model="domainQuery" placeholder="Enter your desired domain (e.g. cloudhost.com)" required autofocus
                class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-700 rounded-xl text-base focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            <button type="submit" :disabled="checking"
                class="px-8 py-3 cf-btn cf-btn-primary text-white font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow disabled:opacity-75">
                <span x-show="!checking">Search</span>
                <span x-show="checking" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Checking...
                </span>
            </button>
        </form>

        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
            <span>Popular:</span>
            <button type="button" @click="domainQuery = domainQuery.split('.')[0] + '.com'; check()" class="text-[#F6821F] hover:underline">.com</button>
            <span>•</span>
            <button type="button" @click="domainQuery = domainQuery.split('.')[0] + '.net'; check()" class="text-[#F6821F] hover:underline">.net</button>
            <span>•</span>
            <button type="button" @click="domainQuery = domainQuery.split('.')[0] + '.org'; check()" class="text-[#F6821F] hover:underline">.org</button>
            <span>•</span>
            <button type="button" @click="domainQuery = domainQuery.split('.')[0] + '.xyz'; check()" class="text-[#F6821F] hover:underline">.xyz</button>
        </div>
    </div>

    <!-- Error Alert -->
    <div x-show="error" x-cloak>
        <x-alert type="error" ::message="error" />
    </div>

    <!-- Results Box -->
    <div x-show="result" x-cloak class="bg-white dark:bg-[#111827] p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <span class="text-xl font-black text-slate-900 dark:text-white break-all" x-text="result?.domain"></span>
                    <template x-if="result?.is_available">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Available
                        </span>
                    </template>
                    <template x-if="!result?.is_available">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                            Taken
                        </span>
                    </template>
                </div>
                <template x-if="result?.is_available">
                    <p class="text-xs text-slate-500 mt-1">Ready for 1-10 year registration.</p>
                </template>
            </div>

            <div class="flex items-center gap-5">
                <template x-if="result?.is_available">
                    <div class="text-right">
                        <span class="text-2xl font-black text-[#F6821F]" x-text="'$' + Number(result?.price_usd).toFixed(2)"></span>
                        <span class="block text-xs text-slate-400" x-text="'~ ৳' + Number(result?.price_bdt).toFixed(2) + ' BDT'"></span>
                    </div>
                </template>

                <template x-if="result?.is_available">
                    <a :href="'{{ route('client.domains.register') }}?domain=' + encodeURIComponent(result?.domain)"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow">
                        Configure & Register &rarr;
                    </a>
                </template>

                <template x-if="!result?.is_available">
                    <a :href="'{{ route('client.domains.transfer') }}?domain=' + encodeURIComponent(result?.domain)"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl transition">
                        Transfer to Us
                    </a>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
