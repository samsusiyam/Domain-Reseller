@extends('layouts.auth', ['title' => 'Verify email'])

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Verify your email</h1>
    <p class="text-xs mt-1" style="color: var(--text-muted);">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent to you.
    </p>
</div>

@if (session('status') == 'verification-link-sent')
    <div class="mb-4 p-3 rounded-[6px] text-xs font-medium"
         style="background-color: var(--success-bg); border: 1px solid var(--success); color: var(--success);">
        A new verification link has been sent to the email address provided during registration.
    </div>
@endif

<div class="space-y-4">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="cf-btn cf-btn-primary w-full h-10 font-medium text-sm">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="cf-btn cf-btn-secondary w-full h-10 font-medium text-sm">
            Sign out
        </button>
    </form>
</div>
@endsection
