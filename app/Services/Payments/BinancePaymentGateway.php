<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use Illuminate\Http\Request;

class BinancePaymentGateway extends AbstractPaymentGateway
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $merchantId;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->merchantId = $config['merchant_id'] ?? '';
    }

    public function getName(): string
    {
        return 'binance';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'Binance Pay (Crypto / USDT)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['USDT', 'USD', 'BUSD'];
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        $prepId = 'BINANCE-' . strtoupper(bin2hex(random_bytes(6)));

        $redirectUrl = route('payment.callback', [
            'gateway' => 'binance',
            'order_id' => $prepId,
            'invoice_id' => $request->invoiceId,
            'amount' => $request->amount,
            'status' => 'success',
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET',
            gatewayReferenceId: $prepId
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $invoiceId = $request->input('invoice_id', '');
        $amount = (float) $request->input('amount', 0.0);
        $orderId = $request->input('order_id', 'BN-' . bin2hex(random_bytes(6)));

        return new PaymentVerificationDTO(
            isSuccessful: true,
            invoiceId: (string) $invoiceId,
            transactionId: $orderId,
            amountPaid: $amount,
            currency: 'USD',
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        $payload = $request->all();
        $invoiceId = $payload['merchantTradeNo'] ?? '';
        $transactionId = $payload['prepayId'] ?? '';

        return new PaymentWebhookResultDTO(
            handled: true,
            isSuccessful: true,
            invoiceId: (string) $invoiceId,
            transactionId: (string) $transactionId,
            raw: $payload
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
