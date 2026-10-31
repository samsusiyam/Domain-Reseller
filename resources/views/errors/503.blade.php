@extends('layouts.guest')

@section('title', '503 Scheduled Maintenance')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center p-8 bg-[var(--surface)] border border-[var(--border)] rounded-lg">
        <div class="w-14 h-14 rounded-full bg-[var(--bg)] border border-[var(--border)] flex items-center justify-center mx-auto mb-5 text-[var(--accent)] font-semibold text-xl">
            <svg class="w-7 h-7 text-[var(--accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <h1 class="text-2xl font-semibold text-[var(--text)] tracking-tight">Scheduled maintenance</h1>
        <p class="text-sm text-[var(--text-muted)] mt-2 mb-6 leading-relaxed">
            We are performing scheduled upgrades to enhance platform performance and security. Live provisioning will resume shortly.
        </p>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-[var(--bg)] border border-[var(--border)] text-xs text-[var(--text-muted)]">
            <span class="status-dot status-warning"></span>
            Maintenance in progress
        </div>
    </div>
</div>
@endsection
