@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Bangladesh SMS Gateway & Telemetry</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configure local SMS providers (Greenweb, BulkSMSBD, AlphaSMS) for order alerts, bKash receipts, and expiry warnings.</p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Live Balance: <strong class="ml-1 text-[#F6821F]">{{ $balance }}</strong>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Form (2 cols) -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                <form action="{{ route('admin.settings.sms.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- Enable Switch -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="text-sm font-semibold text-slate-900 dark:text-white">Enable Automated SMS Service</label>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Trigger real-time transaction and domain lifecycle alerts to clients.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="sms_enabled" value="1" {{ $settings['sms_enabled'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#F6821F]"></div>
                        </label>
                    </div>

                    <!-- Provider -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">SMS Gateway Provider</label>
                        <select name="sms_provider" class="cf-select h-9 text-xs">
                            <option value="greenweb" {{ $settings['sms_provider'] === 'greenweb' ? 'selected' : '' }}>Greenweb Bangladesh (api.greenweb.com.bd)</option>
                            <option value="bulksmsbd" {{ $settings['sms_provider'] === 'bulksmsbd' ? 'selected' : '' }}>BulkSMSBD (bulksmsbd.net)</option>
                            <option value="alphasms" {{ $settings['sms_provider'] === 'alphasms' ? 'selected' : '' }}>Alpha SMS / SMS.net.bd</option>
                        </select>
                    </div>

                    <!-- API Key -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">API Key / Token</label>
                        <input type="password" name="sms_api_key" value="{{ $settings['sms_api_key'] }}" placeholder="Enter Gateway API Key or Token" class="cf-input h-9 text-xs font-mono">
                    </div>

                    <!-- Sender ID -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Approved Sender ID (Masking / Non-Masking)</label>
                        <input type="text" name="sms_sender_id" value="{{ $settings['sms_sender_id'] }}" placeholder="e.g. 8809612xxxxxx or HostNibo" class="cf-input h-9 text-xs">
                    </div>

                    <!-- Triggers -->
                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-slate-900 dark:text-white mb-2">Automated Notification Triggers</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="notify_invoice" value="1" {{ $settings['notify_invoice'] ? 'checked' : '' }} class="accent-[#F6821F] rounded">
                                <span>Send SMS on successful bKash / payment receipt</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="notify_domain" value="1" {{ $settings['notify_domain'] ? 'checked' : '' }} class="accent-[#F6821F] rounded">
                                <span>Send SMS when a domain is successfully registered</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="notify_expiry" value="1" {{ $settings['notify_expiry'] ? 'checked' : '' }} class="accent-[#F6821F] rounded">
                                <span>Send SMS 7 days before domain expiration</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                            Save SMS Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Send Test SMS (1 col) -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Send Test SMS</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 mb-4">Verify your API credentials and sender routing with a live test message.</p>

                <form action="{{ route('admin.settings.sms.test') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Recipient Number</label>
                        <input type="text" name="phone" placeholder="017xxxxxxxx" required class="cf-input h-9 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Message Text</label>
                        <textarea name="message" rows="3" required class="cf-textarea text-xs">HostNibo: Your test SMS configuration is active and working!</textarea>
                    </div>
                    <button type="submit" class="cf-btn cf-btn-secondary w-full text-xs font-medium cursor-pointer">
                        Dispatch Test SMS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
