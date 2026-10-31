@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Domain provisioning orders</h1>
            <p class="text-xs mt-1" style="color: var(--text-muted);">Audit registrations, renewals, incoming transfers, and registrar provisioning status.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="cf-panel p-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order number, domain name, client email..."
                       class="cf-input h-9 text-xs w-full">
            </div>

            <div class="w-full sm:w-44">
                <select name="status" class="cf-select h-9 text-xs w-full">
                    <option value="">All statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <button type="submit" class="cf-btn cf-btn-primary h-9 px-4 text-xs font-medium w-full sm:w-auto">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.orders.index') }}" class="text-xs hover:underline" style="color: var(--text-muted);">Reset</a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="cf-panel overflow-hidden">
        @if($orders->isEmpty())
            <div class="text-center py-16 px-4 text-xs" style="color: var(--text-muted);">
                No domain orders found matching criteria.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="cf-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Domains</th>
                            <th>Date</th>
                            <th class="align-right">Total (USD)</th>
                            <th class="text-center">Status</th>
                            <th class="align-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                            <tr>
                                <td class="font-mono font-medium">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="hover:underline" style="color: var(--accent);">
                                        #{{ $o->order_number ?? ('ORD-' . $o->id) }}
                                    </a>
                                </td>
                                <td>
                                    <div class="font-medium" style="color: var(--text);">{{ $o->user?->name ?? 'Unassigned' }}</div>
                                    <div class="text-[11px] font-mono" style="color: var(--text-muted);">{{ $o->user?->email }}</div>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($o->items as $it)
                                            <span class="inline-block px-1.5 py-0.5 text-[11px] font-mono rounded" style="background-color: var(--surface); color: var(--text); border: 1px solid var(--border);">
                                                {{ $it->domain_name ?? $it->description }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-xs font-mono" style="color: var(--text-muted);">
                                    {{ $o->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="align-right font-mono font-medium tabular-nums" style="color: var(--text);">
                                    <x-price :usd="$o->total_amount" />
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center gap-1.5 text-xs">
                                        <span class="status-dot {{ $o->status === 'completed' ? 'status-success' : ($o->status === 'failed' ? 'status-danger' : 'status-warning') }}"></span>
                                        <span class="capitalize" style="color: var(--text);">{{ $o->status }}</span>
                                    </div>
                                </td>
                                <td class="align-right">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-3 border-t" style="border-color: var(--border);">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
