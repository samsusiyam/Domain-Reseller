@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Financial Transaction Ledger</h1>
            <p class="text-xs text-slate-500 mt-1">Audit trail of all client and reseller payments, deposits, withdrawals, and balance debits.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by reference ID, description, user name or email..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-40">
                <select name="type" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Types</option>
                    <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Deposit / Credit</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Debit / Charge</option>
                    <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>

            <div class="w-full sm:w-40">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('type') || request('status'))
                <a href="{{ route('admin.transactions.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($transactions->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No financial transactions found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Reference ID</th>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Method</th>
                            <th class="px-6 py-3.5">Description</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5 text-right">Amount (USD)</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $txn->reference_id ?? ('TXN-' . $txn->id) }}</td>
                                <td class="px-6 py-4 font-sans">
                                    <div class="font-bold text-slate-800">{{ $txn->user?->name ?? 'None' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $txn->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4 uppercase font-sans font-semibold text-[11px] text-slate-600">
                                    {{ $txn->payment_method ?? 'Wallet' }}
                                </td>
                                <td class="px-6 py-4 font-sans text-slate-700 font-medium">
                                    {{ $txn->description ?? 'Transaction' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-sans whitespace-nowrap">
                                    {{ $txn->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right font-black font-sans text-sm {{ $txn->type === 'credit' || $txn->amount > 0 ? 'text-emerald-600' : 'text-slate-900' }}">
                                    ${{ number_format($txn->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center font-sans">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $txn->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $txn->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="p-4 border-t border-slate-100 font-sans">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
