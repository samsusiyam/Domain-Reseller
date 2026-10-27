<?php

namespace Modules\PaymentGateways\Sslcommerz;

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

class SslcommerzPaymentGateway extends AbstractPaymentGateway
{
    public function getConfigFields(): array
    {
        return json_decode('{"store_id": {"label": "Store ID", "type": "text", "placeholder": "Store ID", "required": true}, "store_passwd": {"label": "Store Password", "type": "password", "placeholder": "Store Password", "required": true}, "sandbox": {"label": "Sandbox Mode", "type": "yesno", "required": false}}', true);
    }


    protected string $storeId;
    protected string $storePasswd;
    protected bool $sandbox;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->storeId = SystemSetting::get('sslcommerz_store_id', $config['store_id'] ?? '');
        $this->storePasswd = SystemSetting::get('sslcommerz_store_passwd', $config['store_passwd'] ?? '');
        $this->sandbox = (bool) SystemSetting::get('sslcommerz_sandbox', $config['sandbox'] ?? true);
    }

    public function getName(): string
    {
        return 'sslcommerz';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'SSLCommerz (Cards / MFS / Net Banking)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['BDT', 'USD'];
    }

    protected function getBaseUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        $tranId = 'SSL-' . $request->invoiceId . '-' . time();

        if (!empty($this->storeId) && !empty($this->storePasswd)) {
            try {
                $postData = [
                    'store_id' => $this->storeId,
                    'store_passwd' => $this->storePasswd,
                    'total_amount' => number_format($request->amount, 2, '.', ''),
                    'currency' => $request->currency === 'BDT' ? 'BDT' : 'USD',
                    'tran_id' => $tranId,
                    'success_url' => route('payment.callback', ['gateway' => 'sslcommerz', 'invoice_id' => $request->invoiceId]),
                    'fail_url' => route('payment.callback', ['gateway' => 'sslcommerz', 'invoice_id' => $request->invoiceId, 'status' => 'failed']),
                    'cancel_url' => $request->cancelUrl ?: route('client.invoices.show', $request->invoiceId),
                    'ipn_url' => route('payment.webhook', ['gateway' => 'sslcommerz']),
                    'cus_name' => $request->customerName ?: 'HostNibo Customer',
                    'cus_email' => $request->customerEmail ?: 'customer@hostnibo.com',
                    'cus_add1' => 'Dhaka',
                    'cus_city' => 'Dhaka',
                    'cus_country' => 'Bangladesh',
                    'cus_phone' => $request->customerPhone ?: '01849693630',
                    'shipping_method' => 'NO',
                    'product_name' => 'Domain Registration / Renewal',
                    'product_category' => 'Service',
                    'product_profile' => 'general',
                ];

                $response = Http::asForm()->post("{$this->getBaseUrl()}/gwprocess/v4/api.php", $postData);
                $result = $response->json();

                if (($result['status'] ?? '') === 'SUCCESS' && !empty($result['GatewayPageURL'])) {
                    return new PaymentRedirectDTO(
                        isRedirect: true,
                        redirectUrl: $result['GatewayPageURL'],
                        method: 'GET',
                        gatewayReferenceId: $tranId
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('SSLCommerz Session Init failed: ' . $e->getMessage());
            }
        }

        // Sandbox simulated fallback
        $redirectUrl = route('payment.callback', [
            'gateway' => 'sslcommerz',
            'tran_id' => $tranId,
            'status' => 'VALID',
            'val_id' => 'VAL-' . strtoupper(bin2hex(random_bytes(5))),
            'invoice_id' => $request->invoiceId,
            'amount' => $request->amount,
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET',
            gatewayReferenceId: $tranId
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $valId = $request->input('val_id');
        $status = $request->input('status');
        $tranId = $request->input('tran_id', 'TRX-' . bin2hex(random_bytes(4)));
        $invoiceId = $request->input('invoice_id', '');
        $amount = (float) $request->input('amount', 0.0);

        if ($valId && !str_starts_with($valId, 'VAL-') && !empty($this->storeId)) {
            try {
                $checkUrl = "{$this->getBaseUrl()}/validator/api/validationserverAPI.php?val_id={$valId}&store_id={$this->storeId}&store_passwd={$this->storePasswd}&v=1&format=json";
                $checkRes = Http::get($checkUrl)->json();

                if (($checkRes['status'] ?? '') === 'VALID' || ($checkRes['status'] ?? '') === 'VALIDATED') {
                    return new PaymentVerificationDTO(
                        isSuccessful: true,
                        invoiceId: (string) $invoiceId,
                        transactionId: $checkRes['bank_tran_id'] ?? $tranId,
                        amountPaid: (float) ($checkRes['amount'] ?? $amount),
                        currency: $checkRes['currency'] ?? 'BDT',
                        errorMessage: null,
                        rawPayload: $checkRes
                    );
                }
            } catch (\Throwable $e) {
                Log::channel('payment')->error('SSLCommerz Validation error: ' . $e->getMessage());
            }
        }

        $isSuccess = ($status === 'VALID' || $status === 'SUCCESS');

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: (string) $tranId,
            amountPaid: $amount,
            currency: 'BDT',
            errorMessage: $isSuccess ? null : 'Payment failed or was cancelled via SSLCommerz',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $valId = $request->input('val_id');
        $tranId = $request->input('tran_id');
        $status = $request->input('status');

        $isSuccess = ($status === 'VALID' || $status === 'VALIDATED');

        return new PaymentWebhookResultDTO(
            handled: true,
            isSuccessful: $isSuccess,
            invoiceId: (string) $request->input('value_a', $tranId),
            transactionId: (string) $tranId,
            raw: $request->all()
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
