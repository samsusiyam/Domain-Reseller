@extends('layouts.app', ['title' => 'Manage ' . $domain->domain_name, 'header' => 'Domain Overview'])

@section('content')
<div class="space-y-6" x-data="{
    isLocked: {{ $domain->is_locked ? 'true' : 'false' }},
    isPrivate: {{ $domain->is_private ? 'true' : 'false' }},
    eppCode: '',
    loadingEpp: false,
    eppModal: false,
    toggleLock() {
        fetch('{{ route('client.domains.toggle-lock', $domain->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => res.json()).then(data => { this.isLocked = data.is_locked; });
    },
    togglePrivacy() {
        fetch('{{ route('client.domains.toggle-privacy', $domain->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => res.json()).then(data => { this.isPrivate = data.is_private; });
    },
    fetchEpp() {
        this.loadingEpp = true;
        this.eppModal = true;
        fetch('{{ route('client.domains.epp', $domain->id) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => res.json()).then(data => {
            this.loadingEpp = false;
            this.eppCode = data.epp_code || 'Unable to retrieve';
        });
    }
}">

    <!-- Top Domain Banner -->
    <div class="bg-white dark:bg-[#111827] p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $domain->domain_name }}</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium uppercase
                    {{ $domain->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' }}">
                    {{ ucfirst($domain->status) }}
                </span>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span>Registered: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $domain->registered_at ? $domain->registered_at->format('M d, Y') : 'N/A' }}</strong></span>
                <span>&bull;</span>
                <span>Expires: <strong class="{{ $domain->expires_at && $domain->expires_at->isPast() ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }} font-semibold">{{ $domain->expires_at ? $domain->expires_at->format('M d, Y') : 'N/A' }}</strong></span>
                @if($domain->expires_at)
                    <span class="text-slate-400">({{ $domain->expires_at->diffForHumans() }})</span>
                @endif
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Renew Form Dropdown -->
            <form action="{{ route('client.domains.renew', $domain->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="years" class="cf-input h-9 text-xs font-semibold">
                    <option value="1">1 Year</option>
                    <option value="2">2 Years</option>
                    <option value="3">3 Years</option>
                    <option value="5">5 Years</option>
                </select>
                <button type="submit" class="cf-btn cf-btn-primary text-xs h-9 shadow-xs">
                    Renew Now
                </button>
            </form>

            <button type="button" @click="fetchEpp()" class="cf-btn cf-btn-secondary text-xs h-9">
                Get EPP Code
            </button>
        </div>
    </div>

    <x-domain-tabs :domain="$domain" active="overview" />

    <!-- Security Toggles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Registrar Lock Card -->
        <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Registrar Transfer Lock</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Prevents unauthorized domain release or transfer out</p>
            </div>
            <button type="button" @click="toggleLock()"
                :class="isLocked ? 'bg-[#F6821F]' : 'bg-slate-200 dark:bg-slate-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="isLocked ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>

        <!-- WHOIS Privacy Card -->
        <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">WHOIS ID Protection</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Masks registrant contact identity in public WHOIS directories</p>
            </div>
            <button type="button" @click="togglePrivacy()"
                :class="isPrivate ? 'bg-[#F6821F]' : 'bg-slate-200 dark:bg-slate-700'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="isPrivate ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
        </div>
    </div>

    <!-- Nameservers Snapshot -->
    <div class="bg-white dark:bg-[#111827] p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Current Authoritative Nameservers</h3>
            <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}" class="text-xs font-semibold text-[#F6821F] hover:underline">Change Nameservers &rarr;</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono text-xs">
            @forelse($domain->nameservers ?? [] as $ns)
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-lg text-slate-800 dark:text-slate-200 font-semibold">
                    {{ $ns }}
                </div>
            @empty
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-lg text-slate-400">Default Nameservers</div>
            @endforelse
        </div>
    </div>

    <!-- EPP Modal -->
    <div x-show="eppModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="eppModal = false" class="bg-white dark:bg-[#111827] rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">EPP / Authorization Code</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">This authorization secret code is required if you wish to transfer {{ $domain->domain_name }} to another provider.</p>

            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 text-center font-mono text-base font-bold text-[#F6821F] select-all mb-4">
                <span x-show="!loadingEpp" x-text="eppCode"></span>
                <span x-show="loadingEpp">Fetching from registrar...</span>
            </div>

            <button type="button" @click="eppModal = false" class="cf-btn cf-btn-secondary w-full text-xs cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>
@endsection
