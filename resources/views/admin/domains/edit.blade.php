@extends('layouts.admin', ['title' => 'Edit ' . $domain->domain_name, 'header' => 'Edit Domain'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.domains.show', $domain->id) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:text-slate-300 dark:hover:text-slate-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Domain: {{ $domain->domain_name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Administrative override of registration records, upstream registrar assignments and transfer secret.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.domains.update', $domain->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Domain Status</label>
                    <select name="status" class="cf-input h-10 text-xs font-semibold w-full">
                        <option value="active" {{ old('status', $domain->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ old('status', $domain->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="pending_transfer" {{ old('status', $domain->status) === 'pending_transfer' ? 'selected' : '' }}>Pending Transfer</option>
                        <option value="cancelled" {{ old('status', $domain->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="fraud" {{ old('status', $domain->status) === 'fraud' ? 'selected' : '' }}>Fraud Suspended</option>
                    </select>
                    @error('status') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Upstream Registrar Driver</label>
                    <select name="registrar_id" class="cf-input h-10 text-xs font-semibold w-full">
                        @foreach($registrars as $reg)
                            <option value="{{ $reg->id }}" {{ old('registrar_id', $domain->registrar_id) == $reg->id ? 'selected' : '' }}>{{ $reg->name }}</option>
                        @endforeach
                    </select>
                    @error('registrar_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Registration Date</label>
                    <input type="date" name="registration_date" required class="cf-input h-10 text-xs font-medium w-full"
                        value="{{ old('registration_date', $domain->registration_date ? $domain->registration_date->format('Y-m-d') : ($domain->registered_at ? $domain->registered_at->format('Y-m-d') : '')) }}">
                    @error('registration_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Expiration Date</label>
                    <input type="date" name="expires_at" required class="cf-input h-10 text-xs font-medium w-full"
                        value="{{ old('expires_at', $domain->expires_at ? $domain->expires_at->format('Y-m-d') : '') }}">
                    @error('expires_at') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Transfer Lock Status</label>
                    <select name="is_locked" class="cf-input h-10 text-xs font-medium w-full">
                        <option value="1" {{ old('is_locked', $domain->is_locked) ? 'selected' : '' }}>🔒 Locked (Transfer Protection ON)</option>
                        <option value="0" {{ !old('is_locked', $domain->is_locked) ? 'selected' : '' }}>🔓 Unlocked (Allowed to Transfer Out)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">WHOIS ID Protection</label>
                    <select name="is_private" class="cf-input h-10 text-xs font-medium w-full">
                        <option value="1" {{ old('is_private', $domain->is_private) ? 'selected' : '' }}>🛡️ Enabled (Private WHOIS)</option>
                        <option value="0" {{ !old('is_private', $domain->is_private) ? 'selected' : '' }}>Disabled (Public WHOIS)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Automatic Renewal</label>
                    <select name="auto_renew" class="cf-input h-10 text-xs font-medium w-full">
                        <option value="1" {{ old('auto_renew', $domain->auto_renew || $domain->is_auto_renew) ? 'selected' : '' }}>Auto-Renew Enabled</option>
                        <option value="0" {{ !old('auto_renew', $domain->auto_renew || $domain->is_auto_renew) ? 'selected' : '' }}>Auto-Renew Disabled</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Auth / EPP Transfer Code</label>
                    <input type="text" name="auth_code" value="{{ old('auth_code', $domain->auth_code) }}" placeholder="e.g. 7S9JZ7P60... (Official upstream code from Dynadot)"
                           class="cf-input h-10 font-mono text-xs w-full">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Official authorization secret code. When entered, clients can safely fetch this code for outbound migration.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.domains.show', $domain->id) }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs shadow-xs cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
