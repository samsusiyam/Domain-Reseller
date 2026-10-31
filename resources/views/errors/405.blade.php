@extends('layouts.guest')

@section('title', '405 Method Not Allowed')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center p-8 bg-[var(--surface)] border border-[var(--border)] rounded-lg">
        <div class="w-14 h-14 rounded-full bg-[var(--bg)] border border-[var(--border)] flex items-center justify-center mx-auto mb-5 text-[var(--accent)] font-semibold text-xl">
            405
        </div>
        <h1 class="text-2xl font-semibold text-[var(--text)] tracking-tight">Method not allowed</h1>
        <p class="text-sm text-[var(--text-muted)] mt-2 mb-6 leading-relaxed">
            The requested HTTP action is not supported for this endpoint. Please return to your account dashboard or verify the form submission.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="cf-btn cf-btn-secondary text-xs h-9 px-4">
                Return home
            </a>
            <a href="{{ route('client.dashboard') }}" class="cf-btn cf-btn-primary text-xs h-9 px-4">
                Client dashboard
            </a>
        </div>
    </div>
</div>
@endsection
