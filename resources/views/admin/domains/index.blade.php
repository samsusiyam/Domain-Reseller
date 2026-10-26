@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Master Domain Portfolio</h1>
            <p class="text-xs text-slate-500 mt-1">Supervise all registered, renewed, and incoming domain transfers across upstream registrars.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <form method="GET" action="{{ route('admin.domains.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by domain name, client name or email..."
                    class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="w-full sm:w-44">
                <select name="registrar_id" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Registrars</option>
                    @foreach($registrars as $reg)
                        <option value="{{ $reg->id }}" {{ request('registrar_id') == $reg->id ? 'selected' : '' }}>{{ $reg->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-40">
                <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="pending_transfer" {{ request('status') === 'pending_transfer' ? 'selected' : '' }}>Pending Transfer</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                Filter
            </button>
            @if(request('search') || request('registrar_id') || request('status'))
                <a href="{{ route('admin.domains.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Domains Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($domains->isEmpty())
            <div class="text-center py-16 px-4 text-slate-400 text-sm">
                No domain records found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Domain Name</th>
                            <th class="px-6 py-3.5">Owner / Account</th>
                            <th class="px-6 py-3.5">Upstream Registrar</th>
                            <th class="px-6 py-3.5">Expires At</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($domains as $dom)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('admin.domains.show', $dom->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $dom->domain_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $dom->user?->name ?? 'None' }}</div>
                                    <div class="text-xs text-slate-400">{{ $dom->user?->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                        {{ $dom->registrar?->name ?? 'Default Upstream' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-xs text-slate-700">
                                    {{ $dom->expires_at ? $dom->expires_at->format('M d, Y') : '—' }}
                                    @if($dom->expires_at && $dom->expires_at->isPast())
                                        <span class="text-rose-600 block text-[10px]">Expired</span>
                                    @elseif($dom->expires_at && $dom->expires_at->diffInDays(now()) <= 30)
                                        <span class="text-amber-600 block text-[10px]">{{ $dom->expires_at->diffInDays(now()) }} days left</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $dom->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($dom->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.domains.show', $dom->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Manage</a>
                                    <a href="{{ route('admin.domains.edit', $dom->id) }}" class="text-slate-500 hover:text-slate-800 text-xs font-semibold">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($domains->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $domains->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
