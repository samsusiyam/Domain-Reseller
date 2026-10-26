@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manual Offline Payment Approvals</h1>
            <p class="text-xs text-slate-500 mt-1">Review bank transfers, offline deposits, and manual transaction receipts requiring admin confirmation.</p>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($pendingPayments->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No pending manual payment submissions.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Reference / TXN</th>
                            <th class="px-6 py-3.5">Customer</th>
                            <th class="px-6 py-3.5">Invoice #</th>
                            <th class="px-6 py-3.5">Submission Date</th>
                            <th class="px-6 py-3.5 text-right">Amount (USD)</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Verification Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingPayments as $p)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900 text-xs">
                                    {{ $p->reference_id ?? ('TXN-' . $p->id) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $p->user?->name ?? 'Guest' }}</div>
                                    <div class="text-xs text-slate-400">{{ $p->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600">
                                    @if($p->invoice_id)
                                        <a href="{{ route('admin.invoices.show', $p->invoice_id) }}" class="hover:underline font-bold">
                                            #{{ $p->invoice?->invoice_number ?? $p->invoice_id }}
                                        </a>
                                    @else
                                        Wallet Deposit
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-xs">{{ $p->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    ${{ number_format($p->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    @if($p->status !== 'completed')
                                        <form action="{{ route('admin.payments.manual.approve', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve this payment and credit user account?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                Approve
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.payments.manual.reject', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Reject this payment submission?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-bold transition">
                                                Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-semibold">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pendingPayments->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $pendingPayments->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
