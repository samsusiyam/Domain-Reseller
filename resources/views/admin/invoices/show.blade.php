@extends('layouts.admin', ['title' => 'Invoice #' . ($invoice->invoice_number ?? $invoice->id)])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Actions Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:text-white dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Invoices</span>
        </a>

        <div class="flex items-center gap-2">
            @if($invoice->status === 'paid')
                <form action="{{ route('admin.invoices.refund', $invoice) }}" method="POST" onsubmit="return confirm('Refund this invoice amount back to the client wallet balance?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 border border-[#F6821F]/40 hover:bg-[#F6821F]/10 text-[#F6821F] rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span>Refund to Wallet</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank" class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-800/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print</span>
            </a>

            @if($invoice->status === 'unpaid')
                <form action="{{ route('admin.invoices.cancel', $invoice) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this invoice?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl text-xs font-bold transition shadow-sm">
                        Cancel
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Invoice Sheet -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-8 sm:p-12 print:shadow-none print:border-none print:p-0 transition-colors duration-200">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start pb-8 border-b border-slate-200 dark:border-slate-800 gap-6">
            <div>
                <x-brand-logo class="h-8 w-auto" :show-text="true" />
                <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                    <p class="font-bold text-slate-800 dark:text-slate-200">{{ \App\Models\SystemSetting::get('company_name', 'HostNibo Technologies Ltd.') }}</p>
                    <p>Automated Billing & Cloud Provisioning Engine</p>
                    <p>Support: {{ \App\Models\SystemSetting::get('support_email', 'support@hostnibo.com') }}</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <div class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider mb-2 border {{ $invoice->status === 'paid' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : ($invoice->status === 'unpaid' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' : 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20') }}">
                    {{ $invoice->status }}
                </div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">INVOICE</h2>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400 space-y-1 font-mono">
                    <p><span class="font-sans font-semibold text-slate-700 dark:text-slate-300">Invoice No:</span> #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p><span class="font-sans font-semibold text-slate-700 dark:text-slate-300">Invoice Date:</span> {{ $invoice->created_at->format('M d, Y') }}</p>
                    <p><span class="font-sans font-semibold text-slate-700 dark:text-slate-300">Due Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</p>
                </div>
            </div>
        </div>

        <!-- Billed To & Metadata -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-200 dark:border-slate-800 text-sm">
            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 dark:text-slate-400 mb-2">Billed To</h3>
                <div class="text-slate-600 dark:text-slate-300 space-y-1">
                    <p class="font-black text-slate-900 dark:text-white text-base">{{ $invoice->user?->name ?? 'Guest/Deleted User' }}</p>
                    @if($invoice->user?->company_name)
                        <p class="font-medium text-slate-700 dark:text-slate-300">{{ $invoice->user->company_name }}</p>
                    @endif
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->user?->email }}</p>
                    @if($invoice->user?->phone)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->user->phone }}</p>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 dark:text-slate-400 mb-2">Payment Details</h3>
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-xs space-y-1 text-slate-600 dark:text-slate-400">
                    <p><span class="font-semibold text-slate-700 dark:text-slate-300">Currency:</span> {{ $invoice->currency ?? 'USD' }}</p>
                    @if($invoice->paid_at)
                        <p><span class="font-semibold text-slate-700 dark:text-slate-300">Paid At:</span> {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                    @endif
                    @if($invoice->payment_method)
                        <p><span class="font-semibold text-slate-700 dark:text-slate-300">Payment Method:</span> {{ ucfirst($invoice->payment_method) }}</p>
                    @endif
                    @if($invoice->notes)
                        <p class="mt-2 pt-2 border-t border-slate-200/50 dark:border-slate-700/50 text-slate-500 dark:text-slate-400 italic">{{ $invoice->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="py-8 border-b border-slate-200 dark:border-slate-800 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-bold text-slate-400 dark:text-slate-500 dark:text-slate-400">
                        <th class="pb-3">Item Description</th>
                        <th class="pb-3 text-center">Qty</th>
                        <th class="pb-3 text-right">Unit Price</th>
                        <th class="pb-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoice->items as $item)
                        @php
                            $lines = explode("\n", $item->description);
                            $mainDesc = $lines[0];
                            $extraDesc = $lines[1] ?? null;
                        @endphp
                        <tr>
                            <td class="py-4 pr-4">
                                <span class="font-bold text-slate-800 dark:text-white block">{{ $mainDesc }}</span>
                                @if($extraDesc)
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block mt-1 font-normal">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">✓</span> {{ $extraDesc }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 text-center text-slate-600 dark:text-slate-400 font-mono text-xs">{{ $item->quantity }}</td>
                            <td class="py-4 text-right text-slate-600 dark:text-slate-400 font-mono text-xs">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 text-right font-bold text-slate-900 dark:text-white font-mono text-sm">${{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-slate-400 dark:text-slate-500 dark:text-slate-400 text-xs italic">No line items associated with this invoice</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="pt-8 flex justify-end text-sm">
            <div class="w-full sm:w-80 space-y-3">
                <div class="flex justify-between text-slate-600 dark:text-slate-400 text-xs">
                    <span>Subtotal:</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 text-xs">
                        <span>Discount:</span>
                        <span class="font-mono font-semibold">-${{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-3 border-t border-slate-200 dark:border-slate-800">
                    <span>Total Amount:</span>
                    <span class="font-mono">${{ number_format($invoice->total_amount, 2) }} USD</span>
                </div>
                <div class="flex justify-between text-sm font-bold {{ $invoice->balance_due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} pt-2 border-t border-dashed border-slate-200 dark:border-slate-800">
                    <span>Remaining Balance:</span>
                    <span class="font-mono">${{ number_format($invoice->balance_due, 2) }} USD</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
