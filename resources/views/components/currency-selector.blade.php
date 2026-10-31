@php
$currentCurrency = strtoupper(session('currency', request()->cookie('currency', config('currencies.default', 'USD'))));
@endphp

<form action="{{ route('currency.switch') }}" method="POST" class="inline-flex items-center" onsubmit="if(window.setCurrency){ window.setCurrency(this.querySelector('button[type=submit][data-clicked=true]')?.value || 'USD'); return false; }">
    @csrf
    <div class="currency-toggle-group inline-flex items-center p-0.5 rounded-[6px] text-[12px] font-medium select-none transition-colors">
        <button type="submit" name="currency" value="USD" data-currency-toggle data-currency-val="USD"
            onclick="this.form.querySelectorAll('button').forEach(b => b.removeAttribute('data-clicked')); this.setAttribute('data-clicked', 'true'); if(window.setCurrency){ window.setCurrency('USD'); return false; }"
            class="currency-btn px-2.5 py-1 rounded-[4px] text-xs font-semibold transition-all cursor-pointer {{ $currentCurrency === 'USD' ? 'is-active' : '' }}">
            USD
        </button>
        <button type="submit" name="currency" value="BDT" data-currency-toggle data-currency-val="BDT"
            onclick="this.form.querySelectorAll('button').forEach(b => b.removeAttribute('data-clicked')); this.setAttribute('data-clicked', 'true'); if(window.setCurrency){ window.setCurrency('BDT'); return false; }"
            class="currency-btn px-2.5 py-1 rounded-[4px] text-xs font-semibold transition-all cursor-pointer {{ $currentCurrency === 'BDT' ? 'is-active' : '' }}">
            BDT
        </button>
    </div>
</form>
