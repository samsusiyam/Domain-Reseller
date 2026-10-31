@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('client.wallet.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Transaction History</h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Audit log of all credits, debits, registrations, and payment events.</p>
        </div>
        <div>
            <a href="{{ route('client.wallet.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Funds
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        @if($transactions->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300">No Transactions Found</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Your account has no recorded transactions yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Transaction ID</th>
                            <th class="px-6 py-3.5">Type</th>
                            <th class="px-6 py-3.5">Payment Method</th>
                            <th class="px-6 py-3.5">Description</th>
                            <th class="px-6 py-3.5">Date & Time</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4 font-mono font-medium text-slate-800 dark:text-white">{{ $txn->transaction_id ?? ('TXN-' . $txn->id) }}</td>
                                <td class="px-6 py-4">
                                    @if($txn->type === 'credit' || $txn->amount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200/50">Credit / Inflow</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200/50">Debit / Outflow</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 uppercase font-semibold text-xs text-slate-500 dark:text-slate-400">{{ $txn->gateway ?? 'Wallet' }}</td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300 font-medium">{{ $txn->description ?? 'Transaction' }}</td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 text-right font-bold {{ $txn->type === 'credit' || $txn->amount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ $txn->type === 'credit' || $txn->amount > 0 ? '+' : '' }}<x-price-display :amountUsd="$txn->amount" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
