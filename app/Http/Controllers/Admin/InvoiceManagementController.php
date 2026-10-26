<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\User;
use App\Events\InvoicePaid;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class InvoiceManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Lists invoices with status and user filters.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->input('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', $search)->orWhere('email', 'like', $search);
                  });
            });
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        $totalPaid = (float) Invoice::where('status', 'paid')->sum('total_amount');
        $totalUnpaid = (float) Invoice::where('status', 'unpaid')->sum('balance_due');

        return view('admin.invoices.index', compact('invoices', 'totalPaid', 'totalUnpaid'));
    }

    /**
     * Create custom invoice form.
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        return view('admin.invoices.create', compact('users'));
    }

    /**
     * Store custom invoice with line items.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'         => ['required', 'exists:users,id'],
            'due_date'        => ['required', 'date'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.desc'    => ['required', 'string', 'max:255'],
            'items.*.amount'  => ['required', 'numeric', 'min:0.01'],
            'items.*.qty'     => ['required', 'integer', 'min:1'],
        ]);

        $subtotal = 0;
        foreach ($request->input('items') as $item) {
            $subtotal += ((float)$item['amount'] * (int)$item['qty']);
        }

        $invoice = Invoice::create([
            'user_id'         => $request->input('user_id'),
            'subtotal'        => $subtotal,
            'discount_amount' => 0.0000,
            'tax_amount'      => 0.0000,
            'total_amount'    => $subtotal,
            'balance_due'     => $subtotal,
            'currency'        => 'USD',
            'status'          => 'unpaid',
            'due_date'        => $request->input('due_date'),
        ]);

        foreach ($request->input('items') as $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['desc'],
                'quantity'    => (int)$item['qty'],
                'unit_price'  => (float)$item['amount'],
                'total_price' => (float)$item['amount'] * (int)$item['qty'],
            ]);
        }

        $this->activityLogger->log('invoice_created', "Admin created invoice #{$invoice->invoice_number} for \${$subtotal}", $invoice);

        return redirect()->route('admin.invoices.show', $invoice->id)->with('success', 'Invoice generated successfully.');
    }

    /**
     * Show invoice details and items.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['user', 'items', 'transactions', 'order'])->findOrFail($id);
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Mark invoice as paid manually.
     */
    public function markPaid($id)
    {
        $invoice = Invoice::with('order.items')->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('info', 'Invoice is already marked paid.');
        }

        $invoice->update([
            'status'      => 'paid',
            'balance_due' => 0.0000,
            'paid_at'     => now(),
        ]);

        // Create transaction
        Transaction::create([
            'user_id'        => $invoice->user_id,
            'invoice_id'     => $invoice->id,
            'type'           => 'payment',
            'amount'         => $invoice->total_amount,
            'currency'       => $invoice->currency,
            'exchange_rate'  => 1.000000,
            'payment_method' => 'manual_admin',
            'reference_id'   => 'ADM-PAID-' . strtoupper(uniqid()),
            'status'         => 'completed',
            'description'    => "Payment for Invoice #{$invoice->invoice_number} (Marked by Admin)",
        ]);

        // Fire invoice paid event to trigger provisioning
        event(new InvoicePaid($invoice));

        $this->activityLogger->log('invoice_marked_paid', "Admin marked invoice #{$invoice->invoice_number} as paid", $invoice);

        return back()->with('success', "Invoice #{$invoice->invoice_number} marked as paid.");
    }

    /**
     * Cancel invoice.
     */
    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'cancelled']);

        $this->activityLogger->log('invoice_cancelled', "Admin cancelled invoice #{$invoice->invoice_number}", $invoice);

        return back()->with('success', "Invoice #{$invoice->invoice_number} cancelled.");
    }

    /**
     * Refund invoice.
     */
    public function refund($id)
    {
        $invoice = Invoice::with('user')->findOrFail($id);

        if ($invoice->status !== 'paid') {
            return back()->with('error', 'Only paid invoices can be refunded.');
        }

        $user = $invoice->user;
        $refundAmount = (float)$invoice->total_amount;

        $user->increment('wallet_balance', $refundAmount);
        $invoice->update(['status' => 'refunded']);

        Transaction::create([
            'user_id'        => $user->id,
            'invoice_id'     => $invoice->id,
            'type'           => 'credit',
            'amount'         => $refundAmount,
            'currency'       => 'USD',
            'exchange_rate'  => 1.000000,
            'payment_method' => 'wallet_refund',
            'reference_id'   => 'REF-INV-' . strtoupper(uniqid()),
            'status'         => 'completed',
            'description'    => "Refund for Invoice #{$invoice->invoice_number}",
        ]);

        $this->activityLogger->log('invoice_refunded', "Admin refunded invoice #{$invoice->invoice_number} (\${$refundAmount})", $invoice);

        return back()->with('success', "Invoice #{$invoice->invoice_number} refunded to user wallet.");
    }

    /**
     * Generate HTML printable invoice.
     */
    public function generatePdf($id)
    {
        $invoice = Invoice::with(['user', 'items', 'transactions'])->findOrFail($id);
        return view('admin.invoices.print', compact('invoice'));
    }
}
