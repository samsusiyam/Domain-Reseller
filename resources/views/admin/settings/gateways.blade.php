@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    activeTab: 'bkash_personal'
}">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Payment Gateways & Merchant APIs</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configure live payment processors: bKash (Personal & Merchant), SSLCommerz, PipraPay, UddoktaPay, Binance Pay (Personal & Merchant), Stripe, and Bank Wire.</p>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-medium">
        <a href="{{ route('admin.settings.general') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">General & Branding</a>
        <a href="{{ route('admin.settings.email') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Email & SMTP</a>
        <a href="{{ route('admin.settings.security') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Security & 2FA</a>
        <a href="{{ route('admin.settings.currencies') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Currencies & FX Rates</a>
        <a href="{{ route('admin.settings.gateways') }}" class="px-3.5 py-2 text-[#F6821F] border-b-2 border-[#F6821F] font-semibold">Payment Gateways</a>
    </div>

    <form action="{{ route('admin.settings.gateways.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Gateway Selector Sub-Tabs (Clean Pills) -->
        <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-medium">
            <button type="button" @click="activeTab = 'bkash_personal'"
                    :class="activeTab === 'bkash_personal' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                bKash Personal
            </button>
            <button type="button" @click="activeTab = 'uddoktapay'"
                    :class="activeTab === 'uddoktapay' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                UddoktaPay
            </button>
            <button type="button" @click="activeTab = 'binance_personal'"
                    :class="activeTab === 'binance_personal' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                Binance Personal (Pay ID)
            </button>
            <button type="button" @click="activeTab = 'binance_merchant'"
                    :class="activeTab === 'binance_merchant' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                Binance Merchant Pay
            </button>
            <button type="button" @click="activeTab = 'sslcommerz'"
                    :class="activeTab === 'sslcommerz' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                SSLCommerz
            </button>
            <button type="button" @click="activeTab = 'piprapay'"
                    :class="activeTab === 'piprapay' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                PipraPay
            </button>
            <button type="button" @click="activeTab = 'bkash_merchant'"
                    :class="activeTab === 'bkash_merchant' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                bKash Tokenized (Merchant)
            </button>
            <button type="button" @click="activeTab = 'stripe'"
                    :class="activeTab === 'stripe' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                Stripe Cards
            </button>
            <button type="button" @click="activeTab = 'bank'"
                    :class="activeTab === 'bank' ? 'bg-white dark:bg-slate-900 text-[#F6821F] font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                Bank Wire
            </button>
        </div>

        <!-- TAB 1: bKash Personal -->
        <div x-show="activeTab === 'bkash_personal'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">bKash Personal (Send Money)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Customers send money directly to your personal bKash number and submit the TrxID. Admin verifies in 1-click.</p>
                </div>
                <select name="bkash_personal_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['bkash_personal_enabled'] ?? 'true') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['bkash_personal_enabled'] ?? '') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">bKash Personal Mobile Number</label>
                <input type="text" name="bkash_personal_number" value="{{ $settings['bkash_personal_number'] ?? '01849693630' }}" class="cf-input h-9 text-xs font-mono font-bold" placeholder="01849693630">
                <p class="text-[11px] text-slate-400 mt-1">This number will be displayed on the checkout page for the customer to send money.</p>
            </div>
        </div>

        <!-- TAB 2: UddoktaPay -->
        <div x-show="activeTab === 'uddoktapay'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">UddoktaPay (Multi-MFS Aggregator)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Automated payment gateway connecting bKash, Nagad, Rocket, Upay, and Cards via UddoktaPay API v2.</p>
                </div>
                <select name="uddoktapay_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['uddoktapay_enabled'] ?? 'true') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['uddoktapay_enabled'] ?? '') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Base URL</label>
                    <input type="text" name="uddoktapay_api_url" value="{{ $settings['uddoktapay_api_url'] ?? 'https://pay.hostnibo.com/api' }}" class="cf-input h-9 text-xs font-mono" placeholder="https://pay.hostnibo.com/api">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key (RT-UDDOKTAPAY-API-KEY)</label>
                    <input type="password" name="uddoktapay_api_key" value="{{ $settings['uddoktapay_api_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Enter your UddoktaPay API key">
                </div>
            </div>
        </div>

        <!-- TAB 3: Binance Personal Account -->
        <div x-show="activeTab === 'binance_personal'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Binance Personal Account (Pay ID & Spot API)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Accept zero-fee crypto payments via your personal Binance Pay ID. Optional Spot API keys enable 100% automated TxID verification!</p>
                </div>
                <select name="binance_personal_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['binance_personal_enabled'] ?? 'true') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['binance_personal_enabled'] ?? '') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Your Binance Pay ID</label>
                    <input type="text" name="binance_personal_pay_id" value="{{ $settings['binance_personal_pay_id'] ?? '123456789' }}" class="cf-input h-9 text-xs font-mono font-bold" placeholder="e.g. 123456789">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Binance Registered Email (Optional)</label>
                    <input type="email" name="binance_personal_email" value="{{ $settings['binance_personal_email'] ?? 'payments@hostnibo.com' }}" class="cf-input h-9 text-xs font-mono" placeholder="payments@hostnibo.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">USDT Deposit Address (Optional fallback)</label>
                    <input type="text" name="binance_personal_usdt_address" value="{{ $settings['binance_personal_usdt_address'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Txxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Crypto Network</label>
                    <input type="text" name="binance_personal_network" value="{{ $settings['binance_personal_network'] ?? 'TRC20 / BEP20' }}" class="cf-input h-9 text-xs" placeholder="TRC20 / BEP20">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block mb-1">Automated Verification via Binance Spot API (Optional)</span>
                <p class="text-[11px] text-slate-400 mb-3">Add Read-Only Binance Account API Key & Secret to allow automatic verification of customer Pay ID orders and on-chain deposits without manual admin approval.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Binance Account API Key</label>
                        <input type="text" name="binance_personal_api_key" value="{{ $settings['binance_personal_api_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Binance API Key">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Binance Account API Secret</label>
                        <input type="password" name="binance_personal_secret_key" value="{{ $settings['binance_personal_secret_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Binance API Secret">
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Binance Pay Merchant -->
        <div x-show="activeTab === 'binance_merchant'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Binance Pay Merchant (Official OpenAPI v2)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Corporate Binance Pay integration with SHA512 signature, QR code, and mobile app universal redirect.</p>
                </div>
                <select name="binance_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['binance_enabled'] ?? 'true') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['binance_enabled'] ?? '') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Merchant ID</label>
                    <input type="text" name="binance_merchant_id" value="{{ $settings['binance_merchant_id'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="e.g. 192837465">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key (Certificate SN)</label>
                    <input type="text" name="binance_merchant_api_key" value="{{ $settings['binance_merchant_api_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Binance Pay API Key">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Secret Key</label>
                    <input type="password" name="binance_merchant_secret_key" value="{{ $settings['binance_merchant_secret_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Binance Pay Secret Key">
                </div>
            </div>
        </div>

        <!-- TAB 5: SSLCommerz -->
        <div x-show="activeTab === 'sslcommerz'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">SSLCommerz Hosted Checkout</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Cards, Mobile Banking (bKash/Nagad/Rocket), and Internet Banking gateway in Bangladesh.</p>
                </div>
                <select name="sslcommerz_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['sslcommerz_enabled'] ?? '') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['sslcommerz_enabled'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Environment Mode</label>
                    <select name="sslcommerz_mode" class="cf-input h-9 text-xs">
                        <option value="sandbox" {{ ($settings['sslcommerz_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                        <option value="live" {{ ($settings['sslcommerz_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live (Production)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Store ID</label>
                    <input type="text" name="sslcommerz_store_id" value="{{ $settings['sslcommerz_store_id'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Store ID">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Store Password</label>
                    <input type="password" name="sslcommerz_store_passwd" value="{{ $settings['sslcommerz_store_passwd'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Store Password">
                </div>
            </div>
        </div>

        <!-- TAB 6: PipraPay -->
        <div x-show="activeTab === 'piprapay'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">PipraPay API Gateway</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Automated multi-channel payment gateway supporting bKash, Nagad, and local channels.</p>
                </div>
                <select name="piprapay_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['piprapay_enabled'] ?? '') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['piprapay_enabled'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Base URL</label>
                    <input type="text" name="piprapay_api_url" value="{{ $settings['piprapay_api_url'] ?? 'https://piprapay.com/api' }}" class="cf-input h-9 text-xs font-mono" placeholder="https://piprapay.com/api">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">PipraPay API Key</label>
                    <input type="password" name="piprapay_api_key" value="{{ $settings['piprapay_api_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Bearer API Key">
                </div>
            </div>
        </div>

        <!-- TAB 7: bKash Tokenized (Merchant) -->
        <div x-show="activeTab === 'bkash_merchant'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">bKash Tokenized Checkout (Official Merchant)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Direct merchant tokenized checkout v1.2.0-beta via official bKash PGW credentials.</p>
                </div>
                <select name="bkash_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['bkash_enabled'] ?? '') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['bkash_enabled'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Mode</label>
                    <select name="bkash_mode" class="cf-input h-9 text-xs">
                        <option value="sandbox" {{ ($settings['bkash_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="live" {{ ($settings['bkash_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live (Production)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">bKash App Key</label>
                    <input type="text" name="bkash_app_key" value="{{ $settings['bkash_app_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="App Key">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">bKash App Secret</label>
                    <input type="password" name="bkash_app_secret" value="{{ $settings['bkash_app_secret'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="App Secret">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">bKash Merchant Username</label>
                    <input type="text" name="bkash_username" value="{{ $settings['bkash_username'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Username">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">bKash Merchant Password</label>
                    <input type="password" name="bkash_password" value="{{ $settings['bkash_password'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="Password">
                </div>
            </div>
        </div>

        <!-- TAB 8: Stripe Cards -->
        <div x-show="activeTab === 'stripe'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Credit / Debit Cards (Stripe)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Global credit/debit card processing via Stripe Checkout.</p>
                </div>
                <select name="stripe_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['stripe_enabled'] ?? '') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['stripe_enabled'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Stripe Publishable Key</label>
                    <input type="text" name="stripe_key" value="{{ $settings['stripe_key'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="pk_live_...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret" value="{{ $settings['stripe_secret'] ?? '' }}" class="cf-input h-9 text-xs font-mono" placeholder="sk_live_...">
                </div>
            </div>
        </div>

        <!-- TAB 9: Bank Wire / Manual -->
        <div x-show="activeTab === 'bank'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Bank Wire / Cash Deposit</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Corporate bank accounts for invoice settlements.</p>
                </div>
                <select name="bank_transfer_enabled" class="cf-input h-8 text-xs w-28">
                    <option value="true" {{ ($settings['bank_transfer_enabled'] ?? 'true') === 'true' ? 'selected' : '' }}>Enabled</option>
                    <option value="false" {{ ($settings['bank_transfer_enabled'] ?? '') === 'false' ? 'selected' : '' }}>Disabled</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? 'City Bank Ltd' }}" class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Account Holder Name</label>
                    <input type="text" name="bank_account_name" value="{{ $settings['bank_account_name'] ?? 'HostNibo Technologies Ltd' }}" class="cf-input h-9 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ $settings['bank_account_number'] ?? '1100223344001' }}" class="cf-input h-9 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Routing Number</label>
                    <input type="text" name="bank_routing_number" value="{{ $settings['bank_routing_number'] ?? '225272630' }}" class="cf-input h-9 text-xs font-mono">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="submit" class="cf-btn cf-btn-primary h-9 text-xs font-medium px-6 cursor-pointer">
                Save Gateway Credentials
            </button>
        </div>
    </form>
</div>
@endsection
