@extends('layouts.app', ['title' => 'DNS Zone - ' . $domain->domain_name, 'header' => 'DNS Zone Management'])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{ addModal: false, presetModal: false }">
    <!-- Domain Sub-navigation Tabs -->
    <x-domain-tabs :domain="$domain" active="dns" />

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Hosted DNS Records for {{ $domain->domain_name }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage A, AAAA, CNAME, MX, TXT and SRV zone records with instant propagation.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="presetModal = true" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-semibold cursor-pointer flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>1-Click Presets</span>
            </button>
            <button type="button" @click="addModal = true" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold cursor-pointer flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add DNS Record</span>
            </button>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-medium">
                    <tr>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Host / Name</th>
                        <th class="px-5 py-3">Value / Target</th>
                        <th class="px-5 py-3 text-center">Priority</th>
                        <th class="px-5 py-3 text-center">TTL</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono text-xs text-slate-700 dark:text-slate-300">
                    @forelse($domain->dnsRecords as $record)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3 font-bold text-[#F6821F]">
                                <span class="px-1.5 py-0.5 rounded bg-orange-50 dark:bg-orange-950/40 text-[10px]">{{ $record->type }}</span>
                            </td>
                            <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ $record->host }}</td>
                            <td class="px-5 py-3 truncate max-w-xs font-mono text-slate-600 dark:text-slate-400" title="{{ $record->value }}">{{ $record->value }}</td>
                            <td class="px-5 py-3 text-center tabular-nums text-slate-400">{{ $record->priority ?? '—' }}</td>
                            <td class="px-5 py-3 text-center tabular-nums text-slate-500">{{ $record->ttl }}s</td>
                            <td class="px-5 py-3 text-right font-sans">
                                <form action="{{ route('client.domains.dns.destroy', [$domain->id, $record->id]) }}" method="POST" onsubmit="return confirm('Delete this DNS record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline text-xs cursor-pointer">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center font-sans text-slate-400">
                                No custom DNS records configured. Click "Add DNS Record" or "1-Click Presets" to configure zone records.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1-Click DNS Presets Modal -->
    <div x-show="presetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="presetModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 text-base">⚡</span>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">1-Click DNS Presets</h3>
                    </div>
                    <button type="button" @click="presetModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 mb-4">
                    Instantly provision verified DNS records for popular email and enterprise suites without manual record entry:
                </p>

                <form action="{{ route('client.domains.dns.preset', $domain->id) }}" method="POST" class="space-y-3">
                    @csrf
                    
                    <!-- Preset 1: Google Workspace -->
                    <label class="block p-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-[#F6821F] dark:hover:border-[#F6821F] cursor-pointer transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="preset" value="google_workspace" checked class="mt-0.5 accent-[#F6821F]">
                            <div>
                                <span class="font-semibold text-xs text-slate-900 dark:text-white block">Google Workspace (Gmail)</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                    Adds all 5 official Google MX servers (ASPMX.L.GOOGLE.COM) + SPF TXT validation.
                                </span>
                            </div>
                        </div>
                    </label>

                    <!-- Preset 2: Microsoft 365 -->
                    <label class="block p-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-[#F6821F] dark:hover:border-[#F6821F] cursor-pointer transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="preset" value="microsoft_365" class="mt-0.5 accent-[#F6821F]">
                            <div>
                                <span class="font-semibold text-xs text-slate-900 dark:text-white block">Microsoft 365 (Office 365)</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                    Configures Outlook protection MX, Autodiscover CNAME, and Microsoft SPF record.
                                </span>
                            </div>
                        </div>
                    </label>

                    <!-- Preset 3: cPanel Mail -->
                    <label class="block p-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-[#F6821F] dark:hover:border-[#F6821F] cursor-pointer transition">
                        <div class="flex items-start gap-3">
                            <input type="radio" name="preset" value="cpanel_webmail" class="mt-0.5 accent-[#F6821F]">
                            <div>
                                <span class="font-semibold text-xs text-slate-900 dark:text-white block">cPanel / Webmail Standard</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                    Points mail.{{ $domain->domain_name }} CNAME + MX Priority 0 + standard +a +mx SPF.
                                </span>
                            </div>
                        </div>
                    </label>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="presetModal = false" class="cf-btn cf-btn-secondary text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="cf-btn cf-btn-primary text-xs font-semibold cursor-pointer">
                            Apply Selected Preset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Record Modal -->
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="addModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Add DNS Zone Record</h3>
                    <button type="button" @click="addModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('client.domains.dns.store', $domain->id) }}" method="POST" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Record Type</label>
                        <select name="type" class="cf-select h-9 text-xs">
                            <option value="A">A (IPv4 Address)</option>
                            <option value="AAAA">AAAA (IPv6 Address)</option>
                            <option value="CNAME">CNAME (Canonical Alias)</option>
                            <option value="MX">MX (Mail Exchange)</option>
                            <option value="TXT">TXT (Text / SPF / DKIM)</option>
                            <option value="SRV">SRV (Service)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Host / Name</label>
                        <input type="text" name="host" placeholder="@ or subdomain (e.g. mail, api)" required class="cf-input h-9 text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Value / Target</label>
                        <input type="text" name="value" placeholder="192.0.2.1 or mail.server.com" required class="cf-input h-9 text-xs font-mono">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Priority (MX / SRV)</label>
                            <input type="number" name="priority" placeholder="10" class="cf-input h-9 text-xs font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">TTL (Seconds)</label>
                            <input type="number" name="ttl" value="3600" class="cf-input h-9 text-xs font-mono">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="addModal = false" class="cf-btn cf-btn-secondary text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="cf-btn cf-btn-primary text-xs font-semibold cursor-pointer">
                            Save DNS Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
