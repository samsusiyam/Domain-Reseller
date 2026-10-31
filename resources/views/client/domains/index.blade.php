@extends('layouts.app', ['title' => 'My Domains', 'header' => 'My Domains'])

@section('content')
<div class="space-y-6" x-data="{
    selectedDomains: [],
    selectAll: false,
    showNsModal: false,
    ns1: '',
    ns2: '',
    ns3: '',
    ns4: '',
    toggleAll() {
        if (this.selectAll) {
            this.selectedDomains = {{ json_encode($domains->pluck('id')->toArray()) }};
        } else {
            this.selectedDomains = [];
        }
    },
    checkSelected() {
        this.selectAll = this.selectedDomains.length === {{ $domains->count() }};
    },
    submitBulk(action) {
        if (this.selectedDomains.length === 0) return;
        if (action === 'change_nameservers') {
            this.showNsModal = true;
            return;
        }
        if (confirm('Apply ' + action.replace('_', ' ') + ' to ' + this.selectedDomains.length + ' domain(s)?')) {
            let form = document.getElementById('bulk-action-form');
            document.getElementById('bulk-action-input').value = action;
            form.submit();
        }
    }
}">

    <!-- Filter Tabs & Actions Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-800 text-xs font-semibold overflow-x-auto">
            <a href="{{ route('client.domains.index') }}"
               class="px-3 py-1.5 rounded-lg transition {{ !request('status') ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                All Domains
            </a>
            <a href="{{ route('client.domains.index', ['status' => 'active']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'active' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                Active
            </a>
            <a href="{{ route('client.domains.index', ['status' => 'expiring']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'expiring' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                Expiring Soon
            </a>
            <a href="{{ route('client.domains.index', ['status' => 'expired']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'expired' ? 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                Expired
            </a>
        </div>

        <!-- Search & New Domain CTA -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <form method="GET" action="{{ route('client.domains.index') }}" class="relative flex-1 md:w-64">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search domains..."
                       class="cf-input h-9 pl-8 text-xs w-full">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

            <a href="{{ route('client.domains.search') }}" class="cf-btn cf-btn-primary cf-btn-sm font-semibold text-xs inline-flex items-center gap-1.5 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Register</span>
            </a>
            <a href="{{ route('domain.transfer') }}" class="cf-btn cf-btn-secondary cf-btn-sm font-medium text-xs inline-flex items-center gap-1.5 shrink-0">
                <span>Transfer</span>
            </a>
        </div>
    </div>

    <!-- Sticky / Dynamic Bulk Action Bar -->
    <div x-show="selectedDomains.length > 0" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-[#111827] text-white p-3 sm:px-5 sm:py-3.5 rounded-xl shadow-lg border border-slate-700/80 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F6821F] text-white font-bold text-xs" x-text="selectedDomains.length"></span>
            <span class="text-xs font-semibold text-slate-200">domain(s) selected</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="submitBulk('change_nameservers')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700 transition">
                Change Nameservers
            </button>
            <button type="button" @click="submitBulk('lock')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700 transition">
                Lock Selected
            </button>
            <button type="button" @click="submitBulk('unlock')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700 transition">
                Unlock Selected
            </button>
            <button type="button" @click="submitBulk('enable_autorenew')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700 transition">
                Enable Auto-Renew
            </button>
            <button type="button" @click="submitBulk('disable_autorenew')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 border border-slate-700 transition">
                Disable Auto-Renew
            </button>
            <button type="button" @click="selectedDomains = []; selectAll = false" class="text-xs text-slate-400 hover:text-white px-2 py-1">
                Clear
            </button>
        </div>
    </div>

    <!-- Hidden Bulk Form -->
    <form id="bulk-action-form" method="POST" action="{{ route('client.domains.bulk-action') }}" class="hidden">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input">
        <template x-for="id in selectedDomains" :key="id">
            <input type="hidden" name="domain_ids[]" :value="id">
        </template>
    </form>

    <!-- Domains Table -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        @if($domains->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                       class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] cursor-pointer">
                            </th>
                            <th class="py-3 px-4">Domain Name</th>
                            <th class="py-3 px-4">Registered</th>
                            <th class="py-3 px-4">Expires</th>
                            <th class="py-3 px-4 text-center">Auto-Renew</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($domains as $domain)
                            @php
                                $daysLeft = $domain->expires_at ? now()->diffInDays($domain->expires_at, false) : null;
                                $isExpiring = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30;
                                $isExpired = $daysLeft !== null && $daysLeft < 0;
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition" :class="selectedDomains.includes({{ $domain->id }}) ? 'bg-orange-50/30 dark:bg-orange-950/10' : ''">
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox" value="{{ $domain->id }}" x-model="selectedDomains" @change="checkSelected()"
                                           class="rounded border-slate-300 dark:border-slate-700 text-[#F6821F] focus:ring-[#F6821F] cursor-pointer">
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('client.domains.show', $domain->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-[#F6821F] transition text-sm">
                                            {{ $domain->domain_name }}
                                        </a>
                                        @if($domain->is_locked)
                                            <span title="Registrar Lock Active" class="text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        @endif
                                        @if($domain->is_private)
                                            <span title="WHOIS Privacy Active" class="text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $domain->registered_at ? $domain->registered_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($domain->expires_at)
                                        <div class="font-medium text-slate-800 dark:text-slate-200">
                                            {{ $domain->expires_at->format('M d, Y') }}
                                        </div>
                                        @if($isExpired)
                                            <span class="inline-flex text-[10px] font-bold text-rose-600 dark:text-rose-400">
                                                Expired ({{ abs($daysLeft) }}d ago)
                                            </span>
                                        @elseif($isExpiring)
                                            <span class="inline-flex text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                                Expires in {{ $daysLeft }} days
                                            </span>
                                        @else
                                            <span class="inline-flex text-[10px] text-slate-400">
                                                in {{ $domain->expires_at->diffForHumans(null, true) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $domain->is_auto_renew ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                        {{ $domain->is_auto_renew ? 'On' : 'Off' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $domain->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                        {{ ucfirst($domain->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5" x-data="{ openDropdown: false }">
                                        <a href="{{ route('client.domains.show', $domain->id) }}"
                                           class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-semibold inline-flex items-center gap-1">
                                            <span>Manage</span>
                                        </a>

                                        <!-- Dropdown Menu -->
                                        <div class="relative">
                                            <button type="button" @click="openDropdown = !openDropdown" @click.away="openDropdown = false"
                                                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                            </button>

                                            <div x-show="openDropdown" x-cloak
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 class="absolute right-0 mt-1 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-20 py-1 text-left text-xs">
                                                <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="block px-3.5 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium">
                                                    Nameservers
                                                </a>
                                                <a href="{{ route('client.domains.dns.index', $domain->id) }}" class="block px-3.5 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium">
                                                    DNS Management
                                                </a>
                                                <a href="{{ route('client.domains.whois.edit', $domain->id) }}" class="block px-3.5 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium">
                                                    WHOIS Contacts
                                                </a>
                                                <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}" class="block px-3.5 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium">
                                                    Email Forwarding
                                                </a>
                                                <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}" class="block px-3.5 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium">
                                                    URL Redirection
                                                </a>
                                                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                                <form action="{{ route('client.domains.renew', $domain->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="years" value="1">
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-[#F6821F] hover:bg-orange-50 dark:hover:bg-orange-950/20 font-bold">
                                                        Renew Domain (1 yr)
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $domains->links() }}
            </div>
        @else
            <!-- Actionable Empty State -->
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-950/30 border border-orange-100 dark:border-orange-900/50 flex items-center justify-center text-[#F6821F] mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">No domains found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">No domains match your current filter or search criteria.</p>
                <div class="mt-4 flex items-center justify-center gap-3">
                    <a href="{{ route('client.domains.search') }}" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-semibold">
                        Register Domain
                    </a>
                    <a href="{{ route('domain.transfer') }}" class="cf-btn cf-btn-secondary cf-btn-sm text-xs font-semibold">
                        Transfer Domain
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Bulk Nameserver Modal -->
    <div x-show="showNsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showNsModal" @click="showNsModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showNsModal" class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Bulk Change Nameservers</h3>
                    <button type="button" @click="showNsModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-xs text-slate-500">Updating nameservers for <span class="font-bold text-[#F6821F]" x-text="selectedDomains.length"></span> selected domains.</p>
                <form method="POST" action="{{ route('client.domains.bulk-action') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="action" value="change_nameservers">
                    <template x-for="id in selectedDomains" :key="id">
                        <input type="hidden" name="domain_ids[]" :value="id">
                    </template>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nameserver 1 *</label>
                        <input type="text" name="nameservers[]" required placeholder="ns1.example.com" class="cf-input h-9 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nameserver 2 *</label>
                        <input type="text" name="nameservers[]" required placeholder="ns2.example.com" class="cf-input h-9 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nameserver 3 (Optional)</label>
                        <input type="text" name="nameservers[]" placeholder="ns3.example.com" class="cf-input h-9 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nameserver 4 (Optional)</label>
                        <input type="text" name="nameservers[]" placeholder="ns4.example.com" class="cf-input h-9 text-xs font-mono">
                    </div>
                    <div class="pt-3 flex items-center justify-end gap-2">
                        <button type="button" @click="showNsModal = false" class="cf-btn cf-btn-secondary cf-btn-sm text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="cf-btn cf-btn-primary cf-btn-sm text-xs font-bold">
                            Update All Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
