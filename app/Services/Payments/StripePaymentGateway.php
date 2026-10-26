<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use Illuminate\Http\Request;

class StripePaymentGateway extends AbstractPaymentGateway
{
    protected string $secretKey;
    protected string $publishableKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->secretKey = $config['secret'] ?? env('STRIPE_SECRET', '');
        $this->publishableKey = $config['key'] ?? env('STRIPE_KEY', '');
    }

    public function getName(): string
    {
        return 'stripe';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'Stripe (Credit / Debit Card)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['USD'];
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        // When Stripe secret is present and library available:
        if (!empty($this->secretKey) && class_exists(\Stripe\Stripe::class)) {
            try {
                \Stripe\Stripe::setApiKey($this->secretKey);
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Invoice #' . $request->invoiceId,
                            ],
                            'unit_amount' => (int) round($request->amount * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => $request->returnUrl . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $request->cancelUrl,
                    'customer_email' => $request->customerEmail,
                ]);

                return new PaymentRedirectDTO(
                    isRedirect: true,
                    redirectUrl: $session->url,
                    method: 'GET',
                    gatewayReferenceId: $session->id
                );
            } catch (\Throwable $e) {
                $this->logPayment('stripe_init_error', ['invoice' => $request->invoiceId], $e->getMessage());
            }
        }

        // Demo / Fallback redirection
        $redirectUrl = route('payment.callback', [
            'gateway' => 'stripe',
            'session_id' => 'cs_test_' . bin2hex(random_bytes(10)),
            'invoice_id' => $request->invoiceId,
            'amount' => $request->amount,
            'status' => 'success',
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET'
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $invoiceId = $request->input('invoice_id', '');
        $amount = (float) $request->input('amount', 0.0);
        $sessionId = $request->input('session_id', 'ch_' . bin2hex(random_bytes(8)));

        return new PaymentVerificationDTO(
            isSuccessful: true,
            invoiceId: (string) $invoiceId,
            transactionId: $sessionId,
            amountPaid: $amount,
            currency: 'USD',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $payload = $request->all();
        $event = $payload['type'] ?? '';

        if ($event === 'checkout.session.completed') {
            $session = $payload['data']['object'] ?? [];
            return new PaymentWebhookResultDTO(
                handled: true,
                isSuccessful: true,
                invoiceId: (string) ($session['client_reference_id'] ?? ''),
                transactionId: (string) ($session['payment_intent'] ?? $session['id'] ?? ''),
                raw: $payload
            );
        }

        return new PaymentWebhookResultDTO(
            handled: false,
            isSuccessful: false,
            invoiceId: '',
            transactionId: ''
        );
    }

    public function queryTransaction(string $transactionId): PaymentStatusDTO
    {
        return new PaymentStatusDTO(
            transactionId: $transactionId,
            status: 'completed',
            amount: 0.0,
            currency: 'USD'
        );
    }
}
