@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="cf-breadcrumb mb-1.5">
                <a href="{{ route('admin.orders.index') }}">Orders</a>
                <span class="cf-breadcrumb-sep">/</span>
                <span class="cf-breadcrumb-current">#{{ $order->order_number ?? ('ORD-' . $order->id) }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">
                    Order #{{ $order->order_number ?? ('ORD-' . $order->id) }}
                </h1>
                <div class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded border" style="background-color: var(--surface); border-color: var(--border);">
                    <span class="status-dot {{ $order->status === 'completed' ? 'status-success' : ($order->status === 'failed' ? 'status-danger' : 'status-warning') }}"></span>
                    <span class="capitalize font-medium" style="color: var(--text);">{{ $order->status }}</span>
                </div>
            </div>
            <p class="text-xs mt-1" style="color: var(--text-muted);">
                Placed on {{ $order->created_at->format('Y-m-d H:i:s') }} &bull; Customer: {{ $order->user?->name ?? 'None' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Retry Provisioning -->
            <form action="{{ route('admin.orders.retry-provision', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-primary h-9 px-3 text-xs font-medium inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>Retry provisioning</span>
                </button>
            </form>

            @if($order->status !== 'cancelled')
                <!-- Cancel Order -->
                <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Cancel this order and refund user wallet balance?');">
                    @csrf
                    <button type="submit" class="cf-btn cf-btn-secondary h-9 px-3 text-xs font-medium hover:text-[#DC2626]">
                        Cancel & refund
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Order Metadata Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="cf-panel p-4">
            <span class="text-[11px] uppercase tracking-wider block mb-1 font-semibold" style="color: var(--text-muted);">Customer</span>
            @if($order->user)
                <p class="font-medium text-sm" style="color: var(--text);">{{ $order->user->name }}</p>
                <p class="text-xs font-mono mt-0.5" style="color: var(--text-muted);">{{ $order->user->email }}</p>
            @else
                <p class="text-xs" style="color: var(--text-muted);">Unassigned user</p>
            @endif
        </div>

        <div class="cf-panel p-4">
            <span class="text-[11px] uppercase tracking-wider block mb-1 font-semibold" style="color: var(--text-muted);">Invoice</span>
            @if($order->invoice)
                <p class="font-mono font-medium text-sm">
                    <a href="{{ route('admin.invoices.show', $order->invoice->id) }}" class="hover:underline" style="color: var(--accent);">
                        #{{ $order->invoice->invoice_number ?? str_pad($order->invoice->id, 6, '0', STR_PAD_LEFT) }}
                    </a>
                </p>
                <div class="mt-1">
                    <span class="text-[11px] font-mono px-1.5 py-0.5 rounded uppercase" style="background-color: var(--surface); color: var(--text-muted); border: 1px solid var(--border);">
                        {{ $order->invoice->status }}
                    </span>
                </div>
            @else
                <p class="text-xs" style="color: var(--text-muted);">Direct wallet deduction</p>
            @endif
        </div>

        <div class="cf-panel p-4">
            <span class="text-[11px] uppercase tracking-wider block mb-1 font-semibold" style="color: var(--text-muted);">Total</span>
            <p class="font-mono font-semibold text-lg tabular-nums" style="color: var(--text);">
                <x-price :usd="$order->total_amount" />
            </p>
            <p class="text-[11px] mt-0.5" style="color: var(--text-muted);">Method: {{ $order->payment_method ?? 'Wallet balance' }}</p>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="cf-panel overflow-hidden">
        <div class="cf-panel-header">
            <h3 class="font-semibold text-xs" style="color: var(--text);">Order items & upstream status</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="cf-table">
                <thead>
                    <tr>
                        <th>Domain item</th>
                        <th>Action</th>
                        <th class="text-center">Status</th>
                        <th class="align-right">Price</th>
                        <th>Provisioning notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="font-mono font-medium" style="color: var(--text);">
                                {{ $item->domain_name ?? $item->description }}
                            </td>
                            <td class="text-xs capitalize" style="color: var(--text-muted);">
                                {{ str_replace('_', ' ', $item->item_type) }} ({{ $item->years ?? 1 }} yr)
                            </td>
                            <td class="text-center">
                                <div class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="status-dot {{ $item->status === 'completed' ? 'status-success' : ($item->status === 'failed' ? 'status-danger' : 'status-warning') }}"></span>
                                    <span class="capitalize" style="color: var(--text);">{{ $item->status }}</span>
                                </div>
                            </td>
                            <td class="align-right font-mono font-medium tabular-nums" style="color: var(--text);">
                                <x-price :usd="$item->total_price" />
                            </td>
                            <td class="font-mono text-xs max-w-xs truncate" style="color: var(--text-muted);">
                                {{ $item->error_message ?? ($item->status === 'completed' ? 'Provisioned successfully' : 'Queued') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
