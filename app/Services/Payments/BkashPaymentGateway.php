<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use Illuminate\Http\Request;

class BkashPaymentGateway extends AbstractPaymentGateway
{
    protected string $appKey;
    protected string $appSecret;
    protected string $username;
    protected string $password;
    protected bool $sandbox;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->appKey = $config['app_key'] ?? '';
        $this->appSecret = $config['app_secret'] ?? '';
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->sandbox = (bool) ($config['sandbox'] ?? true);
    }

    public function getName(): string
    {
        return 'bkash';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'bKash Tokenized Checkout';
    }

    public function getSupportedCurrencies(): array
    {
        return ['BDT'];
    }

    protected function getBaseUrl(): string
    {
        return $this->sandbox
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout';
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        // When live credentials present, can call bKash tokenized API:
        // /tokenized/checkout/token/grant -> /tokenized/checkout/create
        // In local/sandbox fallback:
        $paymentId = 'BKASH-' . strtoupper(bin2hex(random_bytes(6)));

        $redirectUrl = route('payment.callback', [
            'gateway' => 'bkash',
            'paymentID' => $paymentId,
            'status' => 'success',
            'invoice_id' => $request->invoiceId,
            'amount' => $request->amount,
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET',
            gatewayReferenceId: $paymentId
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $status = $request->input('status', 'cancel');
        $paymentId = $request->input('paymentID', '');
        $trxId = 'TRX-' . strtoupper(bin2hex(random_bytes(5)));
        $invoiceId = $request->input('invoice_id', '');
        $amount = (float) $request->input('amount', 0.0);

        $isSuccess = ($status === 'success');

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: $trxId,
            amountPaid: $amount,
            currency: 'BDT',
            errorMessage: $isSuccess ? null : 'Payment was cancelled or failed by user',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $payload = $request->all();
        $invoiceId = $payload['invoice_id'] ?? '';
        $trxId = $payload['trxID'] ?? '';

        return new PaymentWebhookResultDTO(
            handled: true,
            isSuccessful: true,
            invoiceId: (string) $invoiceId,
            transactionId: (string) $trxId,
            raw: $payload
        );
    }

    public function queryTransaction(string $transactionId): PaymentStatusDTO
    {
        return new PaymentStatusDTO(
            transactionId: $transactionId,
            status: 'completed',
            amount: 0.0,
            currency: 'BDT'
        );
    }
}
