@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ search: '' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">TLD Extensions & Pricing Matrix</h1>
            <p class="text-xs text-slate-500 mt-1">Configure retail margins, wholesale reseller rates, and upstream registrar routing per extension.</p>
        </div>
        <div>
            <a href="{{ route('admin.tlds.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition text-xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New TLD Extension
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" x-model="search" placeholder="Search TLD (e.g. .com, .net)..."
                class="block w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="text-xs text-slate-500">
            Total Extensions: <strong class="text-slate-800">{{ $tlds->count() }}</strong>
        </div>
    </div>

    <!-- TLDs Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">TLD Extension</th>
                        <th class="px-6 py-3.5">Upstream Registrar</th>
                        <th class="px-6 py-3.5 text-right">Upstream Cost (Reg/Ren)</th>
                        <th class="px-6 py-3.5 text-right">Retail Client (Reg/Ren)</th>
                        <th class="px-6 py-3.5 text-right">Reseller Wholesale</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tlds as $tld)
                        <tr class="hover:bg-slate-50/50 transition" x-show="!search || '{{ strtolower($tld->tld) }}'.includes(search.toLowerCase())">
                            <td class="px-6 py-4 font-mono font-bold text-base text-slate-900">
                                {{ $tld->tld }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                    {{ $tld->registrar?->name ?? 'Default Upstream' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-xs text-slate-500">
                                ${{ number_format($tld->cost_register, 2) }} / ${{ number_format($tld->cost_renew, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                ${{ number_format($tld->client_register, 2) }}
                                <span class="text-xs text-slate-400 font-normal">/ ${{ number_format($tld->client_renew, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-indigo-700">
                                ${{ number_format($tld->default_reseller_register, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $tld->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $tld->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.tlds.edit', $tld->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold">Edit</a>
                                <form action="{{ route('admin.tlds.destroy', $tld->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this TLD pricing extension?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
