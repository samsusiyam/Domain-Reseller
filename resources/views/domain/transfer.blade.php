@extends('layouts.guest', ['title' => 'Transfer Domain to HostNibo'])

@section('content')
<script>
function domainTransferApp() {
    return {
        domain: '{{ old('domain', request('domain', '')) }}',
        eppCode: '{{ old('epp_code', '') }}',
        loading: false,
        errorMessage: '',
        showEpp: false,
        transferRate: null,
        tlds: @json($allTlds->pluck('client_transfer', 'tld')),

        calculateRate() {
            let d = this.domain.trim().toLowerCase();
            if (!d.includes('.')) {
                this.transferRate = null;
                return;
            }
            let parts = d.split('.');
            let tld = '.' + parts.slice(1).join('.');
            if (this.tlds && this.tlds[tld]) {
                this.transferRate = this.tlds[tld];
            } else {
                this.transferRate = null;
            }
        },

        submitTransfer() {
            if (!this.domain || !this.eppCode) {
                this.errorMessage = 'Please provide both your domain name and EPP / Auth code.';
                return;
            }
            this.loading = true;
            this.errorMessage = '';

            let formData = new FormData();
            formData.append('domain', this.domain.trim().toLowerCase());
            formData.append('action', 'transfer');
            formData.append('epp_code', this.eppCode.trim());
            formData.append('years', 1);

            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    window.location.href = '{{ route('cart.index') }}';
                } else {
                    this.errorMessage = data.message || 'Unable to add domain transfer to cart.';
                }
            })
            .catch(() => {
                this.loading = false;
                this.errorMessage = 'Network connection failed. Please retry.';
            });
        }
    };
}
</script>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14" x-data="domainTransferApp()" x-init="if (domain) calculateRate()">

    <!-- Header Section -->
    <div class="mb-8">
        <div class="cf-breadcrumb mb-2">
            <a href="{{ route('home') }}">Home</a>
            <span class="cf-breadcrumb-sep">/</span>
            <a href="{{ route('domain.search') }}">Domains</a>
            <span class="cf-breadcrumb-sep">/</span>
            <span class="cf-breadcrumb-current">Transfer</span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Transfer your domain to HostNibo</h1>
        <p class="text-xs sm:text-sm mt-1" style="color: var(--text-muted);">
            Seamlessly migrate your domains with zero downtime. Every transfer automatically includes an extra 1-year renewal extension.
        </p>
    </div>

    <!-- Main Transfer Grid: Form on Left, Transfer Steps on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Transfer Initiation Form (7 cols) -->
        <div class="lg:col-span-7 cf-panel p-6 sm:p-8">
            <h2 class="text-base font-semibold mb-1" style="color: var(--text);">Initiate domain transfer</h2>
            <p class="text-xs mb-6" style="color: var(--text-muted);">Enter the existing domain name and its authorization (EPP) code from your current registrar.</p>

            <div x-show="errorMessage" x-cloak class="mb-4 p-3 rounded-lg text-xs font-medium text-red-600 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800" x-text="errorMessage"></div>

            <form @submit.prevent="submitTransfer()" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Domain name to transfer</label>
                    <div class="relative">
                        <input type="text"
                               x-model="domain"
                               @input="calculateRate()"
                               placeholder="example.com"
                               required
                               class="cf-input h-10 font-mono text-sm w-full">
                    </div>
                    <p class="text-[11px] mt-1" style="color: var(--text-muted);">Must be currently registered and unlocked at your existing registrar.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-medium" style="color: var(--text);">Authorization code (Auth / EPP)</label>
                        <button type="button" @click="showEpp = !showEpp" class="text-[11px] hover:underline cursor-pointer" style="color: var(--accent);">
                            <span x-text="showEpp ? 'Hide code' : 'Show code'"></span>
                        </button>
                    </div>
                    <input :type="showEpp ? 'text' : 'password'"
                           x-model="eppCode"
                           placeholder="Enter registrar Auth/EPP key"
                           required
                           class="cf-input h-10 font-mono text-sm w-full">
                    <p class="text-[11px] mt-1" style="color: var(--text-muted);">Request this secret code from your current domain provider.</p>
                </div>

                <!-- Live Transfer Price Banner -->
                <div class="p-3.5 rounded-lg border flex items-center justify-between text-xs" style="background-color: var(--surface); border-color: var(--border);">
                    <div>
                        <span class="font-medium" style="color: var(--text);">Transfer renewal rate</span>
                        <span class="block text-[11px]" style="color: var(--text-muted);">Includes +1 year registry renewal</span>
                    </div>
                    <div class="text-right">
                        <template x-if="transferRate">
                            <span class="font-bold text-sm font-mono text-emerald-600 dark:text-emerald-400" x-text="'$' + parseFloat(transferRate).toFixed(2)"></span>
                        </template>
                        <template x-if="!transferRate">
                            <span class="text-[11px] italic" style="color: var(--text-muted);">Enter domain to see rate</span>
                        </template>
                    </div>
                </div>

                <button type="button"
                        @click="submitTransfer()"
                        :disabled="loading"
                        class="cf-btn cf-btn-primary w-full h-11 font-medium text-sm flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <template x-if="!loading">
                        <div class="flex items-center gap-1.5">
                            <span>Proceed with transfer</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                    </template>
                    <template x-if="loading">
                        <div class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Checking registry eligibility...</span>
                        </div>
                    </template>
                </button>
            </form>
        </div>

        <!-- Right: 4-Step Transfer Guide & ICANN Guidelines (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- 4 Steps Card -->
            <div class="cf-panel p-6">
                <h3 class="text-xs font-semibold uppercase tracking-wider mb-4" style="color: var(--text-muted);">How transfer works</h3>
                
                <ol class="space-y-4 text-xs">
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-[#F6821F]/10 text-[#F6821F] font-bold text-[11px] flex items-center justify-center shrink-0 mt-0.5">1</span>
                        <div>
                            <p class="font-medium" style="color: var(--text);">Unlock at current provider</p>
                            <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Disable domain registrar theft lock and ensure WHOIS privacy is temporarily accessible.</p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-[#F6821F]/10 text-[#F6821F] font-bold text-[11px] flex items-center justify-center shrink-0 mt-0.5">2</span>
                        <div>
                            <p class="font-medium" style="color: var(--text);">Get your Auth / EPP code</p>
                            <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Copy the authorization key provided in your existing domain registrar control panel.</p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-[#F6821F]/10 text-[#F6821F] font-bold text-[11px] flex items-center justify-center shrink-0 mt-0.5">3</span>
                        <div>
                            <p class="font-medium" style="color: var(--text);">Submit transfer on HostNibo</p>
                            <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Checkout securely. The transfer fee automatically extends your domain validity by 1 full year.</p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-[#F6821F]/10 text-[#F6821F] font-bold text-[11px] flex items-center justify-center shrink-0 mt-0.5">4</span>
                        <div>
                            <p class="font-medium" style="color: var(--text);">Automated completion</p>
                            <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">The registry automatically transfers the domain within 5 to 7 days with zero website downtime.</p>
                        </div>
                    </li>
                </ol>
            </div>

            <!-- ICANN Rules Check -->
            <div class="p-4 rounded-xl border text-xs space-y-2" style="background-color: var(--surface); border-color: var(--border);">
                <div class="flex items-center gap-1.5 font-medium" style="color: var(--text);">
                    <svg class="w-4 h-4 text-[#F6821F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>ICANN 60-Day Transfer Rule</span>
                </div>
                <p class="text-[11px] leading-relaxed" style="color: var(--text-muted);">
                    Per ICANN policy, domains registered or transferred within the past 60 days cannot be transferred. Ensure your domain is at least 60 days old and not expired beyond grace period.
                </p>
            </div>

        </div>

    </div>

</div>
@endsection
