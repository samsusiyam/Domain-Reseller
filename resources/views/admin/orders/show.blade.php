@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Order #{{ $order->order_number ?? ('ORD-' . $order->id) }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                           ($order->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->created_at->format('M d, Y H:i') }} &bull; Customer: {{ $order->user?->name ?? 'None' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Retry Provisioning -->
            <form action="{{ route('admin.orders.retry-provision', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Retry Provisioning
                </button>
            </form>

            @if($order->status !== 'cancelled')
                <!-- Cancel Order -->
                <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Cancel this order and refund user wallet balance?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-xl text-xs font-bold transition">
                        Cancel & Refund
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Order Metadata Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Customer Account</span>
            @if($order->user)
                <p class="font-bold text-slate-900 text-base mt-1">{{ $order->user->name }}</p>
                <p class="text-xs text-slate-500 font-mono">{{ $order->user->email }}</p>
            @else
                <p class="text-slate-500 text-sm mt-1">Unassigned User</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Attached Invoice</span>
            @if($order->invoice)
                <p class="font-bold text-indigo-600 text-base mt-1">
                    <a href="{{ route('admin.invoices.show', $order->invoice->id) }}" class="hover:underline">
                        #{{ $order->invoice->invoice_number ?? str_pad($order->invoice->id, 6, '0', STR_PAD_LEFT) }}
                    </a>
                </p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                    {{ $order->invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    Invoice {{ ucfirst($order->invoice->status) }}
                </span>
            @else
                <p class="text-slate-500 text-sm mt-1">Direct API / Wallet deduction</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Order Total</span>
            <p class="font-black text-slate-900 text-2xl mt-1">${{ number_format($order->total_amount, 2) }} USD</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Paid via {{ $order->payment_method ?? 'Wallet Balance' }}</p>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Order Items & Provisioning Logs</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Item Description</th>
                        <th class="px-6 py-3.5">Action Type</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Price</th>
                        <th class="px-6 py-3.5">Upstream Error / Response</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                {{ $item->domain_name ?? $item->description }}
                            </td>
                            <td class="px-6 py-4 text-xs capitalize text-slate-700">
                                {{ str_replace('_', ' ', $item->item_type) }} ({{ $item->years ?? 1 }} Year)
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    {{ $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                       ($item->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                ${{ number_format($item->total_price, 2) }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-rose-600 max-w-xs truncate">
                                {{ $item->error_message ?? ($item->status === 'completed' ? 'Provisioned Successfully' : 'Queued') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
