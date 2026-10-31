<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number ?? $invoice->id }} - {{ \App\Models\SystemSetting::get('company_name', 'HostNibo') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            background: #fff;
            padding: 40px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        .logo-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-img {
            max-height: 38px;
            width: auto;
            object-fit: contain;
        }
        .logo-text {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #0f172a;
        }
        .logo-text span {
            color: #F6821F;
        }
        .company-meta {
            margin-top: 8px;
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }
        .inv-details {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .badge-paid { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-unpaid { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .badge-other { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .inv-title {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 24px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .meta-col h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .meta-col p {
            margin-bottom: 2px;
        }
        .customer-name {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }
        th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .item-title {
            font-weight: 700;
            color: #0f172a;
        }
        .item-addon {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }
        .totals {
            margin-left: auto;
            width: 280px;
            padding-top: 16px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            color: #64748b;
        }
        .totals-total {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 4px;
        }
        .totals-due {
            font-size: 14px;
            font-weight: 800;
            border-top: 1px dashed #e2e8f0;
            padding-top: 6px;
            margin-top: 4px;
        }
        .totals-due.due-zero { color: #059669; }
        .totals-due.due-positive { color: #d97706; }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            background: #F6821F;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="header">
        <div>
            @php
                $logoLight = \App\Models\SystemSetting::get('logo_light');
                $company = \App\Models\SystemSetting::get('company_name', 'HostNibo Technologies Ltd.');
                $email = \App\Models\SystemSetting::get('support_email', 'support@hostnibo.com');
            @endphp
            <div class="logo-box">
                @if($logoLight)
                    <img src="{{ asset($logoLight) }}" alt="{{ $company }}" class="logo-img">
                @else
                    <span class="logo-text">Host<span>Nibo</span></span>
                @endif
            </div>
            <div class="company-meta">
                <p><strong>{{ $company }}</strong></p>
                <p>Automated Billing & Cloud Provisioning Engine</p>
                <p>Support: {{ $email }}</p>
            </div>
        </div>

        <div class="inv-details">
            <div class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : ($invoice->status === 'unpaid' ? 'badge-unpaid' : 'badge-other') }}">
                {{ $invoice->status }}
            </div>
            <h1 class="inv-title">INVOICE</h1>
            <p style="font-size: 11px; color: #64748b; margin-top: 4px;">#{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p style="font-size: 11px; color: #64748b;">Date: {{ $invoice->created_at->format('M d, Y') }}</p>
            <p style="font-size: 11px; color: #64748b;">Due: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Due on receipt' }}</p>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-col">
            <h3>Billed To</h3>
            <p class="customer-name">{{ $invoice->user?->name ?? 'Guest/Deleted User' }}</p>
            @if($invoice->user?->company_name)
                <p>{{ $invoice->user->company_name }}</p>
            @endif
            <p style="color: #64748b;">{{ $invoice->user?->email }}</p>
            @if($invoice->user?->phone)
                <p style="color: #64748b;">{{ $invoice->user->phone }}</p>
            @endif
        </div>

        <div class="meta-col" style="text-align: right;">
            <h3>Payment Details</h3>
            <p>Currency: <strong>{{ $invoice->currency ?? 'USD' }}</strong></p>
            @if($invoice->paid_at)
                <p>Paid At: {{ $invoice->paid_at->format('M d, Y H:i') }}</p>
            @endif
            @if($invoice->payment_method)
                <p>Method: {{ ucfirst($invoice->payment_method) }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: center; width: 60px;">Qty</th>
                <th style="text-align: right; width: 100px;">Unit Price</th>
                <th style="text-align: right; width: 100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                @php
                    $lines = explode("\n", $item->description);
                    $mainDesc = $lines[0];
                    $extraDesc = $lines[1] ?? null;
                @endphp
                <tr>
                    <td>
                        <div class="item-title">{{ $mainDesc }}</div>
                        @if($extraDesc)
                            <div class="item-addon">✓ {{ $extraDesc }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; font-family: monospace;">{{ $item->quantity }}</td>
                    <td style="text-align: right; font-family: monospace;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 700; font-family: monospace;">${{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span style="font-family: monospace;">${{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @if($invoice->discount_amount > 0)
            <div class="totals-row" style="color: #059669;">
                <span>Discount</span>
                <span style="font-family: monospace;">-${{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
        @endif
        <div class="totals-row totals-total">
            <span>Total Amount</span>
            <span style="font-family: monospace;">${{ number_format($invoice->total_amount, 2) }} USD</span>
        </div>
        <div class="totals-row totals-due {{ $invoice->balance_due > 0 ? 'due-positive' : 'due-zero' }}">
            <span>Remaining Balance</span>
            <span style="font-family: monospace;">${{ number_format($invoice->balance_due, 2) }} USD</span>
        </div>
    </div>
</body>
</html>
