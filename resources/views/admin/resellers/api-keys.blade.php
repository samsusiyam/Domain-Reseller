@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.resellers.show', $reseller->id) }}" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">API Keys: {{ $reseller->company_name ?: $reseller->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage and audit active developer API credentials for reseller #{{ $reseller->id }}.</p>
        </div>
    </div>

    <!-- Keys Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($apiKeys->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                This reseller has not generated any API credentials yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Key Name</th>
                            <th class="px-6 py-3.5">Public Key</th>
                            <th class="px-6 py-3.5">IP Whitelist</th>
                            <th class="px-6 py-3.5">Last Used</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        @foreach($apiKeys as $key)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-sans font-bold text-slate-900">{{ $key->key_name }}</td>
                                <td class="px-6 py-4 font-bold text-indigo-700 select-all">{{ $key->public_key ?? $key->api_key }}</td>
                                <td class="px-6 py-4 text-slate-500 font-sans text-xs">
                                    {{ !empty($key->ip_whitelist) ? (is_array($key->ip_whitelist) ? implode(', ', $key->ip_whitelist) : $key->ip_whitelist) : 'Any IP (*)' }}
                                </td>
                                <td class="px-6 py-4 text-slate-400 font-sans text-xs">
                                    {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-6 py-4 text-center font-sans">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $key->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $key->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-sans">
                                    <form action="{{ route('admin.resellers.revoke-api-key', [$reseller->id, $key->id]) }}" method="POST" onsubmit="return confirm('Revoke and delete this API key? WHMCS or other integrations using it will immediately cease to function.');">
                                        @csrf
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold">
                                            Revoke Key
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
