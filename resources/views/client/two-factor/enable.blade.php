@extends('layouts.app', ['title' => 'Enable 2FA', 'header' => 'Setup Two-Factor Authentication'])

@section('content')
<div class="max-w-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 shadow-sm space-y-6">
    <div>
        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Scan QR Code with Authenticator App</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
            Open Google Authenticator, Microsoft Authenticator, or Authy on your mobile device, scan the code or enter the secret key manually.
        </p>
    </div>

    <!-- QR Code / Secret Key Display -->
    <div class="p-6 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center gap-6">
        <div class="w-40 h-40 bg-white p-2 rounded-xl border border-slate-300 flex items-center justify-center shadow-inner">
            <!-- Render QR Code using online service or SVG -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrUrl) }}" alt="TOTP QR Code" class="w-full h-full object-contain">
        </div>
        <div class="flex-1 space-y-2 text-center sm:text-left">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Manual Secret Key</span>
            <div class="p-2.5 bg-white font-mono text-sm font-bold text-[#F6821F] rounded-xl border border-slate-200 dark:border-slate-700 select-all tracking-wider text-center sm:text-left">
                {{ $secret }}
            </div>
            <p class="text-[11px] text-slate-500">
                Type: Time-based (TOTP) • 6 Digits • 30s interval
            </p>
        </div>
    </div>

    <!-- Confirmation Form -->
    <form action="{{ route('client.two-factor.confirm') }}" method="POST" class="space-y-4 pt-4 border-t border-slate-100">
        @csrf

        <div>
            <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Enter 6-digit Code to Confirm</label>
            <input type="text" name="code" id="code" required autofocus maxlength="8" placeholder="123456"
                class="w-full sm:w-64 px-4 py-3 text-center font-mono tracking-widest text-lg border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#F6821F] focus:outline-none @error('code') border-rose-500 @enderror">
            @error('code')
                <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-[#F6821F] hover:bg-[#E07116] text-white font-bold text-sm rounded-xl transition shadow">
                Activate 2FA Protection
            </button>
            <a href="{{ route('client.two-factor.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
