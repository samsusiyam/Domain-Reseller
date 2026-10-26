@php
$currentCurrency = session('selected_currency', 'USD');
@endphp

<form action="{{ route('currency.switch') }}" method="POST" class="inline-block" x-data>
    @csrf
    <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-700 p-0.5 bg-slate-100 dark:bg-slate-800 text-xs font-semibold">
        <button type="submit" name="currency" value="USD"
            class="px-2.5 py-1 rounded-md transition {{ $currentCurrency === 'USD' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
            USD ($)
        </button>
        <button type="submit" name="currency" value="BDT"
            class="px-2.5 py-1 rounded-md transition {{ $currentCurrency === 'BDT' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
            BDT (৳)
        </button>
    </div>
</form>
