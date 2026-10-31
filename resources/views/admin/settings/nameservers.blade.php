@extends('layouts.admin', ['title' => 'Default Nameservers'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Default Nameservers

    <x-admin-settings-tabs active="nameservers" />@if(session('success'))
        <div class="p-3.5 rounded-[6px] text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="cf-panel p-6 sm:p-7">
        <form action="{{ route('admin.settings.nameservers.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Primary Nameserver (NS1) *</label>
                    <input type="text" name="default_ns1" value="{{ old('default_ns1', $settings['default_ns1'] ?? 'ns1.hostnibo.com') }}" required class="cf-input h-9 w-full font-mono">
                    @error('default_ns1')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Secondary Nameserver (NS2) *</label>
                    <input type="text" name="default_ns2" value="{{ old('default_ns2', $settings['default_ns2'] ?? 'ns2.hostnibo.com') }}" required class="cf-input h-9 w-full font-mono">
                    @error('default_ns2')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Tertiary Nameserver (NS3 - Optional)</label>
                    <input type="text" name="default_ns3" value="{{ old('default_ns3', $settings['default_ns3'] ?? '') }}" placeholder="ns3.hostnibo.com" class="cf-input h-9 w-full font-mono">
                </div>

                <div>
                    <label class="block font-medium mb-1 text-slate-700 dark:text-slate-300">Quaternary Nameserver (NS4 - Optional)</label>
                    <input type="text" name="default_ns4" value="{{ old('default_ns4', $settings['default_ns4'] ?? '') }}" placeholder="ns4.hostnibo.com" class="cf-input h-9 w-full font-mono">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                <button type="submit" class="cf-btn cf-btn-primary h-9 px-6 text-xs font-medium">
                    Save Default Nameservers
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
