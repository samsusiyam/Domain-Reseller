@extends('layouts.reseller')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">API Credentials & Keys</h1>
            <p class="text-sm text-slate-500 mt-1">Authenticate your WHMCS, Blesta, or custom billing application with our REST API.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reseller.api-docs') }}" class="px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                View API Docs
            </a>
            <a href="{{ route('reseller.api-logs') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                API Logs
            </a>
        </div>
    </div>

    <!-- Alert for Newly Generated Key / Secret -->
    @if(session('new_api_secret'))
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-amber-900">Important: Save Your API Secret Key Now!</h3>
                    <p class="text-xs text-amber-800 mt-1">For security reasons, your API Secret is never stored in plain text and will <strong class="underline">never be displayed again</strong>. Copy it immediately to your WHMCS registrar configuration.</p>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 font-mono text-xs">
                        @if(session('new_api_key'))
                            <div class="bg-white p-3 rounded-xl border border-amber-200">
                                <span class="text-slate-400 block text-[10px] uppercase font-sans font-bold">API Public Key:</span>
                                <span class="text-slate-900 font-bold select-all">{{ session('new_api_key') }}</span>
                            </div>
                        @endif
                        <div class="bg-white p-3 rounded-xl border border-amber-200">
                            <span class="text-amber-600 block text-[10px] uppercase font-sans font-bold">API Secret Key (Copy Now):</span>
                            <span class="text-rose-600 font-bold select-all">{{ session('new_api_secret') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Generate New API Key Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-800 mb-1">Create API Key</h3>
                <p class="text-xs text-slate-500 mb-5">Issue a new key pair for a production or staging server.</p>

                <form action="{{ route('reseller.api-keys.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Key Description / Name</label>
                        <input type="text" name="key_name" placeholder="e.g. WHMCS Primary Production" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            value="{{ old('key_name') }}">
                        @error('key_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Allowed Server IPs (Optional)</label>
                        <textarea name="ip_whitelist" rows="3" placeholder="192.168.1.1&#10;203.0.113.50"
                            class="w-full px-3 py-2 font-mono text-xs border border-slate-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('ip_whitelist') }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">One IP address per line. Leave empty to allow any server IP (not recommended for production).</p>
                        @error('ip_whitelist') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Generate API Key Pair
                    </button>
                </form>
            </div>
        </div>

        <!-- Keys Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Your Active API Keys</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Use these credentials in your WHMCS or client application.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                        {{ $apiKeys->count() }} Keys
                    </span>
                </div>

                @if($apiKeys->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                        <h4 class="text-base font-semibold text-slate-700">No API Keys Generated</h4>
                        <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">Generate an API key pair using the form on the left to start provisioning domains via WHMCS.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($apiKeys as $key)
                            <div class="p-6 hover:bg-slate-50/50 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-slate-900 text-base">{{ $key->key_name }}</h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                                {{ $key->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $key->status }}
                                            </span>
                                        </div>
                                        <div class="mt-2 space-y-1 font-mono text-xs">
                                            <p class="text-slate-600">
                                                <span class="text-slate-400 uppercase font-sans font-semibold text-[10px] mr-1">Public Key:</span>
                                                <span class="bg-slate-100 px-2 py-0.5 rounded select-all font-bold text-slate-800">{{ $key->public_key ?? $key->api_key }}</span>
                                            </p>
                                            <p class="text-slate-500 text-[11px] font-sans">
                                                Created {{ $key->created_at->format('M d, Y') }} &bull; Last used {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start sm:self-auto">
                                        <!-- Regenerate Secret -->
                                        <form action="{{ route('reseller.api-keys.regenerate-secret', $key->id) }}" method="POST" onsubmit="return confirm('Regenerate secret? Any server using the old secret will fail to authenticate until updated.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition">
                                                Roll Secret
                                            </button>
                                        </form>

                                        <!-- Revoke / Delete -->
                                        <form action="{{ route('reseller.api-keys.destroy', $key->id) }}" method="POST" onsubmit="return confirm('Permanently revoke and delete this API key?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold rounded-lg transition">
                                                Revoke
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if(!empty($key->ip_whitelist))
                                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500 font-mono">
                                        <span class="text-[10px] font-sans uppercase font-bold text-slate-400">IP Whitelist:</span>
                                        <span>{{ is_array($key->ip_whitelist) ? implode(', ', $key->ip_whitelist) : $key->ip_whitelist }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
