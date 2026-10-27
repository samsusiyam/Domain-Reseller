@extends('layouts.guest')

@section('title', '419 Page Expired')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center p-8 bg-[var(--surface)] border border-[var(--border)] rounded-lg">
        <div class="w-14 h-14 rounded-full bg-[var(--bg)] border border-[var(--border)] flex items-center justify-center mx-auto mb-5 text-[var(--accent)] font-semibold text-xl">
            419
        </div>
        <h1 class="text-2xl font-semibold text-[var(--text)] tracking-tight">Security token expired</h1>
        <p class="text-sm text-[var(--text-muted)] mt-2 mb-6 leading-relaxed">
            Your session or CSRF security token has expired due to inactivity. Please refresh the page and try again.
        </p>
        <div class="flex items-center justify-center gap-3">
            <button onclick="window.location.reload()" class="cf-btn cf-btn-primary text-xs h-9 px-4">
                Refresh page
            </button>
            <a href="{{ route('login') }}" class="cf-btn cf-btn-secondary text-xs h-9 px-4">
                Sign in again
            </a>
        </div>
    </div>
</div>
@endsection
