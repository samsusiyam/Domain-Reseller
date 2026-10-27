@extends('layouts.auth', ['title' => 'Sign in'])

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Sign in</h1>
    <p class="text-xs mt-1" style="color: var(--text-muted);">Access your domain portfolio, billing, or reseller console.</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPass: false }">
    @csrf

    <div>
        <label for="email" class="block text-xs font-medium mb-1.5" style="color: var(--text);">Email address</label>
        <input id="email"
               type="email"
               name="email"
               value="{{ old('email') }}"
               required
               autofocus
               autocomplete="username"
               placeholder="name@company.com"
               class="cf-input h-10 @error('email') border-[#DC2626] @enderror">
        @error('email')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-medium" style="color: var(--text);">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs hover:underline" style="color: var(--accent);">Forgot password?</a>
        </div>
        <div class="relative">
            <input id="password"
                   :type="showPass ? 'text' : 'password'"
                   name="password"
                   required
                   autocomplete="current-password"
                   placeholder="Enter your password"
                   class="cf-input h-10 pr-10 @error('password') border-[#DC2626] @enderror">
            <button type="button"
                    @click="showPass = !showPass"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs transition"
                    style="color: var(--text-muted);"
                    aria-label="Toggle password visibility">
                <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
            </button>
        </div>
        @error('password')
            <p class="text-xs mt-1 font-medium" style="color: var(--danger);">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center text-xs">
        <label class="flex items-center gap-2 cursor-pointer" style="color: var(--text-muted);">
            <input type="checkbox" name="remember" class="rounded-[4px] border-[1px] accent-[#F6821F]">
            <span>Remember me</span>
        </label>
    </div>

    <button type="submit"
            class="cf-btn cf-btn-primary w-full h-10 font-medium text-sm mt-2">
        Sign in
    </button>
</form>

<div class="mt-5 pt-4 text-center text-xs" style="border-top: 1px solid var(--border); color: var(--text-muted);">
    Don't have an account?
    <a href="{{ route('register') }}" class="font-medium hover:underline ml-1" style="color: var(--accent);">Create an account</a>
</div>
@endsection
