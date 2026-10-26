@php
$currentCurrency = strtoupper(session('currency', config('currencies.default', 'USD')));
@endphp

<form action="{{ route('currency.switch') }}" method="POST" class="inline-flex items-center" x-data>
    @csrf
    <div class="inline-flex items-center p-0.5 rounded-full bg-slate-900/60 border border-slate-700/80 text-[11px] font-bold shadow-inner backdrop-blur">
        <button type="submit" name="currency" value="USD"
            class="px-2.5 py-1 rounded-full transition-all duration-150 flex items-center gap-1 {{ $currentCurrency === 'USD' ? 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200' }}">
            <span>USD</span>
            <span class="text-[9px] opacity-75 font-mono">$</span>
        </button>
        <button type="submit" name="currency" value="BDT"
            class="px-2.5 py-1 rounded-full transition-all duration-150 flex items-center gap-1 {{ $currentCurrency === 'BDT' ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200' }}">
            <span>BDT</span>
            <span class="text-[9px] opacity-75 font-mono">৳</span>
        </button>
    </div>
</form>
