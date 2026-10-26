@extends('layouts.auth', ['title' => 'Sign In'])

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Welcome Back</h2>
    <p class="text-xs text-slate-500 mt-1">Sign in to your client or reseller account</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('email') border-rose-500 @enderror">
        @error('email')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1">
            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-indigo-600 hover:underline">Forgot password?</a>
        </div>
        <input id="password" type="password" name="password" required
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('password') border-rose-500 @enderror">
        @error('password')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between text-xs">
        <label class="flex items-center gap-2 cursor-pointer text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span>Remember me for 30 days</span>
        </label>
    </div>

    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-md shadow-indigo-600/20">
        Sign In to Portal
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
    Don't have an account yet?
    <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:underline">Create an account</a>
</div>
@endsection
