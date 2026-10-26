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
        $pendingPayments = Transaction::where('payment_method', 'manual')
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
        $transaction = Transaction::where('payment_method', 'manual')->findOrFail($id);

        if ($transaction->status === 'completed') {
            return back()->with('info', 'This payment is already approved.');
        }

        $transaction->update(['status' => 'completed']);

        // If transaction has an invoice attached
        if ($transaction->invoice_id) {
            $invoice = Invoice::find($transaction->invoice_id);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update([
                    'status'      => 'paid',
                    'balance_due' => 0.0000,
                    'paid_at'     => now(),
                ]);
                event(new InvoicePaid($invoice));
            }
        } else {
            // It was a direct wallet deposit
            $transaction->user->increment('wallet_balance', $transaction->amount);
        }

        $this->activityLogger->log(
            'manual_payment_approved',
            "Admin approved manual payment Ref: {$transaction->reference_id} for \${$transaction->amount}",
            $transaction
        );

        return back()->with('success', "Payment {$transaction->reference_id} approved successfully.");
    }

    /**
     * Reject manual payment submission.
     */
    public function reject(Request $request, $id)
    {
        $transaction = Transaction::where('payment_method', 'manual')->findOrFail($id);

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $transaction->update([
            'status'      => 'failed',
            'description' => $transaction->description . " | Rejection Reason: " . $request->input('reason'),
        ]);

        $this->activityLogger->log(
            'manual_payment_rejected',
            "Admin rejected manual payment Ref: {$transaction->reference_id}. Reason: {$request->input('reason')}",
            $transaction
        );

        return back()->with('success', "Payment {$transaction->reference_id} rejected.");
    }
}
