@extends('layouts.admin', ['title' => 'Configure ' . $registrar->name . ' Module', 'header' => 'Registrar Configuration'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.registrars.index') }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Configure {{ $registrar->name }} Module</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium {{ $registrar->environment === 'production' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                        {{ strtoupper($registrar->environment) }}
                    </span>
                    @if($registrar->is_default)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#F6821F]/10 text-[#F6821F] border border-[#F6821F]/20">
                            DEFAULT REGISTRAR
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Isolated driver module located at <code class="font-mono text-[#F6821F]">modules/registers/{{ $registrar->slug }}/</code>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.registrars.test-connection', $registrar->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="cf-btn cf-btn-secondary text-xs inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Test Connection
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-medium flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs font-medium flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-[#111827] rounded-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('admin.registrars.update', $registrar->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Module Core Environment -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <label for="environment" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                        Environment Mode <span class="text-red-500">*</span>
                    </label>
                    <select name="environment" id="environment" required class="cf-input w-full font-semibold">
                        <option value="sandbox" {{ old('environment', $registrar->environment) === 'sandbox' ? 'selected' : '' }}>
                            Sandbox / Testbed (OT&E)
                        </option>
                        <option value="production" {{ old('environment', $registrar->environment) === 'production' ? 'selected' : '' }}>
                            Production (Live Registration)
                        </option>
                    </select>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Select whether API commands execute against upstream live registry or sandbox testbed.
                    </p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-3 cursor-pointer pt-3">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $registrar->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F]">
                        <div>
                            <span class="text-xs font-medium text-slate-900 dark:text-white block">Enable this Registrar Module</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Allow storefront and automated tasks to route operations through this provider</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Dynamic Schema-Driven Credentials Section (WHMCS Style) -->
            <div>
                <h3 class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span>API Authentication Credentials</span>
                    <span class="text-[11px] font-normal lowercase font-mono text-slate-400">defined by {{ $registrar->slug }} module</span>
                </h3>

                <div class="space-y-4">
                    @if(!empty($fields))
                        @foreach($fields as $key => $field)
                            <div>
                                <label for="config_{{ $key }}" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                    {{ $field['label'] ?? ucfirst($key) }}
                                    @if(!empty($field['required'])) <span class="text-red-500">*</span> @endif
                                </label>

                                @if(($field['type'] ?? 'text') === 'password')
                                    <input type="password"
                                           name="config[{{ $key }}]"
                                           id="config_{{ $key }}"
                                           value="{{ old('config.' . $key, $registrar->config[$key] ?? '') }}"
                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                           class="cf-input w-full font-mono text-xs"
                                           @if(!empty($field['required'])) required @endif>
                                @elseif(($field['type'] ?? 'text') === 'textarea')
                                    <textarea name="config[{{ $key }}]"
                                              id="config_{{ $key }}"
                                              rows="3"
                                              placeholder="{{ $field['placeholder'] ?? '' }}"
                                              class="cf-input w-full font-mono text-xs">{{ old('config.' . $key, $registrar->config[$key] ?? '') }}</textarea>
                                @elseif(($field['type'] ?? 'text') === 'select')
                                    <select name="config[{{ $key }}]" id="config_{{ $key }}" class="cf-input w-full font-semibold">
                                        @foreach(($field['options'] ?? []) as $val => $lbl)
                                            <option value="{{ $val }}" {{ old('config.' . $key, $registrar->config[$key] ?? '') == $val ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text"
                                           name="config[{{ $key }}]"
                                           id="config_{{ $key }}"
                                           value="{{ old('config.' . $key, $registrar->config[$key] ?? '') }}"
                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                           class="cf-input w-full font-mono text-xs"
                                           @if(!empty($field['required'])) required @endif>
                                @endif

                                @if(!empty($field['description']))
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        {{ $field['description'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <!-- Fallback default API Key -->
                        <div>
                            <label for="config_api_key" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                API Key <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   name="config[api_key]"
                                   id="config_api_key"
                                   value="{{ old('config.api_key', $registrar->config['api_key'] ?? '') }}"
                                   placeholder="Upstream API Key"
                                   class="cf-input w-full font-mono text-xs"
                                   required>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.registrars.index') }}" class="cf-btn cf-btn-secondary text-xs">
                    Cancel
                </a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs inline-flex items-center gap-1.5 shadow-xs">
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
