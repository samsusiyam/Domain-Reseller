@extends('layouts.guest')

@section('title', '500 Server Error')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center p-8 bg-[var(--surface)] border border-[var(--border)] rounded-lg">
        <div class="w-14 h-14 rounded-full bg-[var(--bg)] border border-[var(--border)] flex items-center justify-center mx-auto mb-5 text-[var(--danger)] font-semibold text-xl">
            500
        </div>
        <h1 class="text-2xl font-semibold text-[var(--text)] tracking-tight">System encountered an issue</h1>
        <p class="text-sm text-[var(--text-muted)] mt-2 mb-6 leading-relaxed">
            Our engineering team has been notified. This is typically a transient connection timeout with an upstream registrar or payment processor.
        </p>
        <div class="flex items-center justify-center gap-3">
            <button onclick="window.location.reload()" class="btn btn-primary text-xs h-9 px-4">
                Reload page
            </button>
            <a href="{{ url('/') }}" class="btn btn-secondary text-xs h-9 px-4">
                Return home
            </a>
        </div>
    </div>
</div>
@endsection
