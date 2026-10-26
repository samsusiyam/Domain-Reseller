@extends('layouts.reseller')

@section('content')
<div class="max-w-3xl space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-800">White-Label Branding Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Customize the default nameservers, support contact info, and company name shown to your end-customers.</p>
    </div>

    <!-- Whitelabel Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('reseller.whitelabel.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Company / Brand Name</label>
                <input type="text" name="brand_name" required
                    class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                    value="{{ old('brand_name', $settings['brand_name']) }}">
                <p class="text-xs text-slate-400 mt-1">Displayed in automated system communications and default WHOIS attributes.</p>
                @error('brand_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Support Email Address</label>
                <input type="email" name="support_email" required
                    class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                    value="{{ old('support_email', $settings['support_email']) }}">
                <p class="text-xs text-slate-400 mt-1">Contact address where your end-clients reach you for technical or billing inquiries.</p>
                @error('support_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-bold text-slate-800 mb-3">Custom Default Nameservers</h4>
                <p class="text-xs text-slate-500 mb-4">When new domains are registered via your WHMCS or API without explicit nameservers, these defaults will be applied automatically.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Primary Nameserver (NS1)</label>
                        <input type="text" name="ns1" required placeholder="ns1.yourbrand.com"
                            class="w-full px-4 py-2.5 font-mono text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                            value="{{ old('ns1', $settings['ns1']) }}">
                        @error('ns1') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Secondary Nameserver (NS2)</label>
                        <input type="text" name="ns2" required placeholder="ns2.yourbrand.com"
                            class="w-full px-4 py-2.5 font-mono text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                            value="{{ old('ns2', $settings['ns2']) }}">
                        @error('ns2') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Save White-Label Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
