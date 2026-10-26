@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Domain Provisioning Orders</h1>
            <p class="text-xs text-slate-500 mt-1">Audit registrations, renewals, incoming transfers, and registrar provisioning status.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order number, domain name, user..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-44">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($orders->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No orders found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Order Number</th>
                            <th class="px-6 py-3.5">Customer / Reseller</th>
                            <th class="px-6 py-3.5">Items</th>
                            <th class="px-6 py-3.5">Order Date</th>
                            <th class="px-6 py-3.5 text-right">Total (USD)</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($orders as $o)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        #{{ $o->order_number ?? ('ORD-' . $o->id) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $o->user?->name ?? 'None' }}</div>
                                    <div class="text-xs text-slate-400">{{ $o->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @foreach($o->items as $it)
                                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-800 text-xs font-mono rounded mr-1">
                                            {{ $it->domain_name ?? $it->description }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $o->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    ${{ number_format($o->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        {{ $o->status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                                           ($o->status === 'failed' ? 'bg-rose-100 text-rose-800' :
                                           ($o->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                                        {{ ucfirst($o->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Details &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
