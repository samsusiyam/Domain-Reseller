@props(['domain', 'active' => 'overview'])

<div class="flex items-center gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-800 pb-2 text-xs font-medium tracking-tight">
    <a href="{{ route('client.domains.show', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'overview' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        Overview
    </a>
    <a href="{{ route('client.domains.nameservers.edit', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'nameservers' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        Nameservers
    </a>
    <a href="{{ route('client.domains.dns.index', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'dns' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        DNS Zone
    </a>
    <a href="{{ route('client.domains.child-ns.index', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'child-ns' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        Child Nameservers
    </a>
    <a href="{{ route('client.domains.whois.edit', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'whois' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        WHOIS Contacts
    </a>
    <a href="{{ route('client.domains.email-forwarding.index', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'email-forwarding' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        Email Forwards
    </a>
    <a href="{{ route('client.domains.url-forwarding.index', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors {{ $active === 'url-forwarding' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        URL Redirects
    </a>
    <a href="{{ route('client.domains.push', $domain->id) }}"
       class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors flex items-center gap-1.5 {{ $active === 'push' ? 'bg-orange-50 text-[#F6821F] dark:bg-orange-950/30 dark:text-[#F6821F] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span>Push Domain</span>
    </a>
</div>
