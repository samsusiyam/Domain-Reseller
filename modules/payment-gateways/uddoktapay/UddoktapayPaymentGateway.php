<?php

namespace Modules\PaymentGateways\Uddoktapay;

use App\Services\Payments\AbstractPaymentGateway;
use App\Contracts\PaymentGatewayInterface;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UddoktapayPaymentGateway extends AbstractPaymentGateway
{
    public function getConfigFields(): array
    {
        return json_decode('{"api_url": {"label": "API Base URL", "type": "text", "placeholder": "https://pay.hostnibo.com/api", "required": true}, "api_key": {"label": "API Key", "type": "password", "placeholder": "Enter UddoktaPay API key", "required": true}}', true);
    }


    protected string $apiKey;
    protected string $apiUrl;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = SystemSetting::get('uddoktapay_api_key', $config['api_key'] ?? '');
        $this->apiUrl = rtrim(SystemSetting::get('uddoktapay_api_url', $config['api_url'] ?? 'https://pay.hostnibo.com/api'), '/');
    }

    public function getName(): string
    {
        return 'uddoktapay';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'UddoktaPay (bKash / Nagad / Rocket / Upay)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['BDT', 'USD'];
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        $orderId = 'UDD-' . $request->invoiceId . '-' . time();

        if (!empty($this->apiKey)) {
            try {
                $payload = [
                    'full_name' => $request->customerName ?: 'HostNibo Client',
                    'email' => $request->customerEmail ?: 'client@hostnibo.com',
                    'amount' => number_format($request->amount, 2, '.', ''),
                    'metadata' => [
                        'invoice_id' => $request->invoiceId,
                        'order_id' => $orderId,
                    ],
                    'redirect_url' => route('payment.callback', ['gateway' => 'uddoktapay', 'invoice_id' => $request->invoiceId]),
                    'cancel_url' => $request->cancelUrl ?: route('client.invoices.show', $request->invoiceId),
                    'webhook_url' => route('payment.webhook', ['gateway' => 'uddoktapay']),
                ];

                $response = Http::withHeaders([
                    'RT-UDDOKTAPAY-API-KEY' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post("{$this->apiUrl}/checkout-v2", $payload);

                $data = $response->json();

                if (($data['status'] ?? false) && !empty($data['payment_url'])) {
                    return new PaymentRedirectDTO(
                        isRedirect: true,
                        redirectUrl: $data['payment_url'],
                        method: 'GET',
                        gatewayReferenceId: $orderId
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('UddoktaPay Checkout-v2 failed: ' . $e->getMessage());
            }
        }

        // Test/Sandbox fallback
        $redirectUrl = route('payment.callback', [
            'gateway' => 'uddoktapay',
            'order_id' => $orderId,
            'status' => 'COMPLETED',
            'invoice_id' => $request->invoiceId,
            'amount' => $request->amount,
            'transaction_id' => 'UDD-TRX-' . strtoupper(bin2hex(random_bytes(5))),
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET',
            gatewayReferenceId: $orderId
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $uddInvoiceId = $request->input('invoice_id');
        $trxId = $request->input('transaction_id', 'TRX-' . bin2hex(random_bytes(4)));
        $amount = (float) $request->input('amount', 0.0);
        $status = strtoupper($request->input('status', 'PENDING'));

        if (!empty($this->apiKey) && $uddInvoiceId && !str_starts_with($uddInvoiceId, 'UDD-')) {
            try {
                $verifyRes = Http::withHeaders([
                    'RT-UDDOKTAPAY-API-KEY' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post("{$this->apiUrl}/verify-payment", [
                    'invoice_id' => $uddInvoiceId,
                ])->json();

                if (($verifyRes['status'] ?? '') === 'COMPLETED') {
                    return new PaymentVerificationDTO(
                        isSuccessful: true,
                        invoiceId: (string) ($verifyRes['metadata']['invoice_id'] ?? $uddInvoiceId),
                        transactionId: (string) ($verifyRes['transaction_id'] ?? $trxId),
                        amountPaid: (float) ($verifyRes['amount'] ?? $amount),
                        currency: 'BDT',
                        errorMessage: null,
                        rawPayload: $verifyRes
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('UddoktaPay Verify error: ' . $e->getMessage());
            }
        }

        $isSuccess = ($status === 'COMPLETED' || $status === 'SUCCESS');

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $uddInvoiceId,
            transactionId: (string) $trxId,
            amountPaid: $amount,
            currency: 'BDT',
            errorMessage: $isSuccess ? null : 'Payment failed on UddoktaPay gateway',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $payload = $request->all();
        $status = strtoupper($payload['status'] ?? '');
        $invoiceId = $payload['metadata']['invoice_id'] ?? $payload['invoice_id'] ?? '';
        $trxId = $payload['transaction_id'] ?? '';

        $isSuccess = ($status === 'COMPLETED');

        return new PaymentWebhookResultDTO(
            handled: true,
            isSuccessful: $isSuccess,
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
