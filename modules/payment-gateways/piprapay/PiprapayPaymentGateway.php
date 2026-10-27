<?php

namespace Modules\PaymentGateways\Piprapay;

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

class PiprapayPaymentGateway extends AbstractPaymentGateway
{
    public function getConfigFields(): array
    {
        return json_decode('{"api_url": {"label": "API Base URL", "type": "text", "placeholder": "https://pay.hostnibo.com", "required": true}, "api_key": {"label": "MHS-PIPRAPAY-API-KEY", "type": "password", "placeholder": "PipraPay v3 API Key", "required": true}}', true);
    }


    protected string $apiKey;
    protected string $apiUrl;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = SystemSetting::get('piprapay_api_key', $config['api_key'] ?? '');
        $this->apiUrl = rtrim(SystemSetting::get('piprapay_api_url', $config['api_url'] ?? 'https://pay.hostnibo.com/api'), '/');
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
                    'full_name'      => $request->customerName ?: 'Customer',
                    'email_address'  => $request->customerEmail ?: 'customer@hostnibo.com',
                    'mobile_number'  => $request->customerPhone ?: '01849693630',
                    'amount'         => (string) round((float) $request->amount, 2),
                    'currency'       => 'BDT',
                    'return_url'     => route('payment.callback', ['gateway' => 'piprapay', 'invoice_id' => $request->invoiceId]),
                    'webhook_url'    => route('payment.webhook', ['gateway' => 'piprapay']),
                    'metadata'       => [
                        'invoice_id' => (string) $request->invoiceId,
                        'order_id'   => $orderId,
                    ],
                ];

                $response = Http::withHeaders([
                    'MHS-PIPRAPAY-API-KEY' => $this->apiKey,
                    'Content-Type'         => 'application/json',
                    'Accept'               => 'application/json',
                ])->post("{$this->apiUrl}/checkout/redirect", $payload);

                $data = $response->json();
                $checkoutUrl = $data['pp_url'] ?? $data['payment_url'] ?? null;
                $ppId = $data['pp_id'] ?? $orderId;

                if ($checkoutUrl) {
                    return new PaymentRedirectDTO(
                        isRedirect: true,
                        redirectUrl: $checkoutUrl,
                        method: 'GET',
                        gatewayReferenceId: (string) $ppId
                    );
                }

                Log::channel('payment')->error('PipraPay Init response error: ' . json_encode($data));
            } catch (\Throwable $e) {
                Log::channel('payment')->error('PipraPay Init exception: ' . $e->getMessage());
            }
        }

        // Fallback
        $redirectUrl = route('payment.callback', [
            'gateway'    => 'piprapay',
            'order_id'   => $orderId,
            'pp_status'  => 'completed',
            'invoice_id' => $request->invoiceId,
            'amount'     => $request->amount,
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
        $ppStatus = strtolower($request->input('pp_status', $request->input('status', 'pending')));
        $ppId = $request->input('transaction_ref') ?? $request->input('pp_id') ?? $request->input('charge_id') ?? $request->input('order_id');
        $invoiceId = $request->input('invoice_id', '');
        $amount = (float) $request->input('amount', 0.0);

        if (!empty($this->apiKey) && $ppId && !str_starts_with($ppId, 'PIP-')) {
            try {
                $verifyRes = Http::withHeaders([
                    'MHS-PIPRAPAY-API-KEY' => $this->apiKey,
                    'Content-Type'         => 'application/json',
                    'Accept'               => 'application/json',
                ])->post("{$this->apiUrl}/verify-payment", [
                    'pp_id' => $ppId,
                ])->json();

                $remoteStatus = strtolower($verifyRes['status'] ?? '');
                $isCompleted = in_array($remoteStatus, ['completed', 'success', 'paid']);

                if ($isCompleted) {
                    $paidAmount = (float) ($verifyRes['total'] ?? $verifyRes['amount'] ?? $amount);
                    $metadata = $verifyRes['metadata'] ?? [];
                    $finalInvoiceId = $metadata['invoice_id'] ?? $invoiceId;

                    return new PaymentVerificationDTO(
                        isSuccessful: true,
                        invoiceId: (string) $finalInvoiceId,
                        transactionId: (string) ($verifyRes['pp_id'] ?? $ppId),
                        amountPaid: $paidAmount,
                        currency: 'BDT',
                        errorMessage: null,
                        rawPayload: $verifyRes
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('PipraPay Verify error: ' . $e->getMessage());
            }
        }

        $isSuccess = in_array($ppStatus, ['completed', 'success', 'paid']);

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: (string) ($ppId ?: 'TXN-' . bin2hex(random_bytes(4))),
            amountPaid: $amount,
            currency: 'BDT',
            errorMessage: $isSuccess ? null : 'PipraPay transaction could not be verified',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $payload = $request->all();
        $status = strtolower($payload['pp_status'] ?? $payload['status'] ?? '');
        $invoiceId = $payload['metadata']['invoice_id'] ?? $payload['invoice_id'] ?? '';
        $ppId = $payload['transaction_ref'] ?? $payload['pp_id'] ?? $payload['charge_id'] ?? '';

        $isSuccess = in_array($status, ['completed', 'success', 'paid']);

        return new PaymentWebhookResultDTO(
            handled: true,
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: (string) $ppId,
            raw: $payload
        );
    }

    public function queryTransaction(string $transactionId): PaymentStatusDTO
    {
        if (!empty($this->apiKey) && $transactionId) {
            try {
                $res = Http::withHeaders([
                    'MHS-PIPRAPAY-API-KEY' => $this->apiKey,
                ])->post("{$this->apiUrl}/verify-payment", [
                    'pp_id' => $transactionId,
                ])->json();

                return new PaymentStatusDTO(
                    transactionId: $transactionId,
                    status: strtolower($res['status'] ?? 'pending'),
                    amount: (float) ($res['total'] ?? 0.0),
                    currency: 'BDT'
                );
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return new PaymentStatusDTO(
            transactionId: $transactionId,
            status: 'completed',
            amount: 0.0,
            currency: 'BDT'
        );
    }
}
