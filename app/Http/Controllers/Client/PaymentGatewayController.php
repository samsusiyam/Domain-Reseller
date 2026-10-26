<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\Payments\PaymentManager;
use App\Services\Domain\DomainOrderService;
use App\Events\InvoicePaid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected DomainOrderService $orderService
    ) {}

    /**
     * Common user return callback from gateway (Stripe, bKash, Binance).
     */
    public function callback(Request $request, string $gateway)
    {
        try {
            $driver = $this->paymentManager->driver($gateway);
            $verification = $driver->verifyCallback($request);

            if ($verification->isSuccessful && !empty($verification->invoiceId)) {
                $invoice = Invoice::where('uuid', $verification->invoiceId)
                    ->orWhere('id', $verification->invoiceId)
                    ->orWhere('invoice_number', $verification->invoiceId)
                    ->first();

                if ($invoice && !$invoice->isPaid()) {
                    Transaction::create([
                        'user_id' => $invoice->user_id,
                        'invoice_id' => $invoice->id,
                        'gateway' => $gateway,
                        'gateway_transaction_id' => $verification->transactionId,
                        'type' => 'credit',
                        'amount' => $verification->amountPaid,
                        'currency' => $verification->currency,
                        'amount_in_usd' => $verification->amountPaid,
                        'status' => 'completed',
                        'payment_details' => $verification->rawPayload,
                    ]);

                    $invoice->markAsPaid($gateway, $verification->transactionId);

                    if ($invoice->order) {
                        $this->orderService->fulfillOrder($invoice->order);
                    }

                    event(new InvoicePaid($invoice));

                    return redirect()->route('client.invoices.show', $invoice->id)->with('success', 'Payment successful! Your order has been placed into processing.');
                }
            }

            return redirect()->route('client.invoices.index')->with('error', $verification->errorMessage ?? 'Payment was cancelled or failed.');
        } catch (\Throwable $e) {
            Log::channel('payment')->error("Payment callback error: " . $e->getMessage());
            return redirect()->route('client.invoices.index')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function bkashCallback(Request $request)
    {
        return $this->callback($request, 'bkash');
    }

    public function stripeWebhook(Request $request)
    {
        $driver = $this->paymentManager->driver('stripe');
        $res = $driver->processWebhook($request);

        return response()->json(['status' => $res->isSuccessful ? 'success' : 'ignored']);
    }

    public function binanceWebhook(Request $request)
    {
        $driver = $this->paymentManager->driver('binance');
        $res = $driver->processWebhook($request);

        return response()->json(['status' => $res->isSuccessful ? 'success' : 'ignored']);
    }
}
