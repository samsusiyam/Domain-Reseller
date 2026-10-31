@extends('layouts.admin', ['title' => 'Create New Order', 'header' => 'New Order'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="adminOrderApp()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Create Manual Order</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Context-aware registration, live transfer verification, and renewal order creation</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs">
            <ul class="list-disc list-inside space-y-1 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.orders.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 space-y-6 shadow-xs">
            <!-- 1. Client Selection -->
            <div>
                <h2 class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-800">
                    1. Client Selection
                </h2>
                <div class="mt-4">
                    <label for="user_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Assign to Client / Reseller <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" id="user_id" required class="cf-input w-full font-semibold">
                        <option value="">-- Choose Client Account --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', request('user_id')) == $user->id ? 'selected' : '' }}>
                                #{{ $user->id }} — {{ $user->name }} ({{ $user->email }}) {{ $user->company_name ? "• {$user->company_name}" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 2. Domain Order Specifications -->
            <div>
                <h2 class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-800">
                    2. Order Action & Domain Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="item_type" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Order Action Type <span class="text-red-500">*</span>
                        </label>
                        <select name="item_type" id="item_type" x-model="actionType" required class="cf-input w-full font-semibold text-[#F6821F]">
                            <option value="domain_register">New Domain Registration</option>
                            <option value="domain_transfer">Incoming Domain Transfer</option>
                            <option value="domain_renew">Domain Renewal</option>
                        </select>
                    </div>

                    <div>
                        <label for="domain_name" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Domain Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="domain_name" id="domain_name" required
                               x-model="domainName"
                               @input.debounce.500ms="onDomainChanged()"
                               placeholder="e.g. example.com"
                               class="cf-input w-full font-mono text-sm">
                    </div>
                </div>

                <!-- DYNAMIC CONTEXT FIELD: TRANSFER PRE-FLIGHT & LIVE LOCK CHECK -->
                <div x-show="actionType === 'domain_transfer'" x-cloak class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#F6821F] flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Live Transfer & Registrar Lock Verification
                        </span>

                        <button type="button"
                                @click="checkTransferStatus()"
                                :disabled="checkingLock || !domainName.trim()"
                                class="cf-btn cf-btn-secondary text-xs py-1 px-3 inline-flex items-center gap-1.5">
                            <template x-if="checkingLock">
                                <svg class="animate-spin w-3 h-3 text-[#F6821F]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span x-text="checkingLock ? 'Querying Registry...' : 'Check Live Lock Status'"></span>
                        </button>
                    </div>

                    <!-- Live Verification Output Banner -->
                    <template x-if="transferCheck">
                        <div class="p-3.5 rounded-lg border text-xs"
                             :class="transferCheck.is_locked ? 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'">
                            <div class="flex items-center justify-between font-semibold mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase"
                                          :class="transferCheck.is_locked ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white'"
                                          x-text="transferCheck.lock_status"></span>
                                    <span x-text="transferCheck.lock_message"></span>
                                </div>
                                <span class="text-[11px] font-mono text-slate-400" x-text="'Registrar: ' + (transferCheck.registrar || 'Unknown')"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] pt-2 border-t border-slate-200/40 dark:border-slate-700/40">
                                <span>60-Day Transfer Restriction: <strong x-text="transferCheck.is_under_60_days ? 'BLOCKED (< 60 days old)' : 'PASSED (Eligible)'"></strong></span>
                                <span x-show="transferCheck.transfer_price">Est. Transfer Cost: <strong x-text="'$' + Number(transferCheck.transfer_price).toFixed(2)"></strong></span>
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="epp_code" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                EPP / Authorization Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="epp_code" id="epp_code"
                                   x-model="eppCode"
                                   placeholder="EPP auth code provided by losing registrar"
                                   class="cf-input w-full font-mono text-xs">
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                Authorization secret key from the current registrar to authorize domain release.
                            </p>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Transfer Policy Guidelines</span>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed bg-white dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li>Domain must be unlocked at current registrar</li>
                                    <li>Whois privacy should be paused if FOA email required</li>
                                    <li>Automatically renews domain for 1 year upon completion</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DYNAMIC CONTEXT FIELD: REGISTRATION NAMESERVERS & PRIVACY -->
                <div x-show="actionType === 'domain_register'" x-cloak class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#F6821F]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                        </svg>
                        Authoritative Nameservers & WHOIS Privacy
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">Primary Nameserver (ns1)</label>
                            <input type="text" name="ns1" value="{{ old('ns1', 'ns1.hostnibo.com') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 dark:text-slate-400 mb-1">Secondary Nameserver (ns2)</label>
                            <input type="text" name="ns2" value="{{ old('ns2', 'ns2.hostnibo.com') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer pt-1">
                        <input type="checkbox" name="id_protection" value="1" checked
                               class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        <span class="text-xs text-slate-600 dark:text-slate-300">Enable free WHOIS Privacy ID Protection at upstream registry</span>
                    </label>
                </div>

                <!-- DYNAMIC CONTEXT FIELD: RENEWAL NOTICE -->
                <div x-show="actionType === 'domain_renew'" x-cloak class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#F6821F] flex items-center gap-1.5 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Domain Renewal Term & Lifecycle
                    </span>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        This action will extend the current expiration date by the selected registration period. Existing nameservers, DNS host records, and contact handles will be preserved.
                    </p>
                </div>

                <!-- Shared Specs Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label for="years" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Registration Period <span class="text-red-500">*</span>
                        </label>
                        <select name="years" id="years" x-model="years" required class="cf-input w-full font-semibold">
                            <template x-if="actionType === 'domain_transfer'">
                                <option value="1">1 Year (Standard Transfer Auto-Renew)</option>
                            </template>
                            <template x-if="actionType !== 'domain_transfer'">
                                <template x-for="y in [1,2,3,4,5,6,7,8,9,10]" :key="y">
                                    <option :value="y" x-text="y + ' Year(s)'"></option>
                                </template>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="registrar_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Upstream Registrar Driver <span class="text-red-500">*</span>
                        </label>
                        <select name="registrar_id" id="registrar_id" required class="cf-input w-full font-semibold">
                            @foreach($registrars as $reg)
                                <option value="{{ $reg->id }}" {{ old('registrar_id') == $reg->id ? 'selected' : '' }}>
                                    {{ $reg->name }} ({{ ucfirst($reg->slug) }} &bull; {{ ucfirst($reg->environment) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="total_amount" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Total Amount Charged (USD) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">$</span>
                            <input type="number" step="0.01" min="0" name="total_amount" id="total_amount" required
                                   value="{{ old('total_amount', '10.00') }}"
                                   class="cf-input w-full pl-7 font-mono text-sm font-semibold">
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label for="status" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Initial Order Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required class="cf-input w-full font-semibold">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending (Awaiting Accept / Manual Approval)</option>
                            <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Processing (In Queue for Registrar)</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed (Already Registered / Manual Sync)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. Automation & Billing Actions -->
            <div>
                <h2 class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-slate-800">
                    3. Automation & Settlement Actions
                </h2>

                <div class="space-y-3 pt-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="create_invoice" value="1" checked
                               class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        <span class="text-xs text-slate-700 dark:text-slate-300">
                            Generate official billing invoice for this order
                        </span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="mark_paid" value="1" checked
                               class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        <span class="text-xs text-slate-700 dark:text-slate-300">
                            Mark invoice as <strong>PAID</strong> immediately (Manual Admin settlement)
                        </span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="provision_now" value="1"
                               class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        <span class="text-xs text-slate-700 dark:text-slate-300">
                            ⚡ <strong>Provision Now:</strong> Dispatch live API request to registrar driver immediately upon creation
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit & Cancel -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.orders.index') }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
            <button type="submit" class="cf-btn cf-btn-primary text-xs inline-flex items-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Order
            </button>
        </div>
    </form>
</div>

<script>
function adminOrderApp() {
    return {
        actionType: '{{ old('item_type', 'domain_register') }}',
        domainName: '{{ old('domain_name', '') }}',
        eppCode: '{{ old('epp_code', '') }}',
        years: {{ old('years', 1) }},
        checkingLock: false,
        transferCheck: null,

        onDomainChanged() {
            if (this.actionType === 'domain_transfer' && this.domainName.includes('.')) {
                this.checkTransferStatus();
            }
        },

        async checkTransferStatus() {
            let d = this.domainName.trim().toLowerCase();
            if (!d || !d.includes('.')) return;

            this.checkingLock = true;
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
                this.transferCheck = data;
                if (data.transfer_price) {
                    let totalInput = document.getElementById('total_amount');
                    if (totalInput && (!totalInput.value || totalInput.value == '10.00')) {
                        totalInput.value = Number(data.transfer_price).toFixed(2);
                    }
                }
            } catch (e) {
                console.error('RDAP error:', e);
            } finally {
                this.checkingLock = false;
            }
        }
    };
}
</script>
@endsection
