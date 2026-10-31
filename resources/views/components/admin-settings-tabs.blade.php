@props(['active' => 'general'])

<div class="flex items-center gap-1.5 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold overflow-x-auto pb-px mb-6 select-none">
    <a href="{{ route('admin.settings.general') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'general' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        General & Branding
    </a>
    <a href="{{ route('admin.settings.gateways') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'gateways' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Payment Gateways
    </a>
    <a href="{{ route('admin.settings.currencies') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'currencies' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Currencies & FX
    </a>
    <a href="{{ route('admin.settings.sms') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'sms' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        SMS Gateway
    </a>
    <a href="{{ route('admin.settings.nameservers') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'nameservers' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        System Nameservers
    </a>
    <a href="{{ route('admin.settings.email') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'email' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Mail / SMTP
    </a>
    <a href="{{ route('admin.settings.security') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'security' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Platform Security Policy
    </a>
</div>
