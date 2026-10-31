<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\Payments\PaymentManager;
use App\Services\Domain\DomainOrderService;
use App\Events\InvoicePaid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected DomainOrderService $orderService
    ) {}

    /**
     * Show bKash Personal payment confirmation page.
     */
    public function showBkashPersonal(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('client.invoices.show', $invoice->id)->with('info', 'This invoice has already been paid.');
        }

        return view('payment.bkash-personal', compact('invoice'));
    }

    /**
     * Handle bKash Personal submission.
     */
    public function submitBkashPersonal(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'sender_phone' => ['required', 'string', 'min:10', 'max:20'],
            'transaction_id' => ['required', 'string', 'min:5', 'max:50'],
        ]);

        $trxId = strtoupper(trim($request->transaction_id));
        $senderPhone = trim($request->sender_phone);

        $transaction = Transaction::create([
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'gateway' => 'bkash_personal',
            'gateway_transaction_id' => $trxId,
            'type' => 'credit',
            'amount' => $invoice->balance_due,
            'currency' => 'BDT',
            'amount_in_usd' => $invoice->balance_due,
            'status' => 'pending',
            'payment_details' => [
                'sender_phone' => $senderPhone,
                'transaction_id' => $trxId,
                'method' => 'bKash Personal (Send Money)',
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('client.invoices.show', $invoice->id)->with('success', "Your bKash Personal submission (TrxID: {$trxId}) has been received! Our billing team will verify and activate your order shortly.");
    }

    /**
     * Show Binance Personal Pay ID screen.
     */
    public function showBinancePersonal(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('client.invoices.show', $invoice->id)->with('info', 'This invoice has already been paid.');
        }

        return view('payment.binance-personal', compact('invoice'));
    }

    /**
     * Handle Binance Personal submission.
     */
    public function submitBinancePersonal(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'sender_account' => ['required', 'string', 'max:100'],
            'transaction_id' => ['required', 'string', 'min:5', 'max:100'],
        ]);

        $txId = trim($request->transaction_id);
        $sender = trim($request->sender_account);

        /** @var \App\Services\Payments\BinancePersonalPaymentGateway $driver */
        $driver = $this->paymentManager->driver('binance_personal');
        $autoVerify = $driver->tryAutoVerify($txId, (float) $invoice->balance_due);

        if ($autoVerify && !empty($autoVerify['verified'])) {
            // Instant API Auto-Verification Success!
            Transaction::create([
                'user_id' => $invoice->user_id,
                'invoice_id' => $invoice->id,
                'gateway' => 'binance_personal',
                'gateway_transaction_id' => $txId,
                'type' => 'credit',
                'amount' => $invoice->balance_due,
                'currency' => 'USDT',
                'amount_in_usd' => $invoice->balance_due,
                'status' => 'completed',
                'payment_details' => array_merge($autoVerify, ['sender' => $sender]),
            ]);

            $invoice->markAsPaid('binance_personal', $txId);

            if ($invoice->order) {
                $this->orderService->fulfillOrder($invoice->order);
            } else {
                $invoice->user->creditBalance((float) $invoice->total_amount);
            }

            event(new InvoicePaid($invoice));

            return redirect()->route('client.invoices.show', $invoice->id)->with('success', "Payment automatically verified via Binance API! Order activated.");
        }

        // Pending Admin Verification
        Transaction::create([
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'gateway' => 'binance_personal',
            'gateway_transaction_id' => $txId,
            'type' => 'credit',
            'amount' => $invoice->balance_due,
            'currency' => 'USDT',
            'amount_in_usd' => $invoice->balance_due,
            'status' => 'pending',
            'payment_details' => [
                'sender_account' => $sender,
                'transaction_id' => $txId,
                'method' => 'Binance Personal Direct Pay ID',
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('client.invoices.show', $invoice->id)->with('success', "Your Binance payment submission ({$txId}) has been received! Our billing department will confirm and activate your service shortly.");
    }

    /**
     * Common user return callback from all gateways.
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
                    } else {
                        // Wallet balance deposit auto-credit
                        $invoice->user->creditBalance((float) $invoice->total_amount);
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

    /**
     * Unified Webhook endpoint for all gateways.
     */
    public function webhook(Request $request, string $gateway)
    {
        try {
            $driver = $this->paymentManager->driver($gateway);
            $res = $driver->processWebhook($request);

            if ($res->isSuccessful && !empty($res->invoiceId)) {
                $invoice = Invoice::where('uuid', $res->invoiceId)
                    ->orWhere('id', $res->invoiceId)
                    ->orWhere('invoice_number', $res->invoiceId)
                    ->first();

                if ($invoice && !$invoice->isPaid()) {
                    Transaction::create([
                        'user_id' => $invoice->user_id,
                        'invoice_id' => $invoice->id,
                        'gateway' => $gateway,
                        'gateway_transaction_id' => $res->transactionId,
                        'type' => 'credit',
                        'amount' => $invoice->balance_due,
                        'currency' => $invoice->currency,
                        'amount_in_usd' => $invoice->balance_due,
                        'status' => 'completed',
                        'payment_details' => $res->raw,
                    ]);

                    $invoice->markAsPaid($gateway, $res->transactionId);

                    if ($invoice->order) {
                        $this->orderService->fulfillOrder($invoice->order);
                    } else {
                        $invoice->user->creditBalance((float) $invoice->total_amount);
                    }

                    event(new InvoicePaid($invoice));
                }
            }

            return response()->json(['status' => $res->isSuccessful ? 'success' : 'ignored']);
        } catch (\Throwable $e) {
            Log::channel('payment')->error("Webhook error for {$gateway}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function bkashCallback(Request $request)
    {
        return $this->callback($request, 'bkash');
    }

    public function stripeWebhook(Request $request)
    {
        return $this->webhook($request, 'stripe');
    }

    public function binanceWebhook(Request $request)
    {
        return $this->webhook($request, 'binance');
    }

    /**
     * Handle payment checkout initialization from cart/checkout.
     */
    public function checkout(Request $request, Invoice $invoice, string $gateway)
    {
        if ($invoice->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('client.invoices.show', $invoice->id)->with('info', 'This invoice has already been paid.');
        }

        if ($gateway === 'balance') {
            try {
                $this->orderService->payWithBalance(Auth::user(), $invoice);
                return redirect()->route('client.invoices.show', $invoice->id)->with('success', 'Paid successfully with account balance!');
            } catch (\Throwable $e) {
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', $e->getMessage());
            }
        }

        try {
            $driver = $this->paymentManager->driver($gateway);
            $supportedCurrs = $driver->getSupportedCurrencies();
            $targetCurr = in_array('BDT', $supportedCurrs) && !in_array('USD', $supportedCurrs) ? 'BDT' : 'USD';
            $amount = $targetCurr === 'BDT'
                ? app(\App\Services\Currency\CurrencyConverter::class)->usdToBdt((float)$invoice->balance_due)
                : (float)$invoice->balance_due;

            $paymentReq = new \App\DTOs\PaymentRequestDTO(
                invoiceId: (string) $invoice->id,
                amount: $amount,
                currency: $targetCurr,
                returnUrl: route('payment.callback', ['gateway' => $gateway]),
                cancelUrl: route('client.invoices.show', $invoice->id),
                customerEmail: Auth::user()->email,
                customerName: Auth::user()->name,
                customerPhone: Auth::user()->phone ?? '01700000000'
            );

            $redirect = $driver->initializePayment($paymentReq);

            if ($redirect->isRedirect && !empty($redirect->redirectUrl)) {
                return redirect()->away($redirect->redirectUrl);
            }

            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Unable to initialize ' . ucfirst($gateway) . ' gateway.');
        } catch (\Throwable $e) {
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Payment gateway error: ' . $e->getMessage());
        }
    }

}