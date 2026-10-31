@extends('layouts.guest', ['title' => 'Order Confirmation'])

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 sm:py-16">

    <div class="cf-panel p-6 sm:p-8 text-center">

        <div class="w-12 h-12 rounded-full mx-auto mb-4 flex items-center justify-center"
             style="background-color: var(--success-bg); color: var(--success); border: 1px solid var(--success);">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>

        <h1 class="text-xl font-semibold tracking-tight" style="color: var(--text);">
            @if($isPending)
                Order received & awaiting verification
            @else
                Order confirmed & processing!
            @endif
        </h1>

        <p class="text-xs mt-1.5 max-w-md mx-auto" style="color: var(--text-muted);">
            @if($isPending)
                Your offline payment reference has been recorded. An administrator will verify the deposit slip and activate domain provisioning.
            @else
                Your payment was received. Registry provisioning jobs have been dispatched to upstream registrar endpoints.
            @endif
        </p>

        <!-- Order Summary Pill -->
        <div class="mt-6 p-4 rounded-[6px] border text-left text-xs space-y-2.5" style="background-color: var(--surface); border-color: var(--border);">
            <div class="flex justify-between">
                <span style="color: var(--text-muted);">Order number:</span>
                <span class="font-mono font-medium" style="color: var(--text);">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span style="color: var(--text-muted);">Status:</span>
                <span class="font-mono font-semibold uppercase text-xs {{ $order->status === 'completed' || $order->status === 'processing' ? 'text-[#16A34A]' : 'text-[#D97706]' }}">
                    {{ $order->status }}
                </span>
            </div>
            <div class="flex justify-between">
                <span style="color: var(--text-muted);">Total charged:</span>
                <span class="font-mono font-semibold" style="color: var(--text);">
                    <x-price :usd="$order->total_amount" />
                </span>
            </div>
        </div>

        <!-- Domains List -->
        <div class="mt-6 border-t pt-4 text-left" style="border-color: var(--border);">
            <span class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color: var(--text-muted);">
                Items in this order:
            </span>
            <div class="space-y-2">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between text-xs p-2.5 rounded-[4px] border" style="background-color: var(--bg); border-color: var(--border);">
                        <div>
                            <span class="font-mono font-medium" style="color: var(--text);">{{ $item->domain_name }}</span>
                            <span class="text-[11px] ml-2" style="color: var(--text-muted);">({{ $item->years }} yr)</span>
                        </div>
                        <span class="cf-status-dot {{ $item->status === 'completed' ? 'success' : ($item->status === 'failed' ? 'danger' : 'info') }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('client.domains.index') }}" class="cf-btn cf-btn-primary w-full sm:w-auto h-10 px-5 text-sm">
                View my domains
            </a>
            <a href="{{ route('home') }}" class="cf-btn cf-btn-secondary w-full sm:w-auto h-10 px-5 text-sm">
                Back to storefront
            </a>
        </div>

    </div>

</div>
@endsection
