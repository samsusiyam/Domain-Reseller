@extends('layouts.auth', ['title' => 'Verify Email'])

@section('content')
<div class="mb-6 text-center">
    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    </div>
    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Verify Your Email</h2>
    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent to your inbox.
    </p>
</div>

@if (session('status') == 'verification-link-sent')
    <div class="mb-4">
        <x-alert type="success" message="A new verification link has been sent to your email address." />
    </div>
@endif

<div class="space-y-4">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition shadow">
            Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
            Log Out
        </button>
    </form>
</div>
@endsection
