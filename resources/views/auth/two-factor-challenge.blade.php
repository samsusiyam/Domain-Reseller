@extends('layouts.auth', ['title' => 'Two-Factor Authentication'])

@section('content')
<div class="mb-6 text-center" x-data="{ recovery: false }">
    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>
    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Two-Factor Security</h2>
    <p class="text-xs text-slate-500 mt-2" x-show="!recovery">
        Please confirm access to your account by entering the 6-digit authentication code from your authenticator app.
    </p>
    <p class="text-xs text-slate-500 mt-2" x-show="recovery" x-cloak>
        Please enter one of your emergency recovery codes.
    </p>
</div>

<form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4" x-data="{ recovery: false }">
    @csrf

    <div x-show="!recovery">
        <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Authentication Code</label>
        <input id="code" type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code" placeholder="123456"
            class="w-full text-center tracking-widest font-mono text-lg px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('code') border-rose-500 @enderror">
        @error('code')
            <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="recovery" x-cloak>
        <label for="recovery_code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Emergency Recovery Code</label>
        <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code" placeholder="xxxx-xxxx-xxxx"
            class="w-full text-center font-mono text-sm px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>

    <div class="flex items-center justify-between text-xs pt-1">
        <button type="button" class="text-indigo-600 hover:underline" @click="recovery = !recovery" x-text="recovery ? 'Use an authenticator code instead' : 'Use a recovery code instead'"></button>
    </div>

    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
        Verify & Sign In
    </button>
</form>
@endsection
