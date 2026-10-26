@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.registrars.index') }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Configure {{ $registrar->name }} Module</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage upstream API credentials, authentication tokens, and environment mode.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.registrars.update', $registrar->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Environment Mode</label>
                    <select name="environment" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                        <option value="production" {{ old('environment', $registrar->environment) === 'production' ? 'selected' : '' }}>Production (Live Registering)</option>
                        <option value="sandbox" {{ old('environment', $registrar->environment) === 'sandbox' ? 'selected' : '' }}>Sandbox / Testbed (OT&E)</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-800">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $registrar->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                        Enable this Registrar Module
                    </label>
                </div>
            </div>

            <!-- API Credentials Specific Fields -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">API Authentication Credentials</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php $config = (array) ($registrar->config ?? []); @endphp

                    @if($registrar->slug === 'namecheap')
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Username</label>
                            <input type="text" name="config[api_user]" value="{{ old('config.api_user', $config['api_user'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Username (Account)</label>
                            <input type="text" name="config[username]" value="{{ old('config.username', $config['username'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Whitelisted Client IP</label>
                            <input type="text" name="config[client_ip]" value="{{ old('config.client_ip', $config['client_ip'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                    @elseif($registrar->slug === 'spaceship')
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Secret</label>
                            <input type="password" name="config[api_secret]" value="{{ old('config.api_secret', $config['api_secret'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                    @elseif($registrar->slug === 'dynadot')
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Secret Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                    @elseif($registrar->slug === 'namesilo')
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                    @else
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Key / Token</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">API Secret (If required)</label>
                            <input type="password" name="config[api_secret]" value="{{ old('config.api_secret', $config['api_secret'] ?? '') }}" class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg">
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.registrars.index') }}" class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition text-sm">
                    Save Registrar Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
