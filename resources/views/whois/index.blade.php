@extends('layouts.guest', ['title' => 'WHOIS & RDAP Domain Lookup'])

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

    <!-- Header Section: 24px title, one-line description -->
    <div class="mb-8">
        <div class="cf-breadcrumb mb-2">
            <a href="{{ route('home') }}">Home</a>
            <span class="cf-breadcrumb-sep">/</span>
            <a href="{{ route('domain.search') }}">Domains</a>
            <span class="cf-breadcrumb-sep">/</span>
            <span class="cf-breadcrumb-current">WHOIS</span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">WHOIS & RDAP lookup</h1>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
            Inspect authoritative ICANN RDAP records, registration status, delegated nameservers, and expiration dates.
        </p>
    </div>

    <!-- Search Bar: single clean panel with 40px input + orange button -->
    <div class="cf-panel p-4 mb-8">
        <form action="{{ route('whois.query') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
            @csrf
            <div class="relative flex-1">
                <input type="text"
                       name="domain"
                       value="{{ old('domain', $domain ?? '') }}"
                       placeholder="Enter domain name (e.g. google.com, github.com, cloudflare.com)"
                       required
                       autofocus
                       class="cf-input h-10 font-mono text-sm @error('domain') border-[#DC2626] @enderror">
            </div>
            <button type="submit" class="cf-btn cf-btn-primary h-10 px-5 font-medium text-sm">
                Lookup record
            </button>
        </form>

        <div class="mt-3.5 pt-3 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
            <span class="font-medium text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 mr-1">Supported Extensions:</span>
            @foreach(['.com', '.net', '.org', '.com.bd', '.ai', '.xyz', '.store', '.online', '.tech', '.dev', '.io', '.info'] as $chip)
                <span class="px-2 py-0.5 rounded-[4px] text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50">
                    {{ $chip }}
                </span>
            @endforeach
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 p-3.5 rounded-[6px] text-xs flex items-center justify-between font-medium"
             style="background-color: var(--danger-bg); border: 1px solid var(--danger); color: var(--danger);">
            <span>{{ session('error') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="hover:opacity-75">✕</button>
        </div>
    @endif

    @if(!empty($result))
        @if(!$result['success'])
            <div class="cf-panel p-6 mb-8 text-center">
                <div class="cf-empty-icon mx-auto">
                    <svg class="w-8 h-8 mx-auto" style="color: var(--danger);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold mt-2" style="color: var(--text);">Lookup error</h3>
                <p class="text-xs mt-1" style="color: var(--text-muted);">{{ $result['error'] ?? 'Unable to complete domain lookup.' }}</p>
            </div>
        @elseif(!$result['is_registered'])
            <!-- Domain is NOT registered: Available state -->
            <div class="cf-panel p-6 sm:p-8 mb-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="cf-status-dot neutral">Not registered</span>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded" style="background-color: var(--surface); color: var(--text-muted);">
                                RDAP 404
                            </span>
                        </div>
                        <h2 class="text-2xl font-mono font-semibold tracking-tight" style="color: var(--text);">
                            {{ $result['domain'] }}
                        </h2>
                        <p class="text-xs mt-1" style="color: var(--text-muted);">
                            This domain has no active registry record and is currently available for wholesale registration.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('domain.search') }}?query={{ urlencode($result['domain']) }}"
                           class="cf-btn cf-btn-primary h-10 px-5 font-medium text-sm">
                            Register this domain
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Structured WHOIS / RDAP Data Table -->
            <div class="cf-panel mb-8 overflow-hidden">
                <div class="cf-panel-header">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-base font-semibold" style="color: var(--text);">{{ $result['domain'] }}</span>
                        <span class="cf-status-dot success">Registered</span>
                    </div>
                    <span class="text-xs font-mono" style="color: var(--text-muted);">
                        Protocol: {{ $result['protocol'] ?? 'RDAP' }}
                    </span>
                </div>

                <div class="p-0">
                    <table class="cf-table">
                        <tbody>
                            <tr>
                                <td class="w-1/3 font-medium" style="color: var(--text-muted);">Registrar</td>
                                <td class="font-medium" style="color: var(--text);">
                                    {{ $result['registrar'] }}
                                    @if(!empty($result['registrar_iana_id']))
                                        <span class="text-xs font-mono ml-1" style="color: var(--text-muted);">(IANA ID: {{ $result['registrar_iana_id'] }})</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">Creation date</td>
                                <td class="font-mono tabular-nums" style="color: var(--text);">
                                    {{ $result['created_at'] ? date('Y-m-d H:i:s T', strtotime($result['created_at'])) : 'Not reported' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">Registry expiration date</td>
                                <td class="font-mono tabular-nums" style="color: var(--text);">
                                    {{ $result['expires_at'] ? date('Y-m-d H:i:s T', strtotime($result['expires_at'])) : 'Not reported' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">Last updated date</td>
                                <td class="font-mono tabular-nums" style="color: var(--text);">
                                    {{ $result['updated_at'] ? date('Y-m-d H:i:s T', strtotime($result['updated_at'])) : 'Not reported' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">DNSSEC status</td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 font-medium text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full {{ ($result['dnssec'] ?? '') === 'Signed' ? 'bg-[#16A34A]' : 'bg-[#6B7280]' }}"></span>
                                        <span style="color: var(--text);">{{ $result['dnssec'] ?? 'Unsigned' }}</span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">Delegated nameservers</td>
                                <td>
                                    @if(!empty($result['nameservers']))
                                        <div class="space-y-1 font-mono text-xs" style="color: var(--text);">
                                            @foreach($result['nameservers'] as $ns)
                                                <div>{{ $ns }}</div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted);">No nameservers found</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">Domain status codes</td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($result['status'] as $s)
                                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-[4px] border"
                                                  style="background-color: var(--surface); border-color: var(--border); color: var(--text);">
                                                {{ $s }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium" style="color: var(--text-muted);">Registrant details</td>
                                <td class="text-xs" style="color: var(--text);">
                                    <div class="font-medium">{{ $result['registrant']['name'] ?? 'Redacted for Privacy' }}</div>
                                    <div class="text-[11px]" style="color: var(--text-muted);">
                                        Organization: {{ $result['registrant']['organization'] ?? 'Redacted' }}
                                        @if(!empty($result['registrant']['country']))
                                            • Country: {{ $result['registrant']['country'] }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Collapsible Raw RDAP / WHOIS Block -->
            <details class="cf-panel mb-8" x-data="{ copied: false }">
                <summary class="cf-panel-header cursor-pointer select-none">
                    <span class="font-medium text-xs uppercase tracking-wider" style="color: var(--text-muted);">
                        View authoritative raw registry response
                    </span>
                    <span class="text-xs" style="color: var(--accent);">Toggle raw</span>
                </summary>
                <div class="p-4" style="background-color: var(--surface);">
                    <div class="flex justify-end mb-2">
                        <button type="button"
                                class="cf-btn cf-btn-secondary cf-btn-sm"
                                @click="navigator.clipboard.writeText($refs.rawText.innerText); copied = true; setTimeout(() => copied = false, 2000)">
                            <span x-text="copied ? 'Copied' : 'Copy raw text'"></span>
                        </button>
                    </div>
                    <pre x-ref="rawText" class="p-4 rounded-[6px] border text-xs font-mono overflow-x-auto whitespace-pre leading-relaxed"
                         style="background-color: var(--bg); border-color: var(--border); color: var(--text);">{{ $result['raw'] ?? 'No raw output available.' }}</pre>
                </div>
            </details>
        @endif
    @endif

</div>
@endsection
