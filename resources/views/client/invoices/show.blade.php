@extends('layouts.app')

@section('title', 'Invoice #' . ($invoice->invoice_number ?? $invoice->id))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar (Print & Back) -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('client.invoices.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-[#F6821F] transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="cf-btn cf-btn-secondary text-xs h-9 px-4 flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Invoice Paper Sheet (Clean Minimalist White / Dark Card) -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-8 sm:p-12 print:shadow-none print:border-none print:p-0">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-slate-200 dark:border-slate-800 pb-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#F6821F] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        HN
                    </div>
                    <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Host<span class="text-[#F6821F]">Nibo</span></span>
                </div>
                <div class="mt-3 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p class="font-semibold text-slate-800 dark:text-slate-200">HostNibo Technologies Ltd.</p>
                    <p>Enterprise Wholesale Domain Hub & Cloud Services</p>
                    <p>BIN / VAT: 004819264-0101 • Dhaka, Bangladesh</p>
                    <p>Email: support@hostnibo.com • WhatsApp: +880 1849-693630</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">INVOICE</h2>
                <div class="mt-2 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p><span class="font-semibold text-slate-800 dark:text-slate-200">Invoice:</span> #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p><span class="font-semibold text-slate-800 dark:text-slate-200">Date:</span> {{ $invoice->created_at->format('M d, Y') }}</p>
                    <p><span class="font-semibold text-slate-800 dark:text-slate-200">Due Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</p>
                </div>

                <div class="mt-3">
                    @if($invoice->status === 'paid')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            PAID
                        </span>
                    @elseif($invoice->status === 'unpaid')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200 dark:border-red-800">
                            UNPAID
                        </span>
                    @elseif($invoice->status === 'cancelled')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            CANCELLED
                        </span>
                    @elseif($invoice->status === 'refunded')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                            REFUNDED
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Billed To / Payment Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-6 border-b border-slate-200 dark:border-slate-800 text-xs">
            <div>
                <h3 class="font-semibold uppercase tracking-wider text-slate-500 mb-2">Billed to</h3>
                <div class="text-slate-700 dark:text-slate-300 space-y-1">
                    <p class="font-bold text-sm text-slate-900 dark:text-white">{{ $invoice->user->name }}</p>
                    @if($invoice->user->company_name)
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ $invoice->user->company_name }}</p>
                    @endif
                    <p>{{ $invoice->user->email }}</p>
                    @if($invoice->user->phone)
                        <p>{{ $invoice->user->phone }}</p>
                    @endif
                    @if($invoice->user->address_line1 || $invoice->user->city)
                        <p>{{ $invoice->user->address_line1 ?? '' }}{{ $invoice->user->city ? ', ' . $invoice->user->city : '' }}</p>
                    @endif
                    <p>{{ $invoice->user->country ?? 'Bangladesh' }}</p>
                </div>
            </div>

            <div>
                <h3 class="font-semibold uppercase tracking-wider text-slate-500 mb-2">Payment specification</h3>
                <div class="space-y-1.5 p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                    <p><span class="font-semibold text-slate-800 dark:text-slate-200">Billing currency:</span> {{ $invoice->currency ?? 'USD' }}</p>
                    @if($invoice->paid_at)
                        <p><span class="font-semibold text-slate-800 dark:text-slate-200">Paid at:</span> {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                    @endif
                    <p><span class="font-semibold text-slate-800 dark:text-slate-200">Registry status:</span> Real-time automated lock</p>
                    @if($invoice->notes)
                        <p class="mt-1 text-[11px] italic">{{ $invoice->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="py-6 border-b border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="pb-2.5">Item description</th>
                        <th class="pb-2.5 text-center">Period / Qty</th>
                        <th class="pb-2.5 text-right">Unit rate</th>
                        <th class="pb-2.5 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($invoice->items as $item)
                        <tr>
                            <td class="py-3.5">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $item->description }}</p>
                                @if($item->item_type)
                                    <span class="text-[11px] text-slate-500 uppercase">{{ $item->item_type }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 text-center tabular-nums">{{ $item->quantity }}</td>
                            <td class="py-3.5 text-right tabular-nums">
                                <x-price-display :amountUsd="$item->unit_price" :showBdt="false" />
                            </td>
                            <td class="py-3.5 text-right font-semibold text-slate-900 dark:text-white tabular-nums">
                                <x-price-display :amountUsd="$item->total_price" :showBdt="false" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-400">General Domain Provisioning Charge</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="pt-6 flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Subtotal</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200 tabular-nums">
                        <x-price-display :amountUsd="$invoice->subtotal" :showBdt="false" />
                    </span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-medium">
                        <span>Promotional discount</span>
                        <span class="tabular-nums">- <x-price-display :amountUsd="$invoice->discount_amount" :showBdt="false" /></span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Tax & ICANN fee</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200 tabular-nums">
                        @if($invoice->tax_amount > 0)
                            <x-price-display :amountUsd="$invoice->tax_amount" :showBdt="false" />
                        @else
                            Included
                        @endif
                    </span>
                </div>
                <div class="flex justify-between text-sm font-bold text-slate-900 dark:text-white pt-2.5 border-t border-slate-200 dark:border-slate-800">
                    <span>Total</span>
                    <span class="tabular-nums text-base">
                        <x-price-display :amountUsd="$invoice->total_amount" :showBdt="false" />
                    </span>
                </div>
                <div class="flex justify-between text-xs font-bold {{ $invoice->balance_due > 0 ? 'text-[#F6821F]' : 'text-emerald-600' }} pt-1.5 border-t border-dashed border-slate-200 dark:border-slate-800">
                    <span>Balance due</span>
                    <span class="tabular-nums font-extrabold text-sm">
                        <x-price-display :amountUsd="$invoice->balance_due" :showBdt="false" />
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Processing Options (If Unpaid) -->
    @if($invoice->status === 'unpaid' && $invoice->balance_due > 0)
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8 no-print">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">Settle Invoice</h3>
            <p class="text-xs text-slate-500 mb-6">Select your payment method below to clear this invoice immediately.</p>

            <form action="{{ route('client.invoices.pay', $invoice->id) }}" method="POST" x-data="{ selectedGateway: 'balance' }">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                    <!-- Option 1: Wallet Balance -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'balance' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="balance" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Account Credit</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'balance' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'balance'"></div>
                            </div>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 block">Available:</span>
                            <span class="text-xs font-bold {{ auth()->user()->walletBalance >= $invoice->balance_due ? 'text-emerald-600' : 'text-red-500' }}">
                                <x-price-display :amountUsd="auth()->user()->walletBalance" :showBdt="false" />
                            </span>
                        </div>
                    </label>

                    <!-- Option 2: bKash -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'bkash' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="bkash" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">bKash (BDT)</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'bkash' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'bkash'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Instant checkout</span>
                    </label>

                    <!-- Option 3: Stripe -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'stripe' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="stripe" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Card / Stripe</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'stripe' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'stripe'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Visa, Mastercard</span>
                    </label>

                    <!-- Option 4: Binance Pay / Crypto -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'binance' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="binance" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Binance Pay</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'binance' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'binance'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">USDT / Crypto</span>
                    </label>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                        <span>Encrypted 256-bit automated settlement</span>
                    </p>
                    <button type="submit" 
                            class="w-full sm:w-auto h-11 px-8 bg-[#F6821F] hover:bg-[#E07116] active:bg-[#C9620E] text-white font-semibold text-sm rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>Pay</span>
                        <x-price-display :amountUsd="$invoice->balance_due" :showBdt="false" class="text-white font-bold" />
                        <span>Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Payment Transactions History -->
    @if($invoice->transactions->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden no-print">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Settled Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-[11px] font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3">Transaction Reference</th>
                            <th class="px-5 py-3">Gateway</th>
                            <th class="px-5 py-3">Timestamp</th>
                            <th class="px-5 py-3 text-right">Settled Amount</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($invoice->transactions as $txn)
                            <tr>
                                <td class="px-5 py-3 font-mono text-slate-800 dark:text-slate-200">{{ $txn->transaction_id ?? $txn->id }}</td>
                                <td class="px-5 py-3 uppercase text-[11px] font-semibold">{{ $txn->gateway }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-5 py-3 text-right font-bold tabular-nums text-slate-900 dark:text-white">
                                    <x-price-display :amountUsd="$txn->amount" :showBdt="false" />
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Success
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
