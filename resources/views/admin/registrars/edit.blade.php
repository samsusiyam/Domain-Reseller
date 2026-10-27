@extends('layouts.admin', ['title' => 'Configure ' . $registrar->name . ' Module', 'header' => 'Registrar Configuration'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.registrars.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Configure {{ $registrar->name }} Module</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage upstream API credentials, authentication tokens, and environment mode.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-xs border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
        <form action="{{ route('admin.registrars.update', $registrar->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Environment Mode
                    </label>
                    <select name="environment" required class="cf-input w-full font-semibold">
                        <option value="production" {{ old('environment', $registrar->environment) === 'production' ? 'selected' : '' }}>Production (Live Registering)</option>
                        <option value="sandbox" {{ old('environment', $registrar->environment) === 'sandbox' ? 'selected' : '' }}>Sandbox / Testbed (OT&E)</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $registrar->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        Enable this Registrar Module
                    </label>
                </div>
            </div>

            <!-- API Credentials Specific Fields -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">
                    API Authentication Credentials
                </h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php $config = (array) ($registrar->config ?? []); @endphp

                    @if($registrar->slug === 'namecheap')
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Username</label>
                            <input type="text" name="config[api_user]" value="{{ old('config.api_user', $config['api_user'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Username (Account)</label>
                            <input type="text" name="config[username]" value="{{ old('config.username', $config['username'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Whitelisted Client IP</label>
                            <input type="text" name="config[client_ip]" value="{{ old('config.client_ip', $config['client_ip'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @elseif($registrar->slug === 'spaceship')
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Secret</label>
                            <input type="password" name="config[api_secret]" value="{{ old('config.api_secret', $config['api_secret'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @elseif($registrar->slug === 'dynadot')
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Secret Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @elseif($registrar->slug === 'cosmotown')
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Cosmotown API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Cosmotown API Secret</label>
                            <input type="password" name="config[api_secret]" value="{{ old('config.api_secret', $config['api_secret'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @elseif($registrar->slug === 'unstoppabledomains')
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Unstoppable API Key (Bearer Token)</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Reseller ID / Partner Identifier</label>
                            <input type="text" name="config[reseller_id]" value="{{ old('config.reseller_id', $config['reseller_id'] ?? 'hostnibo') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @elseif($registrar->slug === 'namesilo')
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Key</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Key / Token</label>
                            <input type="password" name="config[api_key]" value="{{ old('config.api_key', $config['api_key'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">API Secret (If required)</label>
                            <input type="password" name="config[api_secret]" value="{{ old('config.api_secret', $config['api_secret'] ?? '') }}" class="cf-input w-full font-mono text-xs">
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.registrars.index') }}" class="cf-btn cf-btn-secondary text-xs">Cancel</a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Registrar Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
