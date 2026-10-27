<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Events\InvoicePaid;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\Request;

class ManualPaymentController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    /**
     * Display list of manual payment verification submissions.
     */
    public function index()
    {
        $pendingPayments = Transaction::whereIn('gateway', ['manual', 'bkash_personal', 'binance_personal'])->orWhereNull('gateway')
            ->with(['user', 'invoice'])
            ->latest()
            ->paginate(15);

        return view('admin.payments.manual', compact('pendingPayments'));
    }

    /**
     * Approve manual payment submission.
     */
    public function approve($id)
    {
        $transaction = Transaction::whereIn('gateway', ['manual', 'bkash_personal', 'binance_personal'])->orWhereNull('gateway')->findOrFail($id);

        if ($transaction->status === 'completed') {
            return back()->with('info', 'This payment is already approved.');
        }

        $transaction->update(['status' => 'completed']);

        // If transaction has an invoice attached
        if ($transaction->invoice_id) {
            $invoice = Invoice::find($transaction->invoice_id);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->markAsPaid($transaction->gateway ?? 'manual', $transaction->gateway_transaction_id);
                if ($invoice->order) {
                    app(\App\Services\Domain\DomainOrderService::class)->fulfillOrder($invoice->order);
                } else {
                    $invoice->user->creditBalance((float) $invoice->total_amount);
                }
                event(new InvoicePaid($invoice));
            }
        } else {
            // It was a direct wallet deposit
            $transaction->user->creditBalance((float) $transaction->amount);
        }

        $this->activityLogger->log(
            'manual_payment_approved',
            "Admin approved manual payment Ref: {($transaction->transaction_number ?? $transaction->gateway_transaction_id ?? 'TXN-' . $transaction->id)} for \${$transaction->amount}",
            $transaction
        );

        return back()->with('success', "Payment {($transaction->transaction_number ?? $transaction->gateway_transaction_id ?? 'TXN-' . $transaction->id)} approved successfully.");
    }

    /**
     * Reject manual payment submission.
     */
    public function reject(Request $request, $id)
    {
        $transaction = Transaction::whereIn('gateway', ['manual', 'bkash_personal', 'binance_personal'])->orWhereNull('gateway')->findOrFail($id);

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $transaction->update([
            'status'      => 'failed',
            'payment_details' => array_merge((array)$transaction->payment_details, ['rejection_reason' => $request->input('reason')]),
        ]);

        $this->activityLogger->log(
            'manual_payment_rejected',
            "Admin rejected manual payment Ref: {($transaction->transaction_number ?? $transaction->gateway_transaction_id ?? 'TXN-' . $transaction->id)}. Reason: {$request->input('reason')}",
            $transaction
        );

        return back()->with('success', "Payment {($transaction->transaction_number ?? $transaction->gateway_transaction_id ?? 'TXN-' . $transaction->id)} rejected.");
    }
}
