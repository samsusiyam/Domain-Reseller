@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.domains.show', $domain->id) }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Domain: {{ $domain->domain_name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Administrative override of registration records and upstream registrar assignments.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.domains.update', $domain->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Domain Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        <option value="active" {{ old('status', $domain->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ old('status', $domain->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="pending_transfer" {{ old('status', $domain->status) === 'pending_transfer' ? 'selected' : '' }}>Pending Transfer</option>
                        <option value="cancelled" {{ old('status', $domain->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="fraud" {{ old('status', $domain->status) === 'fraud' ? 'selected' : '' }}>Fraud Suspended</option>
                    </select>
                    @error('status') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Upstream Registrar Module</label>
                    <select name="registrar_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        @foreach($registrars as $reg)
                            <option value="{{ $reg->id }}" {{ old('registrar_id', $domain->registrar_id) == $reg->id ? 'selected' : '' }}>{{ $reg->name }}</option>
                        @endforeach
                    </select>
                    @error('registrar_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Registration Date</label>
                    <input type="date" name="registration_date" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        value="{{ old('registration_date', $domain->registration_date ? $domain->registration_date->format('Y-m-d') : '') }}">
                    @error('registration_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expiration Date</label>
                    <input type="date" name="expires_at" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        value="{{ old('expires_at', $domain->expires_at ? $domain->expires_at->format('Y-m-d') : '') }}">
                    @error('expires_at') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Transfer Lock Status</label>
                    <select name="is_locked" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="1" {{ old('is_locked', $domain->is_locked) ? 'selected' : '' }}>Locked (Recommended)</option>
                        <option value="0" {{ !old('is_locked', $domain->is_locked) ? 'selected' : '' }}>Unlocked</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">WHOIS ID Protection</label>
                    <select name="is_private" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="1" {{ old('is_private', $domain->is_private) ? 'selected' : '' }}>Enabled (Private WHOIS)</option>
                        <option value="0" {{ !old('is_private', $domain->is_private) ? 'selected' : '' }}>Disabled (Public WHOIS)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Automatic Renewal</label>
                    <select name="auto_renew" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="1" {{ old('auto_renew', $domain->auto_renew) ? 'selected' : '' }}>Auto-Renew Enabled</option>
                        <option value="0" {{ !old('auto_renew', $domain->auto_renew) ? 'selected' : '' }}>Auto-Renew Disabled</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.domains.show', $domain->id) }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
