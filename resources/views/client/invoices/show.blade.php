@extends('layouts.app')

@section('title', 'Invoice #' . ($invoice->invoice_number ?? $invoice->id))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('client.invoices.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Invoices
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>
    </div>

    <!-- Invoice Sheet -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12 print:shadow-none print:border-none print:p-0">
        <!-- Invoice Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 border-b border-slate-200 pb-8">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-indigo-700 flex items-center justify-center text-white font-bold text-xl shadow-md">
                        D
                    </div>
                    <span class="text-2xl font-black tracking-tight text-slate-900">Domain<span class="text-brand-600">Reseller</span></span>
                </div>
                <div class="mt-4 text-xs text-slate-500 space-y-1">
                    <p class="font-medium text-slate-700">Domain Reseller Platform Inc.</p>
                    <p>Automated Domain & Cloud Provisioning System</p>
                    <p>support@domainreseller.com</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">INVOICE</h2>
                <div class="mt-2 text-sm text-slate-600 space-y-1">
                    <p><span class="font-semibold text-slate-800">Invoice Number:</span> #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p><span class="font-semibold text-slate-800">Invoice Date:</span> {{ $invoice->created_at->format('M d, Y') }}</p>
                    <p><span class="font-semibold text-slate-800">Due Date:</span> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</p>
                </div>

                <div class="mt-4">
                    @if($invoice->status === 'paid')
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            PAID
                        </span>
                    @elseif($invoice->status === 'unpaid')
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            UNPAID
                        </span>
                    @elseif($invoice->status === 'cancelled')
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            CANCELLED
                        </span>
                    @elseif($invoice->status === 'refunded')
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-purple-100 text-purple-800 border border-purple-200">
                            REFUNDED
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Billed To / Notes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-200">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Billed To</h3>
                <div class="text-sm text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900 text-base">{{ $invoice->user->name }}</p>
                    @if($invoice->user->company_name)
                        <p class="font-medium text-slate-800">{{ $invoice->user->company_name }}</p>
                    @endif
                    <p>{{ $invoice->user->email }}</p>
                    @if($invoice->user->phone)
                        <p>{{ $invoice->user->phone }}</p>
                    @endif
                    @if($invoice->user->address_line1)
                        <p>{{ $invoice->user->address_line1 }}{{ $invoice->user->city ? ', ' . $invoice->user->city : '' }}</p>
                        <p>{{ $invoice->user->country }}</p>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Payment Details</h3>
                <div class="text-sm text-slate-600 space-y-1 bg-slate-50 p-4 rounded-xl border border-slate-200/60">
                    <p><span class="font-semibold text-slate-700">Currency:</span> {{ $invoice->currency ?? 'USD' }}</p>
                    @if($invoice->paid_at)
                        <p><span class="font-semibold text-slate-700">Paid At:</span> {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                    @endif
                    @if($invoice->notes)
                        <p class="mt-2 text-xs text-slate-500 italic">{{ $invoice->notes }}</p>
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
                            <td class="py-4">
                                <p class="font-semibold text-slate-800">{{ $item->description }}</p>
                                @if($item->item_type)
                                    <span class="text-xs text-slate-400 uppercase tracking-wider">{{ $item->item_type }}</span>
                                @endif
                            </td>
                            <td class="py-4 text-center text-slate-600">{{ $item->quantity }}</td>
                            <td class="py-4 text-right text-slate-600">
                                <x-price-display :amountUsd="$item->unit_price" />
                            </td>
                            <td class="py-4 text-right font-semibold text-slate-900">
                                <x-price-display :amountUsd="$item->total_price" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-400">General Billing Charge</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="pt-8 flex justify-end">
            <div class="w-full sm:w-80 space-y-3 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-800"><x-price-display :amountUsd="$invoice->subtotal" /></span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Discount:</span>
                        <span class="font-semibold">- <x-price-display :amountUsd="$invoice->discount_amount" /></span>
                    </div>
                @endif
                @if($invoice->tax_amount > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Tax / VAT:</span>
                        <span class="font-semibold text-slate-800"><x-price-display :amountUsd="$invoice->tax_amount" /></span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-bold text-slate-900 pt-3 border-t border-slate-200">
                    <span>Total:</span>
                    <span><x-price-display :amountUsd="$invoice->total_amount" /></span>
                </div>
                <div class="flex justify-between text-base font-bold {{ $invoice->balance_due > 0 ? 'text-amber-600' : 'text-emerald-600' }} pt-2 border-t border-dashed border-slate-200">
                    <span>Balance Due:</span>
                    <span><x-price-display :amountUsd="$invoice->balance_due" /></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Processing Options (If Unpaid) -->
    @if($invoice->status === 'unpaid' && $invoice->balance_due > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 no-print">
            <h3 class="text-xl font-bold text-slate-900 mb-2">Pay This Invoice</h3>
            <p class="text-sm text-slate-500 mb-6">Select your preferred payment method below to clear this invoice immediately.</p>

            <form action="{{ route('client.invoices.pay', $invoice->id) }}" method="POST" x-data="{ selectedGateway: 'balance' }">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Option 1: Wallet Balance -->
                    <label class="cursor-pointer border rounded-xl p-4 flex flex-col justify-between transition hover:border-brand-500 relative"
                        :class="selectedGateway === 'balance' ? 'border-brand-600 ring-2 ring-brand-50 bg-brand-50/20' : 'border-slate-200'">
                        <input type="radio" name="gateway" value="balance" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-sm text-slate-800">Account Balance</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="selectedGateway === 'balance' ? 'border-brand-600 bg-brand-600' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'balance'"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Available Credit:</p>
                            <p class="text-base font-black {{ auth()->user()->walletBalance >= $invoice->balance_due ? 'text-emerald-600' : 'text-rose-600' }}">
                                <x-price-display :amountUsd="auth()->user()->walletBalance" />
                            </p>
                            @if(auth()->user()->walletBalance < $invoice->balance_due)
                                <p class="text-[11px] text-rose-500 mt-1 font-medium">Insufficient balance</p>
                            @endif
                        </div>
                    </label>

                    <!-- Option 2: bKash -->
                    <label class="cursor-pointer border rounded-xl p-4 flex flex-col justify-between transition hover:border-pink-500 relative"
                        :class="selectedGateway === 'bkash' ? 'border-pink-600 ring-2 ring-pink-50 bg-pink-50/20' : 'border-slate-200'">
                        <input type="radio" name="gateway" value="bkash" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-sm text-pink-600">bKash (BDT)</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="selectedGateway === 'bkash' ? 'border-pink-600 bg-pink-600' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'bkash'"></div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Auto instant checkout via bKash Payment Gateway.</p>
                    </label>

                    <!-- Option 3: Stripe -->
                    <label class="cursor-pointer border rounded-xl p-4 flex flex-col justify-between transition hover:border-indigo-500 relative"
                        :class="selectedGateway === 'stripe' ? 'border-indigo-600 ring-2 ring-indigo-50 bg-indigo-50/20' : 'border-slate-200'">
                        <input type="radio" name="gateway" value="stripe" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-sm text-indigo-600">Credit / Debit Card</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="selectedGateway === 'stripe' ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'stripe'"></div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Visa, Mastercard, Amex via Stripe Checkout.</p>
                    </label>

                    <!-- Option 4: Binance Pay / Crypto -->
                    <label class="cursor-pointer border rounded-xl p-4 flex flex-col justify-between transition hover:border-amber-500 relative"
                        :class="selectedGateway === 'binance' ? 'border-amber-600 ring-2 ring-amber-50 bg-amber-50/20' : 'border-slate-200'">
                        <input type="radio" name="gateway" value="binance" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-sm text-amber-600">Binance Pay</span>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                :class="selectedGateway === 'binance' ? 'border-amber-600 bg-amber-600' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'binance'"></div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Instant USDT, BUSD & Crypto payment.</p>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-500">Transactions are secured and encrypted with 256-bit SSL.</p>
                    <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-2">
                        Pay <x-price-display :amountUsd="$invoice->balance_due" /> Now
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Payment Transactions History -->
    @if($invoice->transactions->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden no-print">
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Recorded Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Transaction ID</th>
                            <th class="px-6 py-3">Gateway</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->transactions as $txn)
                            <tr>
                                <td class="px-6 py-3 font-mono font-medium text-slate-800">{{ $txn->transaction_id ?? $txn->id }}</td>
                                <td class="px-6 py-3 uppercase text-xs font-semibold text-slate-600">{{ $txn->gateway }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-3 text-right font-bold text-slate-900"><x-price-display :amountUsd="$txn->amount" /></td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
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
