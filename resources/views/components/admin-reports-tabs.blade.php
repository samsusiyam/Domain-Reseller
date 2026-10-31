@props(['active' => 'revenue'])

<div class="flex items-center gap-1.5 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold overflow-x-auto pb-px mb-6 select-none">
    <a href="{{ route('admin.reports.revenue') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'revenue' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Revenue & Finances
    </a>
    <a href="{{ route('admin.reports.domains') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'domains' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Domain Portfolio
    </a>
    <a href="{{ route('admin.reports.clients') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'clients' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Client Growth
    </a>
    <a href="{{ route('admin.reports.resellers') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'resellers' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Wholesale Resellers
    </a>
    <a href="{{ route('admin.reports.tlds') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'tlds' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        TLD Profit Margins
    </a>
    <a href="{{ route('admin.reports.payments') }}"
       class="px-3.5 py-2 rounded-t-[4px] transition whitespace-nowrap {{ $active === 'payments' ? 'text-[#F6821F] border-b-2 border-[#F6821F] font-bold bg-[#F6821F]/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
        Gateway Volume
    </a>
</div>
