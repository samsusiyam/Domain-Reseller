@extends('layouts.app', ['title' => 'Register Domain', 'header' => 'Configure Registration'])

@section('content')
<div class="max-w-4xl" x-data="{
    years: 1,
    idProtection: true,
    dnsManagement: true,
    emailForwarding: false,
    useCustomNs: false,
    prices: {{ json_encode($prices) }},
    get totalUsd() {
        let base = Number(this.prices[this.years]?.total_usd || 0);
        return base;
    }
}">
    <form action="{{ route('client.domains.register.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="domain" value="{{ $domain }}">

        <!-- Domain & Period Card -->
        <div class="bg-white dark:bg-[#111827] p-6 sm:p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Domain To Register</span>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $domain }}</h2>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Registration Term</span>
                    <select name="years" x-model="years" class="mt-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-bold bg-white dark:bg-[#111827] focus:ring-2 focus:ring-[#F6821F]">
                        @for($y = 1; $y <= 10; $y++)
                            <option value="{{ $y }}">{{ $y }} {{ $y === 1 ? 'Year' : 'Years' }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <!-- Addons -->
            <div class="pt-6 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Available Addons</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-start gap-3 cursor-pointer hover:border-[#F6821F] transition" :class="idProtection ? 'border-[#F6821F] bg-orange-50 dark:bg-orange-950/30/30' : ''">
                        <input type="checkbox" name="id_protection" value="1" x-model="idProtection" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">ID Protection</span>
                            <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">Protect your personal WHOIS data from spam</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-start gap-3 cursor-pointer hover:border-[#F6821F] transition" :class="dnsManagement ? 'border-[#F6821F] bg-orange-50 dark:bg-orange-950/30/30' : ''">
                        <input type="checkbox" name="dns_management" value="1" x-model="dnsManagement" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">DNS Management</span>
                            <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">Hosted cloud DNS zone editor</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-start gap-3 cursor-pointer hover:border-[#F6821F] transition" :class="emailForwarding ? 'border-[#F6821F] bg-orange-50 dark:bg-orange-950/30/30' : ''">
                        <input type="checkbox" name="email_forwarding" value="1" x-model="emailForwarding" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">Email Forwarding</span>
                            <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">Forward emails to Gmail / Outlook</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Nameservers Setup -->
            <div class="pt-6 mt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Authoritative Nameservers</h3>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-[#F6821F]">
                        <input type="checkbox" x-model="useCustomNs" class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        <span>Use Custom Nameservers</span>
                    </label>
                </div>

                <div x-show="!useCustomNs" class="p-3 bg-slate-50 rounded-xl border border-slate-200 dark:border-slate-800 text-xs text-slate-600 font-mono">
                    Default Nameservers: {{ config('dns_templates.default_nameservers.ns1', 'ns1.domainreseller.com') }} / {{ config('dns_templates.default_nameservers.ns2', 'ns2.domainreseller.com') }}
                </div>

                <div x-show="useCustomNs" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <input type="text" name="ns1" placeholder="Nameserver 1 (e.g. ns1.example.com)" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                    <input type="text" name="ns2" placeholder="Nameserver 2 (e.g. ns2.example.com)" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                    <input type="text" name="ns3" placeholder="Nameserver 3 (optional)" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                    <input type="text" name="ns4" placeholder="Nameserver 4 (optional)" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-mono focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Payment Method & Checkout -->
        <div class="bg-white dark:bg-[#111827] p-6 sm:p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Select Payment Method</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <!-- Wallet -->
                <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-[#F6821F] transition flex items-start gap-3">
                    <input type="radio" name="payment_method" value="wallet" checked class="text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Account Credit Wallet</span>
                        <span class="text-[11px] text-slate-500 block">Balance: ${{ number_format((float)auth()->user()->balance, 2) }}</span>
                    </div>
                </label>

                <!-- bKash -->
                <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-[#F6821F] transition flex items-start gap-3">
                    <input type="radio" name="payment_method" value="bkash" class="text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">bKash Online</span>
                        <span class="text-[11px] text-slate-500 block">Live BDT checkout</span>
                    </div>
                </label>

                <!-- Stripe -->
                <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-[#F6821F] transition flex items-start gap-3">
                    <input type="radio" name="payment_method" value="stripe" class="text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Credit / Debit Card</span>
                        <span class="text-[11px] text-slate-500 block">Visa, Mastercard, Amex</span>
                    </div>
                </label>

                <!-- Binance Pay -->
                <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-[#F6821F] transition flex items-start gap-3">
                    <input type="radio" name="payment_method" value="binance" class="text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Binance Pay (Crypto)</span>
                        <span class="text-[11px] text-slate-500 block">Instant USDT/BUSD</span>
                    </div>
                </label>

                <!-- Manual -->
                <label class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-[#F6821F] transition flex items-start gap-3">
                    <input type="radio" name="payment_method" value="manual" class="text-[#F6821F] focus:ring-[#F6821F] mt-0.5">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Manual Payment</span>
                        <span class="text-[11px] text-slate-500 block">Bank transfer / Nagad</span>
                    </div>
                </label>
            </div>

            <!-- Promo Code Input -->
            <div class="pt-4 border-t border-slate-100 flex items-center gap-3 max-w-sm">
                <input type="text" name="promo_code" placeholder="Promo code (optional)" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs uppercase focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
            </div>

            <!-- Summary & Submit -->
            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-slate-500 block">Total Due:</span>
                    <span class="text-2xl font-black text-[#F6821F]" x-text="'$' + totalUsd.toFixed(2)"></span>
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow">
                    Complete Order & Pay
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
