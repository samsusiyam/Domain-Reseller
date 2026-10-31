@extends('layouts.app', ['title' => 'bKash Personal Payment', 'header' => 'bKash Personal'])

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Invoice Summary Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <span class="text-xs uppercase font-mono tracking-wider text-slate-500">Invoice Reference</span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white font-mono mt-0.5">#{{ $invoice->invoice_number ?? $invoice->id }}</h2>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500">Amount Due</span>
                <p class="text-xl font-black text-[#F6821F] font-mono">
                    ৳{{ number_format(app(\App\Services\Currency\CurrencyConverter::class)->usdToBdt((float)$invoice->balance_due), 2) }}
                    <span class="text-xs font-normal text-slate-400">(${{ number_format((float)$invoice->balance_due, 2) }})</span>
                </p>
            </div>
        </div>

        <!-- Step-by-step Instructions -->
        <div class="my-5 p-4 rounded-xl bg-orange-50/60 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/40 text-xs space-y-2.5">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">1</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">Open your bKash App or dial *247#</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">2</span>
                <span>Select <strong>Send Money</strong> to Personal Number:</span>
            </div>
            <div class="ml-7 p-2.5 bg-white dark:bg-slate-900 rounded-lg border border-orange-200 dark:border-orange-900/60 font-mono text-sm font-bold text-slate-900 dark:text-white flex items-center justify-between">
                <span>{{ \App\Models\SystemSetting::get('bkash_personal_number', config('payment.gateways.bkash_personal.personal_number', '01849693630')) }}</span>
                <span class="text-[10px] font-sans font-medium text-slate-400 uppercase">bKash Personal</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">3</span>
                <span>Enter Amount: <strong>৳{{ number_format(app(\App\Services\Currency\CurrencyConverter::class)->usdToBdt((float)$invoice->balance_due), 2) }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">4</span>
                <span>Enter Reference: <strong>{{ $invoice->invoice_number ?? $invoice->id }}</strong></span>
            </div>
        </div>

        <!-- Submission Form -->
        <form action="{{ route('payment.bkash-personal.submit', $invoice->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Your bKash Sender Phone Number
                </label>
                <input type="text" name="sender_phone" required placeholder="01XXXXXXXXX"
                       class="cf-input h-10 text-xs font-mono" value="{{ old('sender_phone') }}">
                @error('sender_phone') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    bKash Transaction ID (TrxID)
                </label>
                <input type="text" name="transaction_id" required placeholder="e.g. BL94A8K0P1"
                       class="cf-input h-10 text-xs font-mono uppercase font-bold" value="{{ old('transaction_id') }}">
                <p class="text-[11px] text-slate-400 mt-1">Copy the TrxID from the bKash confirmation SMS or statement.</p>
                @error('transaction_id') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <a href="{{ route('client.invoices.show', $invoice->id) }}" class="cf-btn cf-btn-secondary text-xs">
                    Cancel & Back
                </a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs font-semibold px-6 cursor-pointer">
                    Confirm & Submit Proof
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
