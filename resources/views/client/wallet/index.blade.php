@extends('layouts.app')

@section('title', 'Wallet & Funds')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">Account Wallet</h1>
            <p class="text-xs text-slate-500 mt-1">Pre-fund your balance for automatic 1-click domain registrations, renewals, and registrar transfers.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('client.transactions.index') }}" class="cf-btn cf-btn-secondary text-xs h-9 px-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>Transaction History</span>
            </a>
        </div>
    </div>

    <!-- Balance & Add Funds Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Current Balance Card (Compact, Minimal, High Contrast) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Available Balance</span>
                    <span class="p-1.5 rounded-lg bg-[#F6821F]/10 text-[#F6821F]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </span>
                </div>
                
                <div class="mt-4">
                    <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">
                        <x-price-display :amountUsd="$user->walletBalance" class="text-slate-900 dark:text-white font-extrabold" />
                    </div>
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Active for instant domain provisioning</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 flex items-center justify-between">
                <span>Account #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="capitalize px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $user->role }}</span>
            </div>
        </div>

        <!-- Right: Add Funds Form -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8" x-data="{ amount: 25, gateway: 'bkash' }">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">Add Funds to Wallet</h3>
            <p class="text-xs text-slate-500 mb-6">Choose an amount and your payment gateway. An invoice is automatically generated and settled.</p>

            <form action="{{ route('client.wallet.add-funds') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Quick Amount Presets -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Deposit Amount (USD)</label>
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        @foreach([10, 25, 50, 100] as $preset)
                            <button type="button" @click="amount = {{ $preset }}"
                                :class="amount == {{ $preset }} 
                                    ? 'border-[#F6821F] bg-[#F6821F]/10 text-[#F6821F] font-bold ring-1 ring-[#F6821F]' 
                                    : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'"
                                class="py-2.5 px-3 border rounded-lg text-xs font-semibold transition text-center cursor-pointer">
                                ${{ $preset }}
                            </button>
                        @endforeach
                    </div>
                    
                    <div class="relative rounded-lg">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">$</div>
                        <input type="number" step="0.01" min="{{ auth()->user()->isReseller() ? 10 : 5 }}" name="amount" x-model="amount" required
                            class="block w-full pl-8 pr-12 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#F6821F] focus:border-[#F6821F] focus:outline-none">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">USD</div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">Minimum deposit: {{ auth()->user()->isReseller() ? '$10.00' : '$5.00' }} USD.</p>
                    @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Gateway Selection (Neutral, Professional, Single Accent) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Payment Method</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <!-- bKash Personal -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'bkash_personal' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="bkash_personal" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">bKash Personal</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'bkash_personal' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'bkash_personal'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Send Money & TrxID verification</span>
                        </label>

                        <!-- UddoktaPay -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'uddoktapay' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="uddoktapay" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">UddoktaPay</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'uddoktapay' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'uddoktapay'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">bKash, Nagad, Rocket, Upay</span>
                        </label>

                        <!-- Binance Personal -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'binance_personal' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="binance_personal" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">Binance Pay (Personal)</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'binance_personal' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'binance_personal'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Direct Pay ID transfer (0% fee)</span>
                        </label>

                        <!-- Binance Merchant -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'binance' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="binance" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">Binance Pay (Merchant)</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'binance' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'binance'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Official QR / Universal App checkout</span>
                        </label>

                        <!-- SSLCommerz -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'sslcommerz' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="sslcommerz" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">SSLCommerz</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'sslcommerz' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'sslcommerz'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Cards, Internet Banking, MFS</span>
                        </label>

                        <!-- PipraPay -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'piprapay' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="piprapay" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">PipraPay</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'piprapay' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'piprapay'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Instant automated gateway</span>
                        </label>

                        <!-- bKash Merchant -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'bkash' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="bkash" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">bKash Tokenized</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'bkash' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'bkash'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Official PGW Checkout</span>
                        </label>

                        <!-- Stripe -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'stripe' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="stripe" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">Card / Stripe</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'stripe' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'stripe'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Visa, Mastercard, Amex</span>
                        </label>

                        <!-- Bank Wire / Manual -->
                        <label class="cursor-pointer border rounded-lg p-3.5 flex flex-col justify-between transition"
                            :class="gateway === 'manual' 
                                ? 'border-[#F6821F] ring-2 ring-[#F6821F]/20 bg-[#F6821F]/5 dark:bg-[#F6821F]/10' 
                                : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600">
                            <input type="radio" name="gateway" value="manual" x-model="gateway" class="sr-only">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-xs text-slate-900 dark:text-white">Bank Wire</span>
                                <div class="w-3.5 h-3.5 rounded-full border flex items-center justify-center"
                                     :class="gateway === 'manual' ? 'border-[#F6821F] bg-[#F6821F]' : 'border-slate-300 dark:border-slate-600'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="gateway === 'manual'"></div>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-500">Offline corporate wire deposit</span>
                        </label>
                    </div>@error('gateway') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Submit CTA Button (Vibrant Orange, 100% visible) -->
                <div>
                    <button type="submit" 
                            class="w-full sm:w-auto h-11 px-8 bg-[#F6821F] hover:bg-[#E07116] active:bg-[#C9620E] text-white font-semibold text-sm rounded-lg shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Continue to Add Funds</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Transactions Preview -->
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Transactions</h3>
            <a href="{{ route('client.transactions.index') }}" class="text-xs font-semibold text-[#F6821F] hover:underline">View All &rarr;</a>
        </div>

        @if($transactions->isEmpty())
            <div class="text-center py-10 px-4">
                <p class="text-xs text-slate-500">No transactions recorded yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3">Transaction ID</th>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3">Description</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3 font-mono font-medium text-slate-800 dark:text-slate-200">{{ $txn->transaction_id ?? ('TXN-' . $txn->id) }}</td>
                                <td class="px-5 py-3">
                                    @if($txn->type === 'credit' || $txn->amount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">Credit</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200 dark:border-red-800">Debit</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-700 dark:text-slate-300">{{ $txn->description ?? ($txn->gateway . ' transaction') }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-5 py-3 text-right font-bold tabular-nums {{ $txn->type === 'credit' || $txn->amount > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ $txn->type === 'credit' || $txn->amount > 0 ? '+' : '' }}<x-price-display :amountUsd="$txn->amount" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
