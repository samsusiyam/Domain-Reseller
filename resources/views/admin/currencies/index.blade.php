@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Currencies & Real-Time FX Rates</h1>
            <p class="text-xs text-slate-500 mt-1">Multi-currency exchange rates table. Core ledger stores USD; secondary currencies (e.g. BDT) convert live.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.settings.currencies.update-rates') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition text-xs inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Fetch Live FX Rates
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-sm font-semibold">
        <a href="{{ route('admin.settings.general') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800">General Settings</a>
        <a href="{{ route('admin.settings.email') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800">Email & SMTP</a>
        <a href="{{ route('admin.settings.security') }}" class="px-4 py-2 text-slate-500 hover:text-slate-800">Security & 2FA</a>
        <a href="{{ route('admin.settings.currencies') }}" class="px-4 py-2 text-indigo-600 border-b-2 border-indigo-600">Currencies & FX Rates</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Currency Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-900 mb-1">Add Currency</h3>
                <p class="text-xs text-slate-500 mb-5">Define ISO 4217 code and fixed or live conversion multiplier.</p>

                <form action="{{ route('admin.settings.currencies.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">ISO Code</label>
                        <input type="text" name="code" placeholder="BDT" maxlength="3" required class="w-full px-3 py-2 font-mono uppercase text-sm border border-slate-300 rounded-lg">
                        @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Currency Name</label>
                        <input type="text" name="name" placeholder="Bangladeshi Taka" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Symbol</label>
                            <input type="text" name="symbol" placeholder="৳" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Decimals</label>
                            <input type="number" name="decimal_places" value="2" min="0" max="4" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Exchange Rate (per 1 USD)</label>
                        <input type="number" step="0.000001" name="exchange_rate" placeholder="120.50" required class="w-full px-3 py-2 font-mono text-sm border border-slate-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Symbol Position</label>
                        <select name="symbol_position" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg">
                            <option value="before">Before Amount ($10.00)</option>
                            <option value="after">After Amount (10.00৳)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Currency
                    </button>
                </form>
            </div>
        </div>

        <!-- Currencies Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Supported Currencies</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Code</th>
                                <th class="px-6 py-3.5">Name</th>
                                <th class="px-6 py-3.5 text-center">Symbol</th>
                                <th class="px-6 py-3.5 text-right">Exchange Rate (1 USD =)</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($currencies as $curr)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                        {{ $curr->code }}
                                        @if($curr->is_primary)
                                            <span class="ml-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase">Primary</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-800">{{ $curr->name }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $curr->symbol }}</td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($curr->exchange_rate, 4) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                            {{ $curr->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $curr->is_active ? 'Active' : 'Disabled' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
