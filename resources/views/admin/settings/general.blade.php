@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">General System Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5">Configure platform branding, support contact details, and base billing policies.</p>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold">
        <a href="{{ route('admin.settings.general') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600">General Settings</a>
        <a href="{{ route('admin.settings.email') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800">Email & SMTP</a>
        <a href="{{ route('admin.settings.security') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800">Security & 2FA</a>
        <a href="{{ route('admin.settings.currencies') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800">Currencies & FX Rates</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.settings.general.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Company / Brand Name</label>
                    <input type="text" name="company_name" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold"
                        value="{{ old('company_name', $settings['company_name'] ?? config('app.name')) }}">
                    @error('company_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Portal Page Title</label>
                    <input type="text" name="portal_title" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        value="{{ old('portal_title', $settings['portal_title'] ?? 'Domain Reseller & Cloud Management') }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Support Email Address</label>
                    <input type="email" name="support_email" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        value="{{ old('support_email', $settings['support_email'] ?? 'support@domainreseller.com') }}">
                    @error('support_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Support Phone Hotline</label>
                    <input type="text" name="support_phone" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        value="{{ old('support_phone', $settings['support_phone'] ?? '+880 1700-000000') }}">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Base Ledger Currency</label>
                    <input type="text" name="currency_base" required readonly class="w-full px-3 py-2 text-sm font-mono font-bold bg-slate-50 border border-slate-300 rounded-lg text-slate-600"
                        value="{{ old('currency_base', $settings['currency_base'] ?? 'USD') }}">
                    <p class="text-[11px] text-slate-400 mt-1">Platform core ledger is strictly indexed in USD.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Default VAT / Tax Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_percentage" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"
                        value="{{ old('tax_percentage', $settings['tax_percentage'] ?? '0.00') }}">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Portal Footer Legal Text</label>
                    <input type="text" name="footer_text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg"
                        value="{{ old('footer_text', $settings['footer_text'] ?? '© ' . date('Y') . ' DomainReseller Inc. All rights reserved.') }}">
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
