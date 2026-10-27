@extends('layouts.guest', ['title' => 'Checkout Failed'])

@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">

    <div class="cf-panel p-8">
        <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center"
             style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger);">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">Checkout was not completed</h1>
        <p class="text-xs mt-2" style="color: var(--text-muted);">
            {{ $reason ?? 'The upstream registrar or payment processor declined the authorization request.' }}
        </p>

        <div class="mt-8 space-y-2">
            <a href="{{ route('checkout.index') }}" class="cf-btn cf-btn-primary w-full h-10 text-sm">
                Return to checkout & retry
            </a>
            <a href="{{ route('cart.index') }}" class="cf-btn cf-btn-secondary w-full h-10 text-sm">
                Back to cart
            </a>
        </div>
    </div>

</div>
@endsection
