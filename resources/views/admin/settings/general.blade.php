@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">General & Branding Settings</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configure platform branding logos (light & dark mode), contact info, and base currency.</p>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-medium">
        <a href="{{ route('admin.settings.general') }}" class="px-3.5 py-2 text-[#F6821F] border-b-2 border-[#F6821F] font-semibold">General & Branding</a>
        <a href="{{ route('admin.settings.email') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Email & SMTP</a>
        <a href="{{ route('admin.settings.security') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Security & 2FA</a>
        <a href="{{ route('admin.settings.currencies') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Currencies & FX Rates</a>
        <a href="{{ route('admin.settings.gateways') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Payment Gateways</a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- SECTION: Brand Logos (Dark & Light Mode) -->
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Brand Logos & Identity</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Upload custom SVG or PNG logos for light and dark themes. These will appear in the Header, Footer, Admin, and Client areas.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Light Mode Logo -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-800/30">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Light Theme Logo (Dark text on light background)
                        </label>
                        @php $logoLight = \App\Models\SystemSetting::get('logo_light'); @endphp
                        <div class="h-16 flex items-center justify-center bg-white rounded-lg border border-slate-200 mb-3 px-4">
                            @if($logoLight)
                                <img src="{{ asset($logoLight) }}" alt="Light Mode Logo" class="max-h-12 w-auto object-contain">
                            @else
                                <span class="text-xs text-slate-400 italic">No logo uploaded (using default fallback)</span>
                            @endif
                        </div>
                        <input type="file" name="logo_light" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-[#F6821F] hover:file:bg-orange-100 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-1">Recommended: Transparent PNG or SVG (~250x60px)</p>
                    </div>

                    <!-- Dark Mode Logo -->
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-800/30">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Dark Theme Logo (White/light text on dark background)
                        </label>
                        @php $logoDark = \App\Models\SystemSetting::get('logo_dark'); @endphp
                        <div class="h-16 flex items-center justify-center bg-slate-950 rounded-lg border border-slate-800 mb-3 px-4">
                            @if($logoDark)
                                <img src="{{ asset($logoDark) }}" alt="Dark Mode Logo" class="max-h-12 w-auto object-contain">
                            @else
                                <span class="text-xs text-slate-500 italic">No logo uploaded (using default fallback)</span>
                            @endif
                        </div>
                        <input type="file" name="logo_dark" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-orange-50 file:text-[#F6821F] hover:file:bg-orange-100 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-1">Recommended: White/light transparent PNG or SVG</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-800 pt-5">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Platform Information</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Company / Brand Name</label>
                        <input type="text" name="company_name" required class="cf-input h-9 text-xs"
                            value="{{ old('company_name', $settings['company_name'] ?? config('app.name')) }}">
                        @error('company_name') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Portal Page Title</label>
                        <input type="text" name="portal_title" class="cf-input h-9 text-xs"
                            value="{{ old('portal_title', $settings['portal_title'] ?? 'Domain Reseller & Cloud Management') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Official Support Email</label>
                        <input type="email" name="support_email" required class="cf-input h-9 text-xs"
                            value="{{ old('support_email', $settings['support_email'] ?? 'support@hostnibo.com') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Support Phone / WhatsApp</label>
                        <input type="text" name="support_phone" class="cf-input h-9 text-xs"
                            value="{{ old('support_phone', $settings['support_phone'] ?? '+880 1849-693630') }}">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Base Ledger Currency</label>
                        <select name="currency_base" class="cf-input h-9 text-xs">
                            <option value="USD" {{ ($settings['currency_base'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD — US Dollar ($)</option>
                            <option value="BDT" {{ ($settings['currency_base'] ?? '') === 'BDT' ? 'selected' : '' }}>BDT — Bangladeshi Taka (৳)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">VAT / Tax Percentage (%)</label>
                        <input type="number" step="0.01" name="tax_percentage" class="cf-input h-9 text-xs"
                            value="{{ old('tax_percentage', $settings['tax_percentage'] ?? 0) }}">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Footer Brand Statement</label>
                    <textarea name="footer_text" rows="2" class="cf-input text-xs py-2">{{ old('footer_text', $settings['footer_text'] ?? 'Wholesale domain registration platform connecting upstream registries with dual USD and BDT billing.') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="submit" class="cf-btn cf-btn-primary h-9 text-xs font-medium px-5 cursor-pointer">
                    Save Branding & Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
