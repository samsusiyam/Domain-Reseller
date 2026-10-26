@extends('layouts.auth', ['title' => 'Sign In'])

@section('content')
<div class="mb-6 text-center" x-data="{
    fillDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }
}">
    <h2 class="text-2xl font-extrabold text-white tracking-tight">Welcome Back</h2>
    <p class="text-xs text-slate-400 mt-1">Sign in to your client, reseller, or administrator portal</p>

    <!-- Quick Demo Credential Pills -->
    <div class="mt-4 p-3 rounded-2xl bg-white/5 border border-white/10 text-left">
        <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 font-bold block mb-1.5">Quick Demo Fill (Pass: password123):</span>
        <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" @click="fillDemo('admin@domainreseller.local')"
                class="px-2 py-1 rounded bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 font-mono text-[10px] font-bold border border-indigo-500/30 transition">
                ⚡ Admin
            </button>
            <button type="button" @click="fillDemo('reseller@domainreseller.local')"
                class="px-2 py-1 rounded bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 font-mono text-[10px] font-bold border border-purple-500/30 transition">
                ⚡ Reseller
            </button>
            <button type="button" @click="fillDemo('client@domainreseller.local')"
                class="px-2 py-1 rounded bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-mono text-[10px] font-bold border border-emerald-500/30 transition">
                ⚡ Client
            </button>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 @error('email') border-rose-500 @enderror">
        @error('email')
            <p class="text-xs text-rose-400 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Forgot password?</a>
        </div>
        <input id="password" type="password" name="password" required
            class="w-full px-4 py-3 bg-obsidian-900 border border-white/15 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 @error('password') border-rose-500 @enderror">
        @error('password')
            <p class="text-xs text-rose-400 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between text-xs pt-1">
        <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-200">
            <input type="checkbox" name="remember" class="rounded bg-obsidian-900 border-white/20 text-indigo-600 focus:ring-indigo-500">
            <span>Remember me on this device</span>
        </label>
    </div>

    <button type="submit"
        class="w-full py-3.5 bg-gradient-to-r from-indigo-500 via-indigo-600 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-indigo-600/30 mt-2">
        Sign In to Portal →
    </button>
</form>

<div class="mt-6 pt-6 border-t border-white/10 text-center text-xs text-slate-400">
    Don't have an account yet?
    <a href="{{ route('register') }}" class="font-bold text-indigo-400 hover:text-indigo-300 ml-1">Create an account</a>
</div>
@endsection
