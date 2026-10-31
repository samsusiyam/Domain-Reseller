@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Two-Factor Authentication (2FA)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Protect administrative access with Google Authenticator, Authy, Microsoft Authenticator, or 1Password.</p>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold overflow-x-auto">
        <a href="{{ route('admin.profile.edit') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Profile, Password & Passkeys</a>
        <a href="{{ route('admin.two-factor.index') }}" class="px-3.5 py-2 text-[#F6821F] border-b-2 border-[#F6821F] font-bold">Two-Factor Authentication</a>
        <a href="{{ route('admin.sessions.index') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Active Sessions</a>
    </div>

    @if(session('recoveryCodes'))
        <div class="p-5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl space-y-3">
            <div>
                <h4 class="font-bold text-amber-900 dark:text-amber-300 text-xs">Save Your Emergency Backup Recovery Codes!</h4>
                <p class="text-[11px] text-amber-700 dark:text-amber-400">Store these 8 one-time emergency codes safely. If you lose your phone or authenticator app, these are required to sign into your administrator account.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-xs bg-white dark:bg-slate-900 p-3 rounded-lg border border-amber-200 dark:border-amber-800/60">
                @foreach(session('recoveryCodes') as $code)
                    <div class="select-all p-1.5 bg-slate-50 dark:bg-slate-800 text-center rounded border border-slate-200 dark:border-slate-700 font-semibold text-slate-800 dark:text-slate-200">{{ $code }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 shadow-xs">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Authenticator App Protection</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Time-based one-time password (TOTP RFC 6238 standard).</p>
            </div>
            @if($user->two_factor_confirmed_at)
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    Active & Enforced
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                    Not Activated
                </span>
            @endif
        </div>

        @if($user->two_factor_confirmed_at)
            <div class="space-y-4">
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Two-factor authentication is active on your administrator account. Every login will require a 6-digit code from your authenticator app.
                </p>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <form action="{{ route('admin.two-factor.disable') }}" method="POST" onsubmit="return confirm('Disable two-factor security for your admin account?');" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        @csrf
                        <input type="password" name="password" placeholder="Enter password to confirm" required
                               class="w-64 px-3 py-1.5 text-xs border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <button type="submit" class="h-8 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-xs transition cursor-pointer">
                            Disable 2FA Protection
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="space-y-6">
                <div class="flex flex-col md:flex-row items-center gap-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                    <!-- QR Code rendering via Google Charts API or fallback -->
                    <div class="shrink-0 p-2 bg-white rounded-lg border border-slate-200 shadow-xs">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($qrUrl) }}"
                             alt="Scan QR code with Authenticator app" class="w-36 h-36">
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Step 1: Scan QR or enter Secret</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Open Google Authenticator, Authy, or 1Password on your phone and scan the QR code to the left, or manually copy your setup secret:
                        </p>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold bg-white dark:bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 select-all text-slate-900 dark:text-white">
                                {{ $secret }}
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2">Step 2: Enter 6-digit confirmation code</h4>
                    <form action="{{ route('admin.two-factor.confirm') }}" method="POST" class="flex flex-col sm:flex-row gap-2 max-w-sm">
                        @csrf
                        <input type="text" name="code" maxlength="8" placeholder="123456" required autocomplete="one-time-code"
                               class="flex-1 px-3.5 py-2 text-xs font-mono text-center tracking-widest border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:outline-none">
                        <button type="submit" class="h-9 px-5 bg-[#F6821F] hover:bg-[#E07116] text-white text-xs font-semibold rounded-lg shadow-xs transition shrink-0 cursor-pointer">
                            Verify & Activate
                        </button>
                    </form>
                    @error('code')
                        <p class="text-[11px] text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
