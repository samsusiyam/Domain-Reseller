@extends('layouts.app', ['title' => 'Transfer Domain', 'header' => 'Transfer In a Domain'])

@section('content')
<div class="max-w-3xl">
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900 mb-1">Transfer Domain to Our Management</h2>
        <p class="text-xs text-slate-500 mb-6">
            Keep your domain active and renew for an additional 1 year at our wholesale transfer rates. Please ensure the domain is unlocked at your current registrar.
        </p>

        <form action="{{ route('client.domains.transfer.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="domain" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Domain Name</label>
                <input type="text" id="domain" name="domain" value="{{ old('domain', $domain) }}" required placeholder="example.com"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label for="epp_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">EPP / Authorization Code</label>
                <input type="text" id="epp_code" name="epp_code" value="{{ old('epp_code') }}" required placeholder="Obtained from current registrar"
                    class="w-full px-4 py-2.5 font-mono border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="id_protection" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Enable Free WHOIS ID Protection upon transfer</span>
                </label>
            </div>

            <!-- Payment method selection -->
            <div class="pt-4 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Method</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="p-3 rounded-xl border border-slate-200 cursor-pointer flex items-center gap-2 text-xs font-bold text-slate-800">
                        <input type="radio" name="payment_method" value="wallet" checked class="text-indigo-600 focus:ring-indigo-500">
                        <span>Credit Wallet</span>
                    </label>
                    <label class="p-3 rounded-xl border border-slate-200 cursor-pointer flex items-center gap-2 text-xs font-bold text-slate-800">
                        <input type="radio" name="payment_method" value="bkash" class="text-indigo-600 focus:ring-indigo-500">
                        <span>bKash Online</span>
                    </label>
                    <label class="p-3 rounded-xl border border-slate-200 cursor-pointer flex items-center gap-2 text-xs font-bold text-slate-800">
                        <input type="radio" name="payment_method" value="stripe" class="text-indigo-600 focus:ring-indigo-500">
                        <span>Credit Card</span>
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <div>
                    @if($price)
                        <span class="text-xs text-slate-500 block">Transfer Rate (1 Year):</span>
                        <span class="text-xl font-black text-indigo-600">${{ number_format($price['total_usd'], 2) }}</span>
                    @endif
                </div>

                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
                    Initiate Transfer &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
