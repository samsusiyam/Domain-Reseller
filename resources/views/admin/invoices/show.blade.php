@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Invoices
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @if($invoice->status === 'unpaid')
                <form action="{{ route('admin.invoices.mark-paid', $invoice->id) }}" method="POST" onsubmit="return confirm('Mark this invoice as PAID manually?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition">
                        ✓ Mark as Paid
                    </button>
                </form>

                <form action="{{ route('admin.invoices.cancel', $invoice->id) }}" method="POST" onsubmit="return confirm('Cancel this invoice?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold shadow-sm transition">
                        Cancel Invoice
                    </button>
                </form>
            @elseif($invoice->status === 'paid')
                <form action="{{ route('admin.invoices.refund', $invoice->id) }}" method="POST" onsubmit="return confirm('Refund this invoice and credit customer wallet?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 border border-purple-200 text-purple-700 hover:bg-purple-50 rounded-lg text-xs font-bold transition">
                        Refund to Wallet
                    </button>
                </form>
            @endif

            <button onclick="window.print()" class="px-3.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
        </div>
    </div>

    <!-- Invoice Sheet -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12 print:shadow-none print:border-none print:p-0">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-slate-200 pb-8">
            <div>
                <span class="text-2xl font-black tracking-tight text-slate-900">Domain<span class="text-indigo-600">Reseller</span></span>
                <div class="mt-4 text-xs text-slate-500 space-y-1">
                    <p class="font-bold text-slate-700">Domain Reseller Platform Administration</p>
                    <p>Automated Billing & Cloud Provisioning Engine</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">INVOICE</h2>
                <div class="mt-2 text-sm text-slate-600 space-y-1">
                    <p><span class="font-semibold text-slate-800">Invoice Number:</span> #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p><span class="font-semibold text-slate-800">Date:</span> {{ $invoice->created_at->format('M d, Y') }}</p>
                    <p><span class="font-semibold text-slate-800">Due Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</p>
                </div>

                <div class="mt-4">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold border uppercase
                        {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' :
                           ($invoice->status === 'unpaid' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Billed To / Notes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-200 text-sm">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Billed To</h3>
                <div class="text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900 text-base">{{ $invoice->user?->name ?? 'Guest/Deleted' }}</p>
                    @if($invoice->user?->company_name)
                        <p class="font-medium text-slate-800">{{ $invoice->user->company_name }}</p>
                    @endif
                    <p>{{ $invoice->user?->email }}</p>
                    @if($invoice->user?->phone)
                        <p>{{ $invoice->user->phone }}</p>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Billing Metadata</h3>
                <div class="text-slate-600 space-y-1 bg-slate-50 p-4 rounded-xl border border-slate-200/60 text-xs">
                    <p><span class="font-semibold text-slate-700">Currency:</span> {{ $invoice->currency ?? 'USD' }}</p>
                    @if($invoice->paid_at)
                        <p><span class="font-semibold text-slate-700">Paid At:</span> {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                    @endif
                    @if($invoice->notes)
                        <p class="mt-2 text-slate-500 italic">{{ $invoice->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="py-8 border-b border-slate-200">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase font-semibold text-slate-400">
                        <th class="pb-3">Description</th>
                        <th class="pb-3 text-center">Qty</th>
                        <th class="pb-3 text-right">Unit Price</th>
                        <th class="pb-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoice->items as $item)
                        <tr>
                            <td class="py-4 font-semibold text-slate-800">{{ $item->description }}</td>
                            <td class="py-4 text-center text-slate-600">{{ $item->quantity }}</td>
                            <td class="py-4 text-right text-slate-600">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 text-right font-bold text-slate-900">${{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">No items configured</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="pt-8 flex justify-end text-sm">
            <div class="w-full sm:w-80 space-y-3">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-800">${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-slate-900 pt-3 border-t border-slate-200">
                    <span>Total Amount:</span>
                    <span>${{ number_format($invoice->total_amount, 2) }} USD</span>
                </div>
                <div class="flex justify-between text-base font-bold {{ $invoice->balance_due > 0 ? 'text-amber-600' : 'text-emerald-600' }} pt-2 border-t border-dashed border-slate-200">
                    <span>Balance Due:</span>
                    <span>${{ number_format($invoice->balance_due, 2) }} USD</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
