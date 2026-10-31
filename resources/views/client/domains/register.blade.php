@extends('layouts.app', ['title' => 'Register Domain', 'header' => 'Configure Registration'])

@section('content')
<div class="max-w-4xl" x-data="{
    years: 1,
    idProtection: true,
    dnsManagement: true,
    emailForwarding: false,
    useCustomNs: false,
    idProtectionCost: {{ (float)($tldModel->id_protection_cost ?? 0) }},
    dnsManagementCost: {{ (float)($tldModel->dns_management_cost ?? 0) }},
    emailForwardingCost: {{ (float)($tldModel->email_forwarding_cost ?? 0) }},
    prices: {{ json_encode($prices) }},
    get totalUsd() {
        let base = Number(this.prices[this.years]?.total_usd || 0);
        if (this.idProtection && this.idProtectionCost > 0) {
            base += (this.idProtectionCost * this.years);
        }
        if (this.dnsManagement && this.dnsManagementCost > 0) {
            base += (this.dnsManagementCost * this.years);
        }
        if (this.emailForwarding && this.emailForwardingCost > 0) {
            base += (this.emailForwardingCost * this.years);
        }
        return base;
    }
}">
    <form action="{{ route('client.domains.register.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="domain" value="{{ $domain }}">

        <!-- Domain & Period Card -->
        <div class="bg-white dark:bg-[#111827] p-6 sm:p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 gap-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Domain To Register</span>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $domain }}</h2>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Registration Term</span>
                    <select name="years" x-model="years" class="mt-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-bold bg-white dark:bg-[#111827] text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#F6821F]">
                        @for($y = 1; $y <= 10; $y++)
                            <option value="{{ $y }}">{{ $y }} {{ Str::plural('Year', $y) }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <!-- Addons -->
            <div class="pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Available Domain Addons</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Included features and security options</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- ID Protection -->
                    <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-start gap-3 cursor-pointer hover:border-[#F6821F] transition relative" :class="idProtection ? 'border-[#F6821F] bg-orange-50/40 dark:bg-orange-950/20' : ''">
                        <input type="checkbox" name="id_protection" value="1" x-model="idProtection" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold text-slate-900 dark:text-white block">ID Protection</span>
                                @if(!empty($tldModel) && (float)$tldModel->id_protection_cost > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        ${{ number_format($tldModel->id_protection_cost, 2) }}/yr
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-mono">
                                        FREE
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block mt-1">Mask personal WHOIS contact data from public scrapers and spam</span>
                        </div>
                    </label>

                    <!-- DNS Management -->
                    <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-start gap-3 cursor-pointer hover:border-[#F6821F] transition relative" :class="dnsManagement ? 'border-[#F6821F] bg-orange-50/40 dark:bg-orange-950/20' : ''">
                        <input type="checkbox" name="dns_management" value="1" x-model="dnsManagement" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold text-slate-900 dark:text-white block">DNS Management</span>
                                @if(!empty($tldModel) && (float)$tldModel->dns_management_cost > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono">
                                        ${{ number_format($tldModel->dns_management_cost, 2) }}/yr
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-mono">
                                        FREE
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block mt-1">Hosted cloud DNS zone editor with global low-latency nameservers</span>
                        </div>
                    </label>

                    <!-- Email Forwarding -->
                    <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-start gap-3 cursor-pointer hover:border-[#F6821F] transition relative" :class="emailForwarding ? 'border-[#F6821F] bg-orange-50/40 dark:bg-orange-950/20' : ''">
                        <input type="checkbox" name="email_forwarding" value="1" x-model="emailForwarding" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold text-slate-900 dark:text-white block">Email Forwarding</span>
                                @if(!empty($tldModel) && (float)$tldModel->email_forwarding_cost > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono">
                                        ${{ number_format($tldModel->email_forwarding_cost, 2) }}/yr
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-mono">
                                        FREE
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block mt-1">Forward custom domain emails directly to your Gmail or Outlook inbox</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Nameservers -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 mt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Nameservers</h3>
                    <button type="button" @click="useCustomNs = !useCustomNs" class="text-xs font-bold text-[#F6821F] hover:underline">
                        <span x-text="useCustomNs ? 'Use Default Nameservers' : 'Use Custom Nameservers'"></span>
                    </button>
                </div>

                <div x-show="!useCustomNs" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                    <span>Using default HostNibo high-performance cloud Anycast nameservers.</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">Recommended</span>
                </div>

                <div x-show="useCustomNs" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @for($i = 1; $i <= 4; $i++)
                        <div>
                            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Nameserver {{ $i }}</label>
                            <input type="text" name="nameservers[]" placeholder="ns{{ $i }}.yournameserver.com" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-sm bg-white dark:bg-[#111827] text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-[#F6821F]">
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Total and Checkout Submit -->
        <div class="bg-white dark:bg-[#111827] p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Order Summary</span>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-3xl font-black text-slate-900 dark:text-white" x-text="window.formatPrice ? window.formatPrice(totalUsd) : '$' + totalUsd.toFixed(2)"></span>
                    <span class="text-xs font-bold text-slate-500 uppercase" x-text="window.getCurrentCurrency ? window.getCurrentCurrency() : 'USD'"></span>
                </div>
            </div>
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-[#F6821F] hover:bg-[#e07316] text-white font-bold rounded-xl shadow-lg shadow-orange-500/20 transition flex items-center justify-center gap-2">
                <span>Continue to Checkout</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>
    </form>
</div>
@endsection
