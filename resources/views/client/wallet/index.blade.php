@extends('layouts.app')

@section('title', 'Wallet & Funds')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Account Wallet</h1>
            <p class="text-sm text-slate-500 mt-1">Pre-fund your account for instant one-click domain registrations, renewals, and automated transfers.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('client.transactions.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Full Transaction History
            </a>
        </div>
    </div>

    <!-- Balance & Add Funds Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Current Balance Card -->
        <div class="lg:col-span-1 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 text-white shadow-xl flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase font-bold tracking-widest text-indigo-300">Available Balance</span>
                    <span class="p-2 bg-white/10 rounded-lg text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </span>
                </div>
                <div class="mt-6">
                    <div class="text-4xl font-black tracking-tight text-white">
                        <x-price-display :amountUsd="$user->walletBalance" />
                    </div>
                    <p class="text-xs text-indigo-200 mt-2">Ready for automated billing and instant purchases</p>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-white/10 text-xs text-indigo-300 flex items-center justify-between">
                <span>Account ID: #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="capitalize px-2 py-0.5 rounded bg-white/10 text-white font-medium">{{ $user->role }}</span>
            </div>
        </div>

        <!-- Add Funds Form -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8" x-data="{ amount: 25, gateway: 'bkash' }">
            <h3 class="text-lg font-bold text-slate-800 mb-1">Add Balance Credit</h3>
            <p class="text-sm text-slate-500 mb-6">Choose an amount and your payment method. An invoice will be generated and processed automatically.</p>

            <form action="{{ route('client.wallet.add-funds') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Quick Amount Presets -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Deposit Amount (USD)</label>
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        @foreach([10, 25, 50, 100] as $preset)
                            <button type="button" @click="amount = {{ $preset }}"
                                :class="amount == {{ $preset }} ? 'border-brand-600 bg-brand-50 text-brand-700 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                                class="py-2.5 px-3 border rounded-xl text-sm font-semibold transition text-center">
                                ${{ $preset }}
                            </button>
                        @endforeach
                    </div>
                    
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold">$</div>
                        <input type="number" step="0.01" min="{{ auth()->user()->isReseller() ? 10 : 5 }}" name="amount" x-model="amount" required
                            class="block w-full pl-8 pr-12 py-2.5 text-base border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">USD</div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Minimum deposit: {{ auth()->user()->isReseller() ? '$10.00' : '$5.00' }} USD.</p>
                    @error('amount') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Gateway Selection -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Select Payment Gateway</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- bKash -->
                        <label class="cursor-pointer border rounded-xl p-3 flex flex-col justify-between transition hover:border-pink-500"
                            :class="gateway === 'bkash' ? 'border-pink-600 ring-2 ring-pink-50 bg-pink-50/20' : 'border-slate-200'">
                            <input type="radio" name="gateway" value="bkash" x-model="gateway" class="sr-only">
                            <span class="font-bold text-sm text-pink-600">bKash</span>
                            <span class="text-xs text-slate-500 mt-1">Direct BDT Mobile Wallet</span>
                        </label>

                        <!-- Stripe -->
                        <label class="cursor-pointer border rounded-xl p-3 flex flex-col justify-between transition hover:border-indigo-500"
                            :class="gateway === 'stripe' ? 'border-indigo-600 ring-2 ring-indigo-50 bg-indigo-50/20' : 'border-slate-200'">
                            <input type="radio" name="gateway" value="stripe" x-model="gateway" class="sr-only">
                            <span class="font-bold text-sm text-indigo-600">Card / Stripe</span>
                            <span class="text-xs text-slate-500 mt-1">Visa, Mastercard</span>
                        </label>

                        <!-- Binance -->
                        <label class="cursor-pointer border rounded-xl p-3 flex flex-col justify-between transition hover:border-amber-500"
                            :class="gateway === 'binance' ? 'border-amber-600 ring-2 ring-amber-50 bg-amber-50/20' : 'border-slate-200'">
                            <input type="radio" name="gateway" value="binance" x-model="gateway" class="sr-only">
                            <span class="font-bold text-sm text-amber-600">Binance Pay</span>
                            <span class="text-xs text-slate-500 mt-1">Instant Crypto / USDT</span>
                        </label>

                        <!-- Manual -->
                        <label class="cursor-pointer border rounded-xl p-3 flex flex-col justify-between transition hover:border-slate-500"
                            :class="gateway === 'manual' ? 'border-slate-800 ring-2 ring-slate-100 bg-slate-50' : 'border-slate-200'">
                            <input type="radio" name="gateway" value="manual" x-model="gateway" class="sr-only">
                            <span class="font-bold text-sm text-slate-800">Bank / Manual</span>
                            <span class="text-xs text-slate-500 mt-1">Bank transfer / offline</span>
                        </label>
                    </div>
                    @error('gateway') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Continue to Add Funds
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Transactions Preview -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">Recent Wallet Transactions</h3>
            <a href="{{ route('client.transactions.index') }}" class="text-sm text-brand-600 hover:text-brand-800 font-medium">View All &rarr;</a>
        </div>

        @if($transactions->isEmpty())
            <div class="text-center py-12 px-4">
                <p class="text-sm text-slate-500">No transactions recorded yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Transaction ID</th>
                            <th class="px-6 py-3.5">Type</th>
                            <th class="px-6 py-3.5">Description</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-medium text-slate-800">{{ $txn->transaction_id ?? ('TXN-' . $txn->id) }}</td>
                                <td class="px-6 py-4">
                                    @if($txn->type === 'credit' || $txn->amount > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Deposit / Credit</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Debit / Charge</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $txn->description ?? ($txn->gateway . ' transaction') }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 text-right font-bold {{ $txn->type === 'credit' || $txn->amount > 0 ? 'text-emerald-600' : 'text-slate-900' }}">
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
