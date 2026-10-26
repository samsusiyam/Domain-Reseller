@extends('layouts.app', ['title' => 'DNS Zone - ' . $domain->domain_name, 'header' => 'DNS Zone Management'])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{ addModal: false }">
    <!-- Domain Sub-navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto border-b border-slate-200 pb-2 text-xs font-bold uppercase tracking-wider text-slate-600">
        <a href="{{ route('client.domains.show', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Overview</a>
        <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Nameservers</a>
        <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg bg-indigo-50 text-indigo-600 whitespace-nowrap">DNS Zone</a>
        <a href="{{ route('client.domains.child-ns.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Child Nameservers</a>
        <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">WHOIS Contacts</a>
        <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">Email Forwards</a>
        <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="px-3.5 py-2 rounded-lg hover:bg-slate-100 whitespace-nowrap">URL Redirects</a>
    </div>

    <!-- Header & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Hosted DNS Records for {{ $domain->domain_name }}</h2>
            <p class="text-xs text-slate-500">Manage A, AAAA, CNAME, MX, TXT and SRV zone records.</p>
        </div>
        <button type="button" @click="addModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow flex items-center gap-2">
            + Add DNS Record
        </button>
    </div>

    <!-- Records Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-wider">
                <tr>
                    <th class="py-3 px-6">Type</th>
                    <th class="py-3 px-6">Host / Name</th>
                    <th class="py-3 px-6">Value / Destination</th>
                    <th class="py-3 px-6 text-center">Priority</th>
                    <th class="py-3 px-6 text-center">TTL</th>
                    <th class="py-3 px-6 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 font-mono text-xs text-slate-800">
                @forelse($domain->dnsRecords as $record)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-6 font-bold text-indigo-600">{{ $record->type }}</td>
                        <td class="py-3.5 px-6 font-semibold">{{ $record->host }}</td>
                        <td class="py-3.5 px-6 truncate max-w-xs" title="{{ $record->value }}">{{ $record->value }}</td>
                        <td class="py-3.5 px-6 text-center">{{ $record->priority ?? '—' }}</td>
                        <td class="py-3.5 px-6 text-center text-slate-500">{{ $record->ttl }}s</td>
                        <td class="py-3.5 px-6 text-right font-sans">
                            <form action="{{ route('client.domains.dns.destroy', [$domain->id, $record->id]) }}" method="POST" onsubmit="return confirm('Delete this DNS record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 font-sans text-xs">
                            No hosted DNS records added yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Record Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="addModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Add DNS Zone Record</h3>

            <form action="{{ route('client.domains.dns.store', $domain->id) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                            <option value="A">A (IPv4)</option>
                            <option value="AAAA">AAAA (IPv6)</option>
                            <option value="CNAME">CNAME</option>
                            <option value="MX">MX (Mail)</option>
                            <option value="TXT">TXT</option>
                            <option value="SRV">SRV</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Host / Name</label>
                        <input type="text" name="host" placeholder="@ or subdomain" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Value / Destination</label>
                    <input type="text" name="value" placeholder="192.0.2.1 or mail.server.com" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Priority (MX only)</label>
                        <input type="number" name="priority" placeholder="10" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">TTL (Seconds)</label>
                        <input type="number" name="ttl" value="3600" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
