@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tlds.index') }}" class="text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Configure New TLD Extension</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Setup upstream pricing cost, retail price, and reseller wholesale margins.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.tlds.store') }}" method="POST" class="space-y-6"
            x-data="{
                costReg: {{ (float)old('cost_register', 9.50) }},
                clientReg: {{ (float)old('client_register', 12.00) }},
                resellerReg: {{ (float)old('default_reseller_register', 10.50) }},
                profit(sell, cost) {
                    let p = parseFloat(sell || 0) - parseFloat(cost || 0);
                    return isNaN(p) ? '0.00' : p.toFixed(2);
                },
                margin(sell, cost) {
                    let s = parseFloat(sell || 0);
                    let c = parseFloat(cost || 0);
                    if (isNaN(c) || c <= 0) return '0.0';
                    return (((s - c) / c) * 100).toFixed(1);
                }
            }">
            @csrf

            <!-- Extension & Registrar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">TLD Extension (with dot)</label>
                    <input type="text" name="tld" placeholder=".com" required class="w-full px-3 py-2 font-mono text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('tld') }}">
                    @error('tld') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Upstream Registrar Provider</label>
                    <select name="registrar_id" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        @foreach($registrars as $reg)
                            <option value="{{ $reg->id }}" {{ old('registrar_id') == $reg->id ? 'selected' : '' }}>{{ $reg->name }}</option>
                        @endforeach
                    </select>
                    @error('registrar_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Upstream Cost Section -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">1. Upstream Registrar Raw Cost (USD)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Cost Register</label>
                        <input type="number" step="0.01" name="cost_register" x-model.number="costReg" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('cost_register', '9.50') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Cost Renew</label>
                        <input type="number" step="0.01" name="cost_renew" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('cost_renew', '9.50') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Cost Transfer</label>
                        <input type="number" step="0.01" name="cost_transfer" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('cost_transfer', '9.50') }}">
                    </div>
                </div>
            </div>

            <!-- Retail Client Price Section -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">2. Retail Client Price (USD)</h4>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-mono font-bold"
                         :class="profit(clientReg, costReg) >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                        <span>Profit: $<span x-text="profit(clientReg, costReg)"></span></span>
                        <span>(<span x-text="margin(clientReg, costReg)"></span>% Margin)</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Client Register</label>
                        <input type="number" step="0.01" name="client_register" x-model.number="clientReg" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('client_register', '13.99') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Client Renew</label>
                        <input type="number" step="0.01" name="client_renew" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('client_renew', '14.99') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Client Transfer</label>
                        <input type="number" step="0.01" name="client_transfer" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('client_transfer', '13.99') }}">
                    </div>
                </div>
            </div>

            <!-- Reseller Wholesale Price Section -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">3. Reseller Wholesale Baseline (USD)</h4>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-mono font-bold"
                         :class="profit(resellerReg, costReg) >= 0 ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800'">
                        <span>Wholesale Margin: $<span x-text="profit(resellerReg, costReg)"></span></span>
                        <span>(<span x-text="margin(resellerReg, costReg)"></span>%)</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Default Wholesale Register</label>
                        <input type="number" step="0.01" name="default_reseller_register" x-model.number="resellerReg" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('default_reseller_register', '10.50') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Default Wholesale Renew</label>
                        <input type="number" step="0.01" name="default_reseller_renew" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('default_reseller_renew', '10.50') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Default Wholesale Transfer</label>
                        <input type="number" step="0.01" name="default_reseller_transfer" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('default_reseller_transfer', '10.50') }}">
                    </div>
                </div>
            </div>

            <!-- Features & Toggles -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">4. Policies & Addons</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">ID Protection Cost (0 for Free)</label>
                        <input type="number" step="0.01" name="id_protection_cost" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('id_protection_cost', '0.00') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">DNS Management Cost (0 for Free)</label>
                        <input type="number" step="0.01" name="dns_management_cost" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('dns_management_cost', '0.00') }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 dark:text-slate-300 mb-1">Email Forwarding Cost (0 for Free)</label>
                        <input type="number" step="0.01" name="email_forwarding_cost" class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-lg" value="{{ old('email_forwarding_cost', '0.00') }}">
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="dns_management_available" value="1" {{ old('dns_management_available', true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-indigo-600">
                            DNS Zone Management Available
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="email_forwarding_available" value="1" {{ old('email_forwarding_available', true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-indigo-600">
                            Email Forwarding Available
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="epp_code_required" value="1" {{ old('epp_code_required', true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-indigo-600">
                            EPP Auth Code Required for Transfers
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-indigo-600">
                            Extension Active for Registration
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.tlds.index') }}" class="px-5 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-800/50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save TLD Extension
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
