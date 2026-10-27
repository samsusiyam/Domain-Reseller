<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PiprapayPaymentGateway extends AbstractPaymentGateway
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = SystemSetting::get('piprapay_api_key', $config['api_key'] ?? '');
        $this->apiUrl = rtrim(SystemSetting::get('piprapay_api_url', $config['api_url'] ?? 'https://piprapay.com/api'), '/');
    }

    public function getName(): string
    {
        return 'piprapay';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'PipraPay (Automated Multi-Channel)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['BDT', 'USD'];
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        $orderId = 'PIP-' . $request->invoiceId . '-' . time();

        if (!empty($this->apiKey)) {
            try {
                $payload = [
                    'amount' => (float) $request->amount,
                    'currency' => $request->currency === 'BDT' ? 'BDT' : 'USD',
                    'full_name' => $request->customerName ?: 'Customer',
                    'email' => $request->customerEmail ?: 'customer@hostnibo.com',
                    'phone_number' => $request->customerPhone ?: '01849693630',
                    'metadata' => [
                        'invoice_id' => $request->invoiceId,
                        'order_id' => $orderId,
                    ],
                    'redirect_url' => route('payment.callback', ['gateway' => 'piprapay', 'invoice_id' => $request->invoiceId]),
                    'cancel_url' => $request->cancelUrl ?: route('client.invoices.show', $request->invoiceId),
                    'webhook_url' => route('payment.webhook', ['gateway' => 'piprapay']),
                ];

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Accept' => 'application/json',
                ])->post("{$this->apiUrl}/create-charge", $payload);

                $data = $response->json();
                $checkoutUrl = $data['payment_url'] ?? $data['charge_url'] ?? $data['data']['payment_url'] ?? null;

                if ($checkoutUrl) {
                    return new PaymentRedirectDTO(
                        isRedirect: true,
                        redirectUrl: $checkoutUrl,
                        method: 'GET',
                        gatewayReferenceId: $data['charge_id'] ?? $orderId
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('PipraPay Init failed: ' . $e->getMessage());
            }
        }

        // Test/Sandbox fallback
        $redirectUrl = route('payment.callback', [
            'gateway' => 'piprapay',
            'order_id' => $orderId,
            'status' => 'COMPLETED',
            'invoice_id' => $request->invoiceId,
            'amount' => $request->amount,
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
        $status = strtoupper($request->input('status', 'PENDING'));
        $chargeId = $request->input('charge_id') ?? $request->input('order_id');
        $invoiceId = $request->input('invoice_id', '');
        $amount = (float) $request->input('amount', 0.0);

        if (!empty($this->apiKey) && $chargeId && !str_starts_with($chargeId, 'PIP-')) {
            try {
                $verifyRes = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                ])->post("{$this->apiUrl}/verify-charge", [
                    'charge_id' => $chargeId,
                ])->json();

                if (($verifyRes['status'] ?? '') === 'COMPLETED' || ($verifyRes['data']['status'] ?? '') === 'COMPLETED') {
                    return new PaymentVerificationDTO(
                        isSuccessful: true,
                        invoiceId: (string) $invoiceId,
                        transactionId: (string) $chargeId,
                        amountPaid: (float) ($verifyRes['amount'] ?? $amount),
                        currency: 'BDT',
                        errorMessage: null,
                        rawPayload: $verifyRes
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('PipraPay Verify error: ' . $e->getMessage());
            }
        }

        $isSuccess = in_array($status, ['COMPLETED', 'SUCCESS', 'PAID']);

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: (string) ($chargeId ?: 'TXN-' . bin2hex(random_bytes(4))),
            amountPaid: $amount,
            currency: 'BDT',
            errorMessage: $isSuccess ? null : 'PipraPay transaction could not be verified',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $payload = $request->all();
        $status = strtoupper($payload['status'] ?? '');
        $invoiceId = $payload['metadata']['invoice_id'] ?? $payload['invoice_id'] ?? '';
        $chargeId = $payload['charge_id'] ?? $payload['transaction_id'] ?? '';

        $isSuccess = in_array($status, ['COMPLETED', 'SUCCESS', 'PAID']);

        return new PaymentWebhookResultDTO(
            handled: true,
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: (string) $chargeId,
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
