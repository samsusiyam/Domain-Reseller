@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Invoice Management</h1>
            <p class="text-xs text-slate-500 mt-1">Supervise billing records, audit paid invoices, and generate custom client charges.</p>
        </div>
        <div>
            <a href="{{ route('admin.invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition text-xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Custom Invoice
            </a>
        </div>
    </div>

    <!-- Summary Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Total Settled (Paid)</span>
                <p class="text-3xl font-black text-slate-900 mt-1">${{ number_format($totalPaid, 2) }} USD</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                ✓
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Outstanding Balance Due</span>
                <p class="text-3xl font-black text-slate-900 mt-1">${{ number_format($totalUnpaid, 2) }} USD</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                $
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by invoice number, user name or email..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-44">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.invoices.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($invoices->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No invoices found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Invoice #</th>
                            <th class="px-6 py-3.5">Customer</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Due Date</th>
                            <th class="px-6 py-3.5 text-right">Total</th>
                            <th class="px-6 py-3.5 text-right">Balance Due</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        #{{ $inv->invoice_number ?? str_pad($inv->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $inv->user?->name ?? 'Guest/Deleted' }}</div>
                                    <div class="text-xs text-slate-400">{{ $inv->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $inv->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : 'Due on receipt' }}</td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    ${{ number_format($inv->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold {{ $inv->balance_due > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                    ${{ number_format($inv->balance_due, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' :
                                           ($inv->status === 'unpaid' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Manage &rarr;</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
