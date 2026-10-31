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
        if (this.password.length >= 8) score++;
        if (/[A-Z]/.test(this.password) && /[a-z]/.test(this.password)) score++;
        if (/[0-9]/.test(this.password)) score++;
        if (/[^A-Za-z0-9]/.test(this.password)) score++;
        return score;
    },
    get strengthLabel() {
        if (!this.password) return '';
        if (this.strength <= 1) return 'Weak (min 8 characters)';
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

    @if(request('next'))
        <input type="hidden" name="next" value="{{ request('next') }}">
    @endif

    <!-- Role Selection Segmented Control -->
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text);">Account Type</label>
        <div class="grid grid-cols-2 gap-1.5 p-1.5 rounded-lg" style="background-color: var(--surface); border: 1px solid var(--border);">
            <button type="button"
                    @click="role = 'client'"
                    :class="role === 'client' 
                        ? 'bg-[#F6821F] text-white font-semibold shadow-md ring-1 ring-[#F6821F]' 
                        : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="py-2.5 px-3 text-xs rounded-md transition-all duration-200 flex items-center justify-center gap-1.5 text-center cursor-pointer">
                <template x-if="role === 'client'">
                    <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </template>
                <span>Client (Personal / Business)</span>
            </button>
            <button type="button"
                    @click="role = 'reseller'"
                    :class="role === 'reseller' 
                        ? 'bg-[#F6821F] text-white font-semibold shadow-md ring-1 ring-[#F6821F]' 
                        : 'text-gray-400 hover:text-white hover:bg-white/5'"
                    class="py-2.5 px-3 text-xs rounded-md transition-all duration-200 flex items-center justify-center gap-1.5 text-center cursor-pointer">
                <template x-if="role === 'reseller'">
                    <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </template>
                <span>Reseller (WHMCS / API)</span>
            </button>
        </div>
        <input type="hidden" name="role" :value="role">

        <div class="mt-2 text-[11px] p-2.5 rounded-md flex items-start gap-2" style="background-color: var(--bg); border: 1px solid var(--border);">
            <svg class="w-4 h-4 text-[#F6821F] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <span x-show="role === 'client'" style="color: var(--text-muted);">
                Instant domain registration, DNS management, WHOIS privacy & nameserver control.
            </span>
            <span x-show="role === 'reseller'" x-cloak style="color: var(--text-muted);">
                Automated WHMCS registrar module, REST API keys, wholesale discount tiers & webhook events.
            </span>
        </div>
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
               placeholder="Min 8 characters"
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
    <a href="{{ route('login', request('next') ? ['next' => request('next')] : []) }}" class="font-medium hover:underline ml-1" style="color: var(--accent);">Sign in</a>
</div>
@endsection
