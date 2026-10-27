@extends('layouts.guest')

@section('title', '403 Access Denied')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center p-8 bg-[var(--surface)] border border-[var(--border)] rounded-lg">
        <div class="w-14 h-14 rounded-full bg-[var(--bg)] border border-[var(--border)] flex items-center justify-center mx-auto mb-5 text-[var(--accent)] font-semibold text-xl">
            403
        </div>
        <h1 class="text-2xl font-semibold text-[var(--text)] tracking-tight">Access restricted</h1>
        <p class="text-sm text-[var(--text-muted)] mt-2 mb-6 leading-relaxed">
            You do not have administrative or authorized permissions to view this resource. If you believe this is an error, please reach out to support.
        </p>
        <div class="flex items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="btn btn-secondary text-xs h-9 px-4">
                Return home
            </a>
            <a href="{{ route('client.tickets.create') }}" class="btn btn-primary text-xs h-9 px-4">
                Contact support
            </a>
        </div>
    </div>
</div>
@endsection
