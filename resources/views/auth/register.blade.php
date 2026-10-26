@extends('layouts.auth', ['title' => 'Create Account'])

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-extrabold text-white tracking-tight">Create an Account</h2>
    <p class="text-xs text-slate-400 mt-1">Join as a direct client or wholesale domain reseller</p>
</div>

<form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ role: 'client' }">
    @csrf

    <!-- Role Selection Tabs -->
    <div>
        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Select Account Role</label>
        <div class="grid grid-cols-2 gap-2 p-1 bg-obsidian-900 border border-white/10 rounded-2xl">
            <button type="button" @click="role = 'client'"
                :class="role === 'client' ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-sm font-bold' : 'text-slate-400 hover:text-white font-medium'"
                class="py-2.5 text-xs rounded-xl transition text-center">
                Direct Client
            </button>
            <button type="button" @click="role = 'reseller'"
                :class="role === 'reseller' ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-sm font-bold' : 'text-slate-400 hover:text-white font-medium'"
                class="py-2.5 text-xs rounded-xl transition text-center">
                Domain Reseller (WHMCS/API)
            </button>
        </div>
        <input type="hidden" name="role" :value="role">
    </div>

    <div>
        <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Full Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 @error('name') border-rose-500 @enderror">
        @error('name')
            <p class="text-xs text-rose-400 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="role === 'reseller'" x-cloak>
        <label for="company_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Company / Hosting Brand Name</label>
        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500" placeholder="e.g. HostCloud BD">
    </div>

    <div>
        <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 @error('email') border-rose-500 @enderror">
        @error('email')
            <p class="text-xs text-rose-400 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
            <input id="password" type="password" name="password" required
                class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 @error('password') border-rose-500 @enderror">
        </div>
        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500">
        </div>
    </div>
    @error('password')
        <p class="text-xs text-rose-400 font-medium">{{ $message }}</p>
    @enderror

    <div class="pt-2">
        <label class="flex items-start gap-2.5 cursor-pointer text-xs text-slate-400 leading-normal">
            <input type="checkbox" name="terms" required class="mt-0.5 rounded bg-obsidian-900 border-white/20 text-indigo-600 focus:ring-indigo-500">
            <span>I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-indigo-400 hover:underline">Terms of Service</a> and ICANN WHOIS accuracy requirements.</span>
        </label>
    </div>

    <button type="submit"
        class="w-full py-3.5 bg-gradient-to-r from-indigo-500 via-indigo-600 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-indigo-600/30 mt-2">
        Complete Registration →
    </button>
</form>

<div class="mt-6 pt-6 border-t border-white/10 text-center text-xs text-slate-400">
    Already have an account?
    <a href="{{ route('login') }}" class="font-bold text-indigo-400 hover:text-indigo-300 ml-1">Sign in here</a>
</div>
@endsection
