@extends('layouts.app')

@section('title', 'Invoices & Billing')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Invoices</h1>
            <p class="text-sm text-slate-500 mt-1">Review your billing history, outstanding charges, and download receipts.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('client.wallet.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                Manage Wallet
            </a>
        </div>
    </div>

    <!-- Filter Status Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('client.invoices.index') }}" 
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ !request('status') ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
               All Invoices
            </a>
            <a href="{{ route('client.invoices.index', ['status' => 'unpaid']) }}" 
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ request('status') === 'unpaid' ? 'bg-amber-500 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
               Unpaid
            </a>
            <a href="{{ route('client.invoices.index', ['status' => 'paid']) }}" 
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ request('status') === 'paid' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
               Paid
            </a>
            <a href="{{ route('client.invoices.index', ['status' => 'cancelled']) }}" 
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ request('status') === 'cancelled' ? 'bg-slate-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
               Cancelled
            </a>
            <a href="{{ route('client.invoices.index', ['status' => 'refunded']) }}" 
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ request('status') === 'refunded' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
               Refunded
            </a>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @if($invoices->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700">No invoices found</h3>
                <p class="text-sm text-slate-500 mt-1">There are no billing records matching your filter criteria.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Invoice #</th>
                            <th class="px-6 py-3.5">Invoice Date</th>
                            <th class="px-6 py-3.5">Due Date</th>
                            <th class="px-6 py-3.5">Total</th>
                            <th class="px-6 py-3.5">Balance Due</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                    <a href="{{ route('client.invoices.show', $inv->id) }}" class="text-brand-600 hover:text-brand-800">
                                        #{{ $inv->invoice_number ?? str_pad($inv->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $inv->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $inv->due_date ? $inv->due_date->format('M d, Y') : 'Due on receipt' }}
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <x-price-display :amountUsd="$inv->total_amount" />
                                </td>
                                <td class="px-6 py-4 font-semibold {{ $inv->balance_due > 0 ? 'text-amber-600' : 'text-slate-500' }}">
                                    <x-price-display :amountUsd="$inv->balance_due" />
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($inv->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Paid</span>
                                    @elseif($inv->status === 'unpaid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Unpaid</span>
                                    @elseif($inv->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Cancelled</span>
                                    @elseif($inv->status === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Refunded</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($inv->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('client.invoices.show', $inv->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition
                                        {{ $inv->status === 'unpaid' ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                        {{ $inv->status === 'unpaid' ? 'Pay Now' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $invoices->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
