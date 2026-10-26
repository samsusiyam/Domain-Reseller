@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.tlds.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit TLD Extension: {{ $tld->tld }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Modify upstream registrar pricing, retail client rates, and wholesale margins.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.tlds.update', $tld->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Extension & Registrar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">TLD Extension (with dot)</label>
                    <input type="text" name="tld" required class="w-full px-3 py-2 font-mono text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('tld', $tld->tld) }}">
                    @error('tld') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Upstream Registrar Provider</label>
                    <select name="registrar_id" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        @foreach($registrars as $reg)
                            <option value="{{ $reg->id }}" {{ old('registrar_id', $tld->registrar_id) == $reg->id ? 'selected' : '' }}>{{ $reg->name }}</option>
                        @endforeach
                    </select>
                    @error('registrar_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Upstream Cost Section -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">1. Upstream Registrar Raw Cost (USD)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Cost Register</label>
                        <input type="number" step="0.01" name="cost_register" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('cost_register', $tld->cost_register) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Cost Renew</label>
                        <input type="number" step="0.01" name="cost_renew" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('cost_renew', $tld->cost_renew) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Cost Transfer</label>
                        <input type="number" step="0.01" name="cost_transfer" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('cost_transfer', $tld->cost_transfer) }}">
                    </div>
                </div>
            </div>

            <!-- Retail Client Price Section -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">2. Retail Client Price (USD)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Client Register</label>
                        <input type="number" step="0.01" name="client_register" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('client_register', $tld->client_register) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Client Renew</label>
                        <input type="number" step="0.01" name="client_renew" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('client_renew', $tld->client_renew) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Client Transfer</label>
                        <input type="number" step="0.01" name="client_transfer" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('client_transfer', $tld->client_transfer) }}">
                    </div>
                </div>
            </div>

            <!-- Reseller Wholesale Price Section -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">3. Reseller Wholesale Baseline (USD)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Default Wholesale Register</label>
                        <input type="number" step="0.01" name="default_reseller_register" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('default_reseller_register', $tld->default_reseller_register) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Default Wholesale Renew</label>
                        <input type="number" step="0.01" name="default_reseller_renew" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('default_reseller_renew', $tld->default_reseller_renew) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Default Wholesale Transfer</label>
                        <input type="number" step="0.01" name="default_reseller_transfer" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('default_reseller_transfer', $tld->default_reseller_transfer) }}">
                    </div>
                </div>
            </div>

            <!-- Features & Toggles -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">4. Policies & Addons</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">ID Protection Cost (0 for Free)</label>
                        <input type="number" step="0.01" name="id_protection_cost" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg" value="{{ old('id_protection_cost', $tld->id_protection_cost) }}">
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="checkbox" name="dns_management_available" value="1" {{ old('dns_management_available', $tld->dns_management_available) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                            DNS Zone Management Available
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="checkbox" name="email_forwarding_available" value="1" {{ old('email_forwarding_available', $tld->email_forwarding_available) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                            Email Forwarding Available
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="checkbox" name="epp_code_required" value="1" {{ old('epp_code_required', $tld->epp_code_required) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                            EPP Auth Code Required for Transfers
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tld->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                            Extension Active for Registration
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.tlds.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Update TLD Extension
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
