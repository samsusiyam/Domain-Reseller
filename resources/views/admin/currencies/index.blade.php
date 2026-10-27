@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    editModalOpen: false,
    editingCurrency: {
        id: '',
        code: '',
        name: '',
        symbol: '',
        exchange_rate: '',
        decimal_places: 2,
        symbol_position: 'before',
        is_active: true
    },
    openEdit(curr) {
        this.editingCurrency = {
            id: curr.id,
            code: curr.code,
            name: curr.name,
            symbol: curr.symbol,
            exchange_rate: curr.exchange_rate,
            decimal_places: curr.decimal_places,
            symbol_position: curr.symbol_position,
            is_active: !!curr.is_active
        };
        this.editModalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Currencies & FX Exchange Rates</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage base currency (USD) and conversion rates for secondary currencies (BDT, etc.). Rates can be edited manually or synced live.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.settings.currencies.update-rates') }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Fetch Live FX Rates
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-medium">
        <a href="{{ route('admin.settings.general') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">General & Branding</a>
        <a href="{{ route('admin.settings.email') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Email & SMTP</a>
        <a href="{{ route('admin.settings.security') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Security & 2FA</a>
        <a href="{{ route('admin.settings.currencies') }}" class="px-3.5 py-2 text-[#F6821F] border-b-2 border-[#F6821F] font-semibold">Currencies & FX Rates</a>
        <a href="{{ route('admin.settings.gateways') }}" class="px-3.5 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">Payment Gateways</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Currency Form -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm sticky top-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-0.5">Add Currency</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Add a new currency code with custom conversion multiplier.</p>

                <form action="{{ route('admin.settings.currencies.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">ISO Code (3-Letters)</label>
                        <input type="text" name="code" placeholder="BDT" maxlength="3" required class="cf-input h-9 text-xs font-mono uppercase">
                        @error('code') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Currency Name</label>
                        <input type="text" name="name" placeholder="Bangladeshi Taka" required class="cf-input h-9 text-xs">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Symbol</label>
                            <input type="text" name="symbol" placeholder="৳" required class="cf-input h-9 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Decimals</label>
                            <input type="number" name="decimal_places" value="2" min="0" max="4" required class="cf-input h-9 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Exchange Rate (per 1 USD)</label>
                        <input type="number" step="0.000001" name="exchange_rate" placeholder="123.50" required class="cf-input h-9 text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Symbol Position</label>
                        <select name="symbol_position" class="cf-input h-9 text-xs">
                            <option value="before">Before Amount ($10.00)</option>
                            <option value="after">After Amount (10.00৳)</option>
                        </select>
                    </div>

                    <button type="submit" class="cf-btn cf-btn-primary w-full h-9 text-xs font-medium flex items-center justify-center gap-1.5 mt-2 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Currency
                    </button>
                </form>
            </div>
        </div>

        <!-- Currencies Table -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Active Currencies</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Click "Edit Rate" on any row to change the exchange rate manually.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                            <tr>
                                <th class="px-4 py-3">Code</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3 text-center">Symbol</th>
                                <th class="px-4 py-3 text-right">Rate (1 USD =)</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            @foreach($currencies as $curr)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="px-4 py-3 font-mono font-semibold text-slate-900 dark:text-white">
                                        {{ $curr->code }}
                                        @if($curr->is_primary)
                                            <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 uppercase">Primary</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $curr->name }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-white">{{ $curr->symbol }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        {{ number_format($curr->exchange_rate, 4) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            {{ $curr->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                            {{ $curr->is_active ? 'Active' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button"
                                                @click="openEdit({{ json_encode($curr) }})"
                                                class="px-2.5 py-1 text-xs font-medium text-[#F6821F] hover:bg-orange-50 dark:hover:bg-orange-950/30 rounded border border-orange-200 dark:border-orange-900/50 transition cursor-pointer">
                                            Edit Rate
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Currency Modal -->
    <div x-show="editModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                 @click="editModalOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white" id="modal-title">
                            Edit Currency: <span class="text-[#F6821F] font-mono" x-text="editingCurrency.code"></span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manually change exchange rate or display format.</p>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'{{ url('admin/settings/currencies') }}/' + editingCurrency.id" method="POST" class="space-y-4 mt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Exchange Rate (per 1 USD)</label>
                        <input type="number" step="0.000001" name="exchange_rate" x-model="editingCurrency.exchange_rate" required class="cf-input h-10 text-sm font-mono font-bold">
                        <p class="text-[11px] text-slate-400 mt-1">Example: For BDT, enter 123.50 (meaning 1 USD = 123.50 ৳)</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Currency Name</label>
                            <input type="text" name="name" x-model="editingCurrency.name" required class="cf-input h-9 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Symbol</label>
                            <input type="text" name="symbol" x-model="editingCurrency.symbol" required class="cf-input h-9 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Decimal Places</label>
                            <input type="number" name="decimal_places" x-model="editingCurrency.decimal_places" min="0" max="4" required class="cf-input h-9 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Symbol Position</label>
                            <select name="symbol_position" x-model="editingCurrency.symbol_position" class="cf-input h-9 text-xs">
                                <option value="before">Before ($10)</option>
                                <option value="after">After (10৳)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" :checked="editingCurrency.is_active" class="rounded border-slate-300 accent-[#F6821F]">
                        <label for="edit_is_active" class="text-xs text-slate-700 dark:text-slate-300">Active for customer checkout & storefront currency toggle</label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="editModalOpen = false" class="cf-btn cf-btn-secondary text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="cf-btn cf-btn-primary text-xs">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
