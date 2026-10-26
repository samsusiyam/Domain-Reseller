@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Upstream Registrar Modules</h1>
            <p class="text-xs text-slate-500 mt-1">Manage API connectors to wholesale suppliers: Namecheap, Spaceship, Dynadot, AtakDomain, and NameSilo.</p>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($registrars as $reg)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between hover:border-indigo-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-800 text-sm">
                                {{ substr($reg->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $reg->name }}</h3>
                                <span class="text-[10px] uppercase font-mono tracking-wider px-2 py-0.5 rounded
                                    {{ $reg->environment === 'production' ? 'bg-purple-100 text-purple-700 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $reg->environment }}
                                </span>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $reg->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $reg->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-3 pt-4 border-t border-slate-100 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 uppercase font-semibold text-[10px]">Upstream Balance:</span>
                            <span class="font-black text-slate-900 text-base">${{ number_format((float)$reg->balance, 2) }} USD</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 uppercase font-semibold text-[10px]">Active TLDs Routed:</span>
                            <span class="font-bold text-slate-800">{{ $reg->tlds_count }} extensions</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 uppercase font-semibold text-[10px]">Domains Provisioned:</span>
                            <span class="font-bold text-slate-800">{{ $reg->domains_count }} domains</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <!-- Test Connection -->
                        <form action="{{ route('admin.registrars.test-connection', $reg->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition" title="Test API ping">
                                Test API
                            </button>
                        </form>

                        <!-- Sync Balance -->
                        <form action="{{ route('admin.registrars.sync-balance', $reg->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg transition" title="Fetch live balance">
                                Sync $
                            </button>
                        </form>
                    </div>

                    <a href="{{ route('admin.registrars.edit', $reg->id) }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition">
                        Configure &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
