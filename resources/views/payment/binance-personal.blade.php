@extends('layouts.app', ['title' => 'Binance Pay Direct Transfer', 'header' => 'Binance Pay'])

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
                    ${{ number_format((float)$invoice->balance_due, 2) }}
                    <span class="text-xs font-normal text-slate-400">USDT</span>
                </p>
            </div>
        </div>

        <!-- Instructions & Binance Pay ID -->
        <div class="my-5 p-4 rounded-xl bg-orange-50/60 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/40 text-xs space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">1</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">Open Binance App -> Tap 'Pay' -> 'Send'</span>
            </div>

            <!-- Pay ID Box -->
            <div class="ml-7 space-y-2">
                <div class="p-2.5 bg-white dark:bg-slate-900 rounded-lg border border-orange-200 dark:border-orange-900/60 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-mono text-slate-400 block">Recipient Binance Pay ID</span>
                        <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">
                            {{ \App\Models\SystemSetting::get('binance_personal_pay_id', config('payment.gateways.binance_personal.pay_id', '123456789')) }}
                        </span>
                    </div>
                    <span class="text-[11px] font-semibold text-[#F6821F] bg-orange-50 dark:bg-orange-950/40 px-2 py-1 rounded">0% Fee</span>
                </div>

                @php $payEmail = \App\Models\SystemSetting::get('binance_personal_email', config('payment.gateways.binance_personal.email')); @endphp
                @if($payEmail)
                    <div class="p-2.5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-mono text-slate-400 block">Binance Email Account</span>
                            <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $payEmail }}</span>
                        </div>
                    </div>
                @endif

                @php $usdtAddr = \App\Models\SystemSetting::get('binance_personal_usdt_address', config('payment.gateways.binance_personal.usdt_address')); @endphp
                @if($usdtAddr)
                    <div class="p-2.5 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] uppercase font-mono text-slate-400 block">USDT Deposit Address ({{ \App\Models\SystemSetting::get('binance_personal_network', 'TRC20') }})</span>
                        <span class="font-mono text-xs text-slate-800 dark:text-slate-200 break-all select-all">{{ $usdtAddr }}</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">2</span>
                <span>Send exact amount: <strong>${{ number_format((float)$invoice->balance_due, 2) }} USDT</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-[#F6821F] text-white flex items-center justify-center font-bold text-[10px]">3</span>
                <span>Enter Note / Memo: <strong>{{ $invoice->invoice_number ?? $invoice->id }}</strong></span>
            </div>
        </div>

        <!-- Proof Form -->
        <form action="{{ route('payment.binance-personal.submit', $invoice->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Your Binance Pay ID / Registered Email
                </label>
                <input type="text" name="sender_account" required placeholder="e.g. 987654321 or your@email.com"
                       class="cf-input h-10 text-xs font-mono" value="{{ old('sender_account') }}">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Binance Order ID / Internal Transfer ID / TXID
                </label>
                <input type="text" name="transaction_id" required placeholder="Paste Order ID or Blockchain TxID"
                       class="cf-input h-10 text-xs font-mono uppercase font-bold" value="{{ old('transaction_id') }}">
                <p class="text-[11px] text-slate-400 mt-1">Found in your Binance App payment history or transaction details.</p>
                @error('transaction_id') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <a href="{{ route('client.invoices.show', $invoice->id) }}" class="cf-btn cf-btn-secondary text-xs">
                    Cancel & Back
                </a>
                <button type="submit" class="cf-btn cf-btn-primary text-xs font-semibold px-6 cursor-pointer">
                    Verify & Submit Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
