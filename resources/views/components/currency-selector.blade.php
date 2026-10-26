@php
$currentCurrency = strtoupper(session('currency', config('currencies.default', 'USD')));
@endphp

<form action="{{ route('currency.switch') }}" method="POST" class="inline-flex items-center">
    @csrf
    <div class="inline-flex items-center p-0.5 rounded-[6px] bg-[#F7F7F8] border border-[#E5E7EB] text-[12px] font-medium select-none">
        <button type="submit" name="currency" value="USD"
            class="px-2 py-0.5 rounded-[4px] transition-colors {{ $currentCurrency === 'USD' ? 'bg-white text-[#111827] font-semibold border border-[#E5E7EB]' : 'text-[#6B7280] hover:text-[#111827]' }}">
            USD
        </button>
        <button type="submit" name="currency" value="BDT"
            class="px-2 py-0.5 rounded-[4px] transition-colors {{ $currentCurrency === 'BDT' ? 'bg-white text-[#111827] font-semibold border border-[#E5E7EB]' : 'text-[#6B7280] hover:text-[#111827]' }}">
            BDT
        </button>
    </div>
</form>
