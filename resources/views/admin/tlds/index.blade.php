@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    search: '',
    marginModalOpen: false,
    marginType: 'fixed',
    clientMargin: 1.50,
    resellerMargin: 0.50
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">TLD Extensions & Pricing Matrix</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage wholesale registry costs, retail markups, reseller wholesale rates, and registrar routing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Sync Wholesale Costs Button -->
            <form action="{{ route('admin.tlds.sync-costs') }}" method="POST">
                @csrf
                <button type="submit" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Sync Wholesale Costs</span>
                </button>
            </form>

            <!-- Profit Margins Modal Trigger -->
            <button type="button" @click="marginModalOpen = true" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#F6821F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Bulk Margin Rules</span>
            </button>

            <!-- Add TLD Extension -->
            <a href="{{ route('admin.tlds.create') }}" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-medium inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Extension</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-4 flex items-center justify-between gap-4 shadow-xs">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" x-model="search" placeholder="Search TLD (e.g. .com, .net, .org)..."
                class="cf-input h-9 pl-9 text-xs">
        </div>
        <div class="text-xs text-slate-500 dark:text-slate-400">
            Total Extensions: <strong class="text-slate-800 dark:text-white font-mono">{{ $tlds->count() }}</strong>
        </div>
    </div>

    <!-- TLDs Table -->
    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                    <tr>
                        <th class="px-5 py-3.5">TLD Extension</th>
                        <th class="px-5 py-3.5">Upstream Registrar</th>
                        <th class="px-5 py-3.5 text-right">Wholesale Cost (Reg/Ren)</th>
                        <th class="px-5 py-3.5 text-right">Retail Client (Reg/Ren)</th>
                        <th class="px-5 py-3.5 text-right">Reseller Tier (Reg/Ren)</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($tlds as $tld)
                        <tr class="hover:bg-slate-50 dark:bg-slate-800/50/50 dark:hover:bg-slate-800/40 transition" x-show="!search || '{{ strtolower($tld->tld) }}'.includes(search.toLowerCase())">
                            <td class="px-5 py-4 font-mono font-bold text-sm text-slate-900 dark:text-white">
                                {{ $tld->tld }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $tld->registrar?->name ?? 'Default' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-mono tabular-nums text-slate-600 dark:text-slate-400">
                                ${{ number_format((float)$tld->cost_register, 2) }} / ${{ number_format((float)$tld->cost_renew, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono tabular-nums font-semibold text-slate-900 dark:text-white">
                                ${{ number_format((float)$tld->client_register, 2) }} / ${{ number_format((float)$tld->client_renew, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono tabular-nums font-semibold text-[#F6821F]">
                                ${{ number_format((float)$tld->default_reseller_register, 2) }} / ${{ number_format((float)$tld->default_reseller_renew, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                    {{ $tld->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:text-slate-400 dark:bg-slate-800' }}">
                                    {{ $tld->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.tlds.edit', $tld->id) }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-medium cursor-pointer">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bulk Profit Margin Engine Modal -->
    <div x-show="marginModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="marginModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Auto Profit Margin Engine</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Calculate retail & reseller prices from wholesale costs.</p>
                    </div>
                    <button type="button" @click="marginModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('admin.tlds.apply-margins') }}" method="POST" class="space-y-4 mt-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Calculation Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer text-xs font-medium"
                                   :class="marginType === 'fixed' ? 'border-[#F6821F] bg-orange-50/40 dark:bg-orange-950/20 text-[#F6821F]' : 'border-slate-200 dark:border-slate-800'">
                                <input type="radio" name="margin_type" value="fixed" x-model="marginType" class="accent-[#F6821F]">
                                <span>Fixed Markup ($)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer text-xs font-medium"
                                   :class="marginType === 'percentage' ? 'border-[#F6821F] bg-orange-50/40 dark:bg-orange-950/20 text-[#F6821F]' : 'border-slate-200 dark:border-slate-800'">
                                <input type="radio" name="margin_type" value="percentage" x-model="marginType" class="accent-[#F6821F]">
                                <span>Percentage Markup (%)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Retail Client Margin <span x-text="marginType === 'fixed' ? '(Add $ onto Cost)' : '(Add % onto Cost)'"></span>
                        </label>
                        <input type="number" step="0.01" name="client_margin" x-model="clientMargin" required class="cf-input h-9 text-xs font-mono font-bold">
                        <p class="text-[11px] text-slate-400 mt-1">Example: 1.50 means Retail Price = Cost + $1.50</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Reseller Wholesale Margin <span x-text="marginType === 'fixed' ? '(Add $ onto Cost)' : '(Add % onto Cost)'"></span>
                        </label>
                        <input type="number" step="0.01" name="reseller_margin" x-model="resellerMargin" required class="cf-input h-9 text-xs font-mono font-bold">
                        <p class="text-[11px] text-slate-400 mt-1">Example: 0.50 means Reseller Price = Cost + $0.50</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="marginModalOpen = false" class="cf-btn cf-btn-secondary text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="cf-btn cf-btn-primary text-xs font-semibold cursor-pointer">
                            Calculate & Apply Margins
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
