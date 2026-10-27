@extends('layouts.app', ['title' => 'Invoice #' . ($invoice->invoice_number ?? $invoice->id), 'header' => 'Invoice Details'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Action Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('client.invoices.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Invoices</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" type="button" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print / PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Invoice Document Container -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-8 sm:p-10 shadow-xs print:shadow-none print:border-none print:p-0">
        <!-- Invoice Header: Logo & Company Info Left, Meta Right -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-8 border-b border-slate-200 dark:border-slate-800">
            <div>
                <div>
                    <x-brand-logo class="h-8 w-auto" :show-text="false" />
                </div>
                <div class="mt-3 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ \App\Models\SystemSetting::get('company_name', 'HostNibo Technologies Ltd.') }}</p>
                    <p>Enterprise Wholesale Domain Hub & Cloud Services</p>
                    <p>BIN / VAT: 004819264-0101 • Dhaka, Bangladesh</p>
                    <p>Support: {{ \App\Models\SystemSetting::get('support_email', 'support@hostnibo.com') }} • WhatsApp: {{ \App\Models\SystemSetting::get('support_phone', '+880 1849-693630') }}</p>
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1">
                <span class="text-xs uppercase font-mono tracking-wider text-slate-400">Invoice Number</span>
                <h1 class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
                    #{{ $invoice->invoice_number ?? $invoice->id }}
                </h1>
                <div class="pt-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $invoice->isPaid() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200' }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Meta Details: Invoiced To & Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-200 dark:border-slate-800 text-xs">
            <div>
                <span class="text-slate-400 uppercase tracking-wider block font-medium mb-1">Invoiced To</span>
                <p class="font-bold text-sm text-slate-900 dark:text-white">{{ $invoice->user->name }}</p>
                @if($invoice->user->company_name)
                    <p class="text-slate-600 dark:text-slate-400">{{ $invoice->user->company_name }}</p>
                @endif
                <p class="text-slate-500 font-mono">{{ $invoice->user->email }}</p>
                @if($invoice->user->address)
                    <p class="text-slate-500 mt-1">{{ $invoice->user->address }}, {{ $invoice->user->city }} {{ $invoice->user->country }}</p>
                @endif
            </div>

            <div class="space-y-2 sm:text-right">
                <div>
                    <span class="text-slate-400 block">Invoice Date</span>
                    <span class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $invoice->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Due Date</span>
                    <span class="font-mono font-medium text-slate-800 dark:text-slate-200">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</span>
                </div>
                @if($invoice->paid_at)
                    <div>
                        <span class="text-slate-400 block">Date Paid</span>
                        <span class="font-mono font-medium text-emerald-600">{{ $invoice->paid_at->format('M d, Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-6 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800 pb-2">
                    <tr>
                        <th class="py-2.5 font-medium">Description</th>
                        <th class="py-2.5 text-center font-medium">Qty</th>
                        <th class="py-2.5 text-right font-medium">Unit Price</th>
                        <th class="py-2.5 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    @forelse($invoice->items as $item)
                        <tr>
                            <td class="py-3.5 pr-4">
                                <span class="font-semibold text-slate-900 dark:text-white text-sm block">{{ $item->description }}</span>
                            </td>
                            <td class="py-3.5 text-center font-mono">{{ $item->quantity }}</td>
                            <td class="py-3.5 text-right font-mono tabular-nums">
                                <x-price-display :amountUsd="$item->unit_price" :showBdt="false" />
                            </td>
                            <td class="py-3.5 text-right font-mono font-semibold text-slate-900 dark:text-white tabular-nums">
                                <x-price-display :amountUsd="$item->total_price" :showBdt="false" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-3.5" colspan="4">Account Provisioning / Balance Deposit</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Calculation -->
        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
            <div class="w-full sm:w-64 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-mono tabular-nums font-semibold text-slate-800 dark:text-slate-200">
                        <x-price-display :amountUsd="$invoice->subtotal" :showBdt="false" />
                    </span>
                </div>
                @if($invoice->tax_amount > 0)
                    <div class="flex justify-between text-slate-500">
                        <span>VAT / Tax ({{ $invoice->tax_percentage ?? 0 }}%):</span>
                        <span class="font-mono tabular-nums font-semibold text-slate-800 dark:text-slate-200">
                            <x-price-display :amountUsd="$invoice->tax_amount" :showBdt="false" />
                        </span>
                    </div>
                @endif
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Discount:</span>
                        <span class="font-mono tabular-nums font-semibold">-<x-price-display :amountUsd="$invoice->discount_amount" :showBdt="false" /></span>
                    </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-800 text-sm font-bold text-slate-900 dark:text-white">
                    <span>Total Due:</span>
                    <div class="text-right">
                        <span class="font-mono tabular-nums text-base text-[#F6821F]">
                            <x-price-display :amountUsd="$invoice->balance_due" :showBdt="true" />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Section (When Invoice is Unpaid) -->
    @if(!$invoice->isPaid() && $invoice->balance_due > 0)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs no-print">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Settle Invoice</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Select your payment method below to clear this invoice immediately.</p>

            <form action="{{ route('client.invoices.pay', $invoice->id) }}" method="POST" x-data="{ selectedGateway: 'balance' }">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                    <!-- Option 1: Wallet Balance -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'balance' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="balance" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Account Credit</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'balance' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
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

                    <!-- Option 2: bKash Personal -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'bkash_personal' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="bkash_personal" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">bKash Personal</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'bkash_personal' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'bkash_personal'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Send Money & TrxID confirm</span>
                    </label>

                    <!-- Option 3: UddoktaPay -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'uddoktapay' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="uddoktapay" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">UddoktaPay</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'uddoktapay' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'uddoktapay'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">bKash, Nagad, Rocket, Upay</span>
                    </label>

                    <!-- Option 4: Binance Personal (Pay ID) -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'binance_personal' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="binance_personal" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Binance Pay (Personal)</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'binance_personal' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'binance_personal'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Direct Pay ID transfer (0% fee)</span>
                    </label>

                    <!-- Option 5: Binance Pay Merchant -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'binance' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="binance" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Binance Pay (Merchant)</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'binance' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'binance'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Official QR & App checkout</span>
                    </label>

                    <!-- Option 6: SSLCommerz -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'sslcommerz' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="sslcommerz" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">SSLCommerz</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'sslcommerz' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'sslcommerz'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Cards, MFS, Net Banking</span>
                    </label>

                    <!-- Option 7: PipraPay -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'piprapay' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="piprapay" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">PipraPay</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'piprapay' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'piprapay'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Multi-channel checkout</span>
                    </label>

                    <!-- Option 8: bKash Tokenized -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'bkash' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="bkash" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">bKash Tokenized</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'bkash' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'bkash'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Official PGW checkout</span>
                    </label>

                    <!-- Option 9: Stripe -->
                    <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                        :class="selectedGateway === 'stripe' 
                            ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                        <input type="radio" name="gateway" value="stripe" x-model="selectedGateway" class="sr-only">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-semibold text-xs text-slate-900 dark:text-white">Credit / Debit Cards</span>
                            <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                 :class="selectedGateway === 'stripe' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedGateway === 'stripe'"></div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500">Visa, Mastercard, Amex</span>
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
</div>
@endsection
