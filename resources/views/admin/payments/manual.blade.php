@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Manual & Personal Payment Approvals</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review and verify submissions from bKash Personal, Binance Personal (Pay ID), and Bank Wire.</p>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($pendingPayments->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-xs">
                No pending manual payment verification requests.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                        <tr>
                            <th class="px-5 py-3.5">Method</th>
                            <th class="px-5 py-3.5">Customer & Sender Info</th>
                            <th class="px-5 py-3.5">Transaction ID / Proof</th>
                            <th class="px-5 py-3.5">Invoice #</th>
                            <th class="px-5 py-3.5 text-right">Amount</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($pendingPayments as $p)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4">
                                    @if($p->gateway === 'bkash_personal')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-pink-50 text-pink-700 dark:bg-pink-950/40 dark:text-pink-300 border border-pink-200 dark:border-pink-900/40">
                                            bKash Personal
                                        </span>
                                    @elseif($p->gateway === 'binance_personal')
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                            Binance Personal
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            Bank / Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $p->user?->name ?? 'Guest' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $p->user?->email }}</div>
                                    @if(!empty($p->payment_details['sender_phone']))
                                        <div class="text-[11px] font-mono text-slate-500 mt-0.5">Phone: {{ $p->payment_details['sender_phone'] }}</div>
                                    @elseif(!empty($p->payment_details['sender_account']))
                                        <div class="text-[11px] font-mono text-slate-500 mt-0.5">Binance: {{ $p->payment_details['sender_account'] }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-xs select-all bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-700">
                                        {{ $p->gateway_transaction_id ?? $p->reference_id ?? ('TXN-' . $p->id) }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $p->created_at->format('M d, Y · H:i') }}</div>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs">
                                    @if($p->invoice_id)
                                        <a href="{{ route('admin.invoices.show', $p->invoice_id) }}" class="text-[#F6821F] hover:underline font-bold">
                                            #{{ $p->invoice?->invoice_number ?? $p->invoice_id }}
                                        </a>
                                    @else
                                        <span class="text-slate-500 font-sans">Wallet Deposit</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">
                                        ${{ number_format((float)$p->amount, 2) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                        {{ $p->status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                    @if($p->status !== 'completed')
                                        <form action="{{ route('admin.payments.manual.approve', $p->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer">
                                                Approve
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.payments.manual.reject', $p->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="reason" value="Transaction ID invalid or payment not received">
                                            <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs text-red-600 hover:text-red-700 cursor-pointer">
                                                Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-emerald-600 font-medium">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pendingPayments->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $pendingPayments->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
