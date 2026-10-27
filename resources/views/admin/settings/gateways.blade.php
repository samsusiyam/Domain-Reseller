@extends('layouts.admin', ['title' => 'Payment Gateway Settings'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Payment Gateways</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Configure credentials and operational status for bKash, Stripe, Binance, and Bank transfer.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3.5 rounded-[6px] text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.settings.gateways.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- BKASH -->
        <div class="cf-panel p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-500/10 text-pink-600 flex items-center justify-center font-bold text-xs">bK</div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">bKash Payment Gateway (Checkout URL)</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Accept BDT wallet payments directly using tokenized bKash API v1.2.0-beta.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="bkash_enabled" value="1" {{ filter_var($settings['bkash_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F6821F]"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Mode</label>
                    <select name="bkash_mode" class="cf-input h-9 w-full">
                        <option value="sandbox" {{ ($settings['bkash_mode'] ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox / Testing</option>
                        <option value="live" {{ ($settings['bkash_mode'] ?? '') === 'live' ? 'selected' : '' }}>Production / Live</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">bKash Username</label>
                    <input type="text" name="bkash_username" value="{{ $settings['bkash_username'] ?? '' }}" class="cf-input h-9 w-full">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">App Key</label>
                    <input type="text" name="bkash_app_key" value="{{ $settings['bkash_app_key'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">App Secret</label>
                    <input type="password" name="bkash_app_secret" value="{{ $settings['bkash_app_secret'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">bKash Password</label>
                    <input type="password" name="bkash_password" value="{{ $settings['bkash_password'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
            </div>
        </div>

        <!-- STRIPE -->
        <div class="cf-panel p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold text-xs">St</div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Stripe (Credit / Debit Cards)</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Global card processing in USD for international clients and resellers.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="stripe_enabled" value="1" {{ filter_var($settings['stripe_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F6821F]"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Stripe Publishable Key</label>
                    <input type="text" name="stripe_key" value="{{ $settings['stripe_key'] ?? '' }}" placeholder="pk_live_..." class="cf-input h-9 w-full font-mono">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret" value="{{ $settings['stripe_secret'] ?? '' }}" placeholder="sk_live_..." class="cf-input h-9 w-full font-mono">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Webhook Signing Secret</label>
                    <input type="password" name="stripe_webhook_secret" value="{{ $settings['stripe_webhook_secret'] ?? '' }}" placeholder="whsec_..." class="cf-input h-9 w-full font-mono">
                </div>
            </div>
        </div>

        <!-- BINANCE PAY -->
        <div class="cf-panel p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-xs">Bn</div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Binance Pay (Crypto)</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Direct zero-fee crypto payments via USDT and cryptocurrency balances.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="binance_enabled" value="1" {{ filter_var($settings['binance_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F6821F]"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Merchant ID</label>
                    <input type="text" name="binance_merchant_id" value="{{ $settings['binance_merchant_id'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">API Key</label>
                    <input type="password" name="binance_api_key" value="{{ $settings['binance_api_key'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Secret Key</label>
                    <input type="password" name="binance_secret_key" value="{{ $settings['binance_secret_key'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
            </div>
        </div>

        <!-- BANK TRANSFER / MANUAL -->
        <div class="cf-panel p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-xs">Bk</div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Bank Transfer / Manual Slip Deposit</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Accept local bank wire deposits verified manually from the Manual Payments queue.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="bank_transfer_enabled" value="1" {{ filter_var($settings['bank_transfer_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#F6821F]"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? '' }}" class="cf-input h-9 w-full">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Account Title / Name</label>
                    <input type="text" name="bank_account_name" value="{{ $settings['bank_account_name'] ?? '' }}" class="cf-input h-9 w-full">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ $settings['bank_account_number'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Routing / Swift Code</label>
                    <input type="text" name="bank_routing_number" value="{{ $settings['bank_routing_number'] ?? '' }}" class="cf-input h-9 w-full font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Instructions for Customer</label>
                    <textarea name="bank_instructions" rows="3" class="cf-textarea w-full text-xs">{{ $settings['bank_instructions'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="cf-btn cf-btn-primary h-9 px-6 text-xs font-medium">
                Save Gateway Settings
            </button>
        </div>
    </form>

</div>
@endsection
