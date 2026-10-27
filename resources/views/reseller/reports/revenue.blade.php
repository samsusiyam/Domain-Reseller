@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Expenditure & Financial Summary</h1>
            <p class="text-sm text-slate-500 mt-1">Review your wholesale capital deposits, monthly domain purchasing spend, and balance consumption.</p>
        </div>
        <div>
            <a href="{{ route('client.wallet.index') }}" class="inline-flex items-center px-4 py-2 cf-btn cf-btn-primary text-white font-semibold rounded-xl shadow-sm transition text-xs">
                + Add Funds to Wallet
            </a>
        </div>
    </div>

    <!-- Financial Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Total Deposits Credited</span>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">
                <x-price-display :amountUsd="$totalDeposited" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Lifetime funds added to account</p>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Total Wholesale Spent</span>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">
                <x-price-display :amountUsd="$totalSpent" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Lifetime registrations & renewals</p>
        </div>

        <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#F6821F]">Current Wallet Balance</span>
            <div class="text-3xl font-black text-[#F6821F] mt-2">
                <x-price-display :amountUsd="auth()->user()->walletBalance" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Available for automatic provisioning</p>
        </div>
    </div>

    <!-- Monthly Spend Table -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Monthly Expenditure (Last 12 Months)</h3>
        </div>

        @if($monthlySpend->isEmpty())
            <div class="text-center py-12 px-4">
                <p class="text-sm text-slate-500">No monthly expenditures recorded yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Billing Month</th>
                            <th class="px-6 py-3.5 text-right">Total Domain Spend (USD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($monthlySpend as $row)
                            <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('F Y') }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white">
                                    <x-price-display :amountUsd="$row->total" />
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
