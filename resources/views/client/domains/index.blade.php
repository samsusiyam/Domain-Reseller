@extends('layouts.app', ['title' => 'My Domains', 'header' => 'My Domains'])

@section('content')
<div class="space-y-6">
    <!-- Header with Search & Register CTA -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('client.domains.index') }}" class="max-w-md w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by domain name..."
                class="w-full px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none shadow-sm">
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('client.domains.search') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                + Register New Domain
            </a>
            <a href="{{ route('client.domains.transfer') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-xs rounded-xl transition shadow-sm">
                Transfer In
            </a>
        </div>
    </div>

    <!-- Domains Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-wider">
                <tr>
                    <th class="py-3.5 px-6">Domain Name</th>
                    <th class="py-3.5 px-6">Registration Date</th>
                    <th class="py-3.5 px-6">Expiration Date</th>
                    <th class="py-3.5 px-6 text-center">Auto-Renew</th>
                    <th class="py-3.5 px-6 text-center">Status</th>
                    <th class="py-3.5 px-6 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-medium text-slate-800">
                @forelse($domains as $domain)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6">
                            <a href="{{ route('client.domains.show', $domain->id) }}" class="font-bold text-indigo-600 hover:underline">
                                {{ $domain->domain_name }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-slate-500 text-xs">
                            {{ $domain->registration_date ? $domain->registration_date->format('M d, Y') : '—' }}
                        </td>
                        <td class="py-4 px-6">
                            @if($domain->expires_at)
                                <span class="font-semibold {{ $domain->expires_at->isPast() ? 'text-rose-600' : ($domain->expires_at->diffInDays() <= 30 ? 'text-amber-600' : 'text-slate-800') }}">
                                    {{ $domain->expires_at->format('M d, Y') }}
                                </span>
                                <span class="block text-[11px] text-slate-400">({{ $domain->expires_at->diffForHumans() }})</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center" x-data="{
                            active: {{ $domain->auto_renew ? 'true' : 'false' }},
                            toggle() {
                                fetch('{{ route('client.domains.toggle-autorenew', $domain->id) }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                }).then(res => res.json()).then(data => { this.active = data.auto_renew; });
                            }
                        }">
                            <button type="button" @click="toggle()"
                                :class="active ? 'bg-indigo-600' : 'bg-slate-200'"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                <span :class="active ? 'translate-x-4' : 'translate-x-0'" class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <x-badge :color="$domain->status">{{ $domain->status }}</x-badge>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('client.domains.show', $domain->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">
                                Manage &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            No domains registered yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($domains->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $domains->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
