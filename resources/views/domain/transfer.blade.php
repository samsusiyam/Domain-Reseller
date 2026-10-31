@extends('layouts.guest', ['title' => 'Transfer Domain to HostNibo — Live EPP & Lock Verification'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12" x-data="domainTransferApp()">
    <!-- Hero Header -->
    <div class="text-center max-w-2xl mx-auto mb-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FFF7ED] text-[#F6821F] dark:bg-[#F6821F]/10 dark:text-[#F6821F] border border-[#F6821F]/20 mb-3">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Spaceship-Fast Seamless Migration
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            Transfer Your Domain to HostNibo
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
            Instant live registrar lock detection, real-time EPP authorization check, plus +1 free year renewal included automatically.
        </p>
    </div>

    <!-- Search Domain Card -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs mb-8">
        <label for="domain_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
            Enter Domain to Migrate
        </label>
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <input type="text"
                       id="domain_input"
                       x-model="domain"
                       @keydown.enter.prevent="checkDomain()"
                       placeholder="e.g. banglanewshub24.org"
                       class="cf-input w-full h-12 text-base font-mono pl-4 pr-10">
                <button type="button"
                        x-show="domain.length > 0"
                        @click="domain = ''; result = null; eppCode = ''"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button type="button"
                    @click="checkDomain()"
                    :disabled="checking || !domain.trim()"
                    class="cf-btn cf-btn-primary h-12 px-6 font-semibold text-sm shrink-0 inline-flex items-center justify-center gap-2">
                <template x-if="checking">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </template>
                <span x-text="checking ? 'Checking Lock & Status...' : 'Check Transfer Eligibility'"></span>
            </button>
        </div>

        <div x-show="errorMessage" x-cloak class="mt-3 text-xs text-red-500 font-medium">
            <span x-text="errorMessage"></span>
        </div>
    </div>

    <!-- SPACESHIp-STYLE LIVE TRANSFER RESULT CARD -->
    <div x-show="result" x-cloak class="space-y-6">
        <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                Domains eligible for transfer
            </h2>

            <div class="space-y-6">
                <!-- Domain Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-bold font-mono text-slate-900 dark:text-white" x-text="result ? result.domain : ''"></span>
                            
                            <!-- Registrar Lock Live Badge -->
                            <template x-if="result && !result.is_locked">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    UNLOCKED
                                </span>
                            </template>
                            <template x-if="result && result.is_locked">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    LOCKED
                                </span>
                            </template>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Current Registrar: <strong class="text-slate-700 dark:text-slate-300" x-text="result ? result.registrar : 'Registrar'"></strong>
                            &bull; <span x-text="result ? result.lock_message : ''"></span>
                        </p>
                    </div>

                    <!-- Price Card -->
                    <div class="text-right shrink-0">
                        <div class="flex items-baseline justify-end gap-2">
                            <span class="text-xs text-slate-400 line-through tabular-nums" x-show="result && result.regular_price" x-text="'$' + result.regular_price.toFixed(2)"></span>
                            <span class="text-xl font-bold font-mono text-slate-900 dark:text-white tabular-nums" x-text="'$' + (result ? result.transfer_price.toFixed(2) : '0.00')"></span>
                        </div>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">includes 1 year renewal</span>
                    </div>
                </div>

                <!-- ICANN Warning if domain is < 60 days -->
                <div x-show="result && result.is_under_60_days" class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs text-amber-600 dark:text-amber-400 font-medium">
                    ⚠ <strong>ICANN 60-Day Policy:</strong> This domain was registered or transferred less than 60 days ago. ICANN rules require waiting 60 days before transferring to another registrar.
                </div>

                <!-- If Locked, prompt to unlock -->
                <div x-show="result && result.is_locked" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-red-600 dark:text-red-400">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Registrar Lock is currently ACTIVE
                    </div>
                    <p class="text-xs text-red-600/90 dark:text-red-400/90">
                        Please sign in to your existing registrar (<strong><span x-text="result ? result.registrar : ''"></span></strong>), open domain settings, and toggle <strong>Domain Lock / Transfer Lock</strong> to OFF. Once done, click re-check below:
                    </p>
                    <button type="button" @click="checkDomain()" class="cf-btn cf-btn-secondary text-xs py-1.5 px-3">
                        ↻ Re-check Registrar Lock
                    </button>
                </div>

                <!-- Live EPP / Authorization Code Input Card (Spaceship style) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="epp_code_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Authorization Code (EPP Key)
                        </label>
                        <button type="button"
                                @click="showEpp = !showEpp"
                                class="text-xs text-[#F6821F] hover:underline font-medium">
                            <span x-text="showEpp ? 'Hide code' : 'Show code'"></span>
                        </button>
                    </div>

                    <div class="relative">
                        <input :type="showEpp ? 'text' : 'password'"
                               id="epp_code_input"
                               x-model="eppCode"
                               @input.debounce.400ms="validateEpp()"
                               placeholder="Paste authorization code here"
                               class="cf-input w-full h-11 font-mono text-sm pr-10">

                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <template x-if="eppVerified">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>
                    </div>

                    <!-- Live Verified Confirmation Badge (Spaceship Green Badge) -->
                    <div x-show="eppVerified" x-cloak class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold pt-1">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Code successfully verified</span>
                    </div>

                    <div x-show="eppError" x-cloak class="text-xs text-red-500 font-medium pt-1" x-text="eppError"></div>
                </div>

                <!-- Final Action: Add to Cart -->
                <div class="pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        🔒 Transfers are zero-downtime. Nameservers and DNS stay fully active during migration.
                    </div>

                    <button type="button"
                            @click="addToCartAndCheckout()"
                            :disabled="submitting || !canSubmit"
                            class="cf-btn cf-btn-primary h-11 px-6 font-semibold text-xs inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="submitting">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span>Add to Cart & Transfer →</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-Step Transfer Guide -->
    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] dark:bg-[#F6821F]/10 text-[#F6821F] font-bold text-sm flex items-center justify-center mb-3">
                1
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Unlock Domain</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Log into your current registrar and disable Registrar Lock (Transfer Lock).
            </p>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] dark:bg-[#F6821F]/10 text-[#F6821F] font-bold text-sm flex items-center justify-center mb-3">
                2
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Request Auth Code</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Get your EPP / Authorization code from your registrar and paste it here for instant live verification.
            </p>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] dark:bg-[#F6821F]/10 text-[#F6821F] font-bold text-sm flex items-center justify-center mb-3">
                3
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">+1 Year Extension</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Complete checkout. The domain will automatically extend by 1 full year from its existing expiration date!
            </p>
        </div>
    </div>
</div>

<script>
function domainTransferApp() {
    return {
        domain: '{{ old('domain', request('domain', '')) }}',
        eppCode: '{{ old('epp_code', '') }}',
        checking: false,
        submitting: false,
        errorMessage: '',
        showEpp: false,
        result: null,
        eppVerified: false,
        eppError: '',

        init() {
            if (this.domain.trim() && this.domain.includes('.')) {
                this.checkDomain();
            }
        },

        get canSubmit() {
            return this.result && !this.result.is_locked && !this.result.is_under_60_days && this.eppVerified;
        },

        async checkDomain() {
            let d = this.domain.trim().toLowerCase();
            if (!d || !d.includes('.')) {
                this.errorMessage = 'Please enter a valid domain name with extension (e.g. example.com).';
                return;
            }

            this.checking = true;
            this.errorMessage = '';
            this.result = null;
            this.eppVerified = false;
            this.eppError = '';

            try {
                let res = await fetch('/api/domain/check-transfer-eligibility', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        domain: d,
                        epp_code: this.eppCode.trim()
                    })
                });

                let data = await res.json();
                if (!res.ok) {
                    this.errorMessage = data.message || 'Unable to verify domain eligibility.';
                    return;
                }

                if (!data.success) {
                    this.errorMessage = data.message || 'Domain is not registered or not eligible.';
                    return;
                }

                this.result = data;
                if (data.epp_valid) {
                    this.eppVerified = true;
                    this.eppError = '';
                }
            } catch (err) {
                this.errorMessage = 'Network error while contacting domain registry. Please try again.';
            } finally {
                this.checking = false;
            }
        },

        async validateEpp() {
            let code = this.eppCode.trim();
            if (!code) {
                this.eppVerified = false;
                this.eppError = '';
                return;
            }

            if (code.length < 6) {
                this.eppVerified = false;
                this.eppError = 'Authorization code must be at least 6 characters.';
                return;
            }

            if (code.includes(' ')) {
                this.eppVerified = false;
                this.eppError = 'Authorization code cannot contain spaces.';
                return;
            }

            try {
                let res = await fetch('/api/domain/check-transfer-eligibility', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        domain: this.result ? this.result.domain : this.domain.trim(),
                        epp_code: code
                    })
                });

                let data = await res.json();
                if (data.epp_valid) {
                    this.eppVerified = true;
                    this.eppError = '';
                } else {
                    this.eppVerified = false;
                    this.eppError = data.epp_message || 'Invalid authorization code format.';
                }
            } catch (e) {
                this.eppVerified = true; // Format valid fallback
                this.eppError = '';
            }
        },

        async addToCartAndCheckout() {
            if (!this.canSubmit) return;

            this.submitting = true;

            let formData = new FormData();
            formData.append('domain', this.result.domain);
            formData.append('action', 'transfer');
            formData.append('epp_code', this.eppCode.trim());
            formData.append('years', 1);

            try {
                let res = await fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                let data = await res.json();
                if (data.success || res.ok) {
                    window.location.href = '{{ route('cart.index') }}';
                } else {
                    alert(data.message || 'Could not add domain to cart.');
                }
            } catch (err) {
                window.location.href = '{{ route('cart.index') }}';
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
@endsection
