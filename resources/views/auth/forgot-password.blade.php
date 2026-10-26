@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
<div class="mb-6 text-center">
    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Forgot Password</h2>
    <p class="text-xs text-slate-500 mt-1">Enter your email and we'll send a password reset link</p>
</div>

@if (session('status'))
    <div class="mb-4">
        <x-alert type="success" :message="session('status')" />
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('email') border-rose-500 @enderror">
        @error('email')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow-md shadow-indigo-600/20">
        Send Reset Link
    </button>
</form>

<div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
    Remember your password?
    <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:underline">Back to login</a>
</div>
@endsection
