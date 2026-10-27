@extends('layouts.auth', ['title' => 'Create account'])

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Create an account</h1>
    <p class="text-xs mt-1" style="color: var(--text-muted);">Register to manage domains or join the wholesale partner program.</p>
</div>

<form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{
    role: '{{ old('role', 'client') }}',
    password: '',
    get strength() {
        let score = 0;
        if (this.password.length >= 10) score++;
        if (/[A-Z]/.test(this.password) && /[a-z]/.test(this.password)) score++;
        if (/[0-9]/.test(this.password)) score++;
        if (/[^A-Za-z0-9]/.test(this.password)) score++;
        return score;
    },
    get strengthLabel() {
        if (!this.password) return '';
        if (this.strength <= 1) return 'Weak (min 10 chars, letters, numbers, symbols)';
        if (this.strength <= 3) return 'Moderate';
        return 'Strong';
    },
    get strengthColor() {
        if (this.strength <= 1) return '#DC2626';
        if (this.strength <= 3) return '#D97706';
        return '#16A34A';
    }
}">
    @csrf

    <!-- Role Selection Segmented Control -->
    <div>
        <label class="block text-xs font-medium mb-1.5" style="color: var(--text);">Account type</label>
        <div class="grid grid-cols-2 gap-1 p-1 rounded-[6px]" style="background-color: var(--surface); border: 1px solid var(--border);">
            <button type="button"
                    @click="role = 'client'"
                    :class="role === 'client' ? 'font-medium shadow-sm' : ''"
                    :style="role === 'client' ? 'background-color: var(--bg); color: var(--text); border: 1px solid var(--border);' : 'color: var(--text-muted);'"
                    class="py-1.5 text-xs rounded-[4px] transition text-center">
                Client
            </button>
            <button type="button"
                    @click="role = 'reseller'"
                    :class="role === 'reseller' ? 'font-medium shadow-sm' : ''"
                    :style="role === 'reseller' ? 'background-color: var(--bg); color: var(--text); border: 1px solid var(--border);' : 'color: var(--text-muted);'"
                    class="py-1.5 text-xs rounded-[4px] transition text-center">
                Reseller (WHMCS/API)
            </button>
        </div>
        <input type="hidden" name="role" :value="role">
        <p x-show="role === 'reseller'" class="text-[11px] mt-1.5" style="color: var(--text-muted);">
            Reseller accounts undergo manual administrator verification before API activation.
        </p>
    </div>

    <div>
        <label for="name" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Full name</label>
        <input id="name"
               type="text"
               name="name"
               value="{{ old('name') }}"
               required
               autofocus
               placeholder="John Doe"
               class="cf-input h-10 @error('name') border-[#DC2626] @enderror">
        @error('name')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="role === 'reseller'" x-cloak>
        <label for="company_name" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Company or organization</label>
        <input id="company_name"
               type="text"
               name="company_name"
               value="{{ old('company_name') }}"
               placeholder="e.g. HostCloud Technologies"
               class="cf-input h-10">
    </div>

    <div>
        <label for="email" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Email address</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email') }}"
               required
               placeholder="name@company.com"
               class="cf-input h-10 @error('email') border-[#DC2626] @enderror">
        @error('email')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Password</label>
        <input id="password"
               type="password"
               name="password"
               x-model="password"
               required
               placeholder="Min 10 characters"
               class="cf-input h-10 @error('password') border-[#DC2626] @enderror">

        <!-- Password Strength Meter -->
        <div class="mt-2" x-show="password.length > 0">
            <div class="grid grid-cols-4 gap-1 h-1">
                <div class="rounded-[2px] transition-colors" :style="'background-color: ' + (strength >= 1 ? strengthColor : 'var(--border)')"></div>
                <div class="rounded-[2px] transition-colors" :style="'background-color: ' + (strength >= 2 ? strengthColor : 'var(--border)')"></div>
                <div class="rounded-[2px] transition-colors" :style="'background-color: ' + (strength >= 3 ? strengthColor : 'var(--border)')"></div>
                <div class="rounded-[2px] transition-colors" :style="'background-color: ' + (strength >= 4 ? strengthColor : 'var(--border)')"></div>
            </div>
            <p class="text-[11px] mt-1" :style="'color: ' + strengthColor" x-text="strengthLabel"></p>
        </div>
        @error('password')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Confirm password</label>
        <input id="password_confirmation"
               type="password"
               name="password_confirmation"
               required
               placeholder="Re-enter password"
               class="cf-input h-10">
    </div>

    <!-- Cloudflare Turnstile Bot Protection Placeholder -->
    <div class="p-2.5 rounded-[6px] flex items-center justify-between text-xs" style="background-color: var(--surface); border: 1px solid var(--border); color: var(--text-muted);">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#16A34A]" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Cloudflare Turnstile Verified</span>
        </div>
        <span class="text-[10px] font-mono">Managed</span>
    </div>

    <div class="pt-1">
        <label class="flex items-start gap-2 cursor-pointer text-xs" style="color: var(--text-muted);">
            <input type="checkbox" name="terms" required class="mt-0.5 rounded-[4px] border-[1px] accent-[#F6821F]">
            <span>I agree to the <a href="{{ route('terms') }}" target="_blank" class="hover:underline font-medium" style="color: var(--accent);">Terms of service</a> and ICANN WHOIS accuracy guidelines.</span>
        </label>
    </div>

    <button type="submit"
            class="cf-btn cf-btn-primary w-full h-10 font-medium text-sm mt-2">
        Create account
    </button>
</form>

<div class="mt-5 pt-4 text-center text-xs" style="border-top: 1px solid var(--border); color: var(--text-muted);">
    Already have an account?
    <a href="{{ route('login') }}" class="font-medium hover:underline ml-1" style="color: var(--accent);">Sign in</a>
</div>
@endsection
