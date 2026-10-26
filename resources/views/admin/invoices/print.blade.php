<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number ?? $invoice->id }} - DomainReseller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; background: #fff !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 font-sans antialiased">
    <div class="max-w-3xl mx-auto mb-4 no-print flex justify-between items-center">
        <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Invoice</a>
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg text-sm shadow hover:bg-indigo-700">
            Print / Save as PDF
        </button>
    </div>

    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200">
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Domain<span class="text-indigo-600">Reseller</span></h1>
                <p class="text-xs text-slate-500 mt-1">Domain & Cloud Management Platform</p>
                <p class="text-xs text-slate-500">support@domainreseller.com</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-black text-slate-900 uppercase">Invoice</h2>
                <p class="text-xs text-slate-600 mt-1"><strong>Invoice #:</strong> #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p class="text-xs text-slate-600"><strong>Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
                <p class="text-xs text-slate-600"><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</p>
                <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-bold uppercase
                    {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>

        <!-- Billed To -->
        <div class="mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Billed To</h3>
            <p class="font-bold text-slate-900">{{ $invoice->user?->name ?? 'Guest' }}</p>
            @if($invoice->user?->company_name)
                <p class="text-xs text-slate-600">{{ $invoice->user->company_name }}</p>
            @endif
            <p class="text-xs text-slate-600">{{ $invoice->user?->email }}</p>
            @if($invoice->user?->phone)
                <p class="text-xs text-slate-600">{{ $invoice->user->phone }}</p>
            @endif
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-sm mb-6 border-b border-slate-200 pb-4">
            <thead>
                <tr class="text-xs uppercase text-slate-400 border-b border-slate-200">
                    <th class="pb-2">Description</th>
                    <th class="pb-2 text-center">Qty</th>
                    <th class="pb-2 text-right">Unit Price</th>
                    <th class="pb-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-3 font-medium text-slate-800">{{ $item->description }}</td>
                        <td class="py-3 text-center text-slate-600">{{ $item->quantity }}</td>
                        <td class="py-3 text-right text-slate-600">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 text-right font-bold text-slate-900">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end text-sm">
            <div class="w-64 space-y-2">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold">${{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-base text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Amount:</span>
                    <span>${{ number_format($invoice->total_amount, 2) }} USD</span>
                </div>
                <div class="flex justify-between font-bold text-sm text-slate-800 pt-1">
                    <span>Balance Due:</span>
                    <span>${{ number_format($invoice->balance_due, 2) }} USD</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
