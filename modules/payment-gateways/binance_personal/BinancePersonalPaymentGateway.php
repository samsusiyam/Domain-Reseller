<?php

namespace Modules\PaymentGateways\Binancepersonal;

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

class BinancePersonalPaymentGateway extends AbstractPaymentGateway
{
    public function getConfigFields(): array
    {
        return json_decode('{"pay_id": {"label": "Binance Pay ID", "type": "text", "placeholder": "123456789", "required": true}, "email": {"label": "Binance Email (Optional)", "type": "text", "placeholder": "payments@hostnibo.com", "required": false}, "usdt_address": {"label": "USDT Deposit Address", "type": "text", "placeholder": "TRC20 Wallet Address", "required": false}, "network": {"label": "Crypto Network", "type": "text", "placeholder": "TRC20 / BEP20", "required": false}}', true);
    }


    protected string $payId;
    protected string $email;
    protected string $usdtAddress;
    protected string $network;
    protected string $apiKey;
    protected string $secretKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->payId = SystemSetting::get('binance_personal_pay_id', $config['pay_id'] ?? '123456789');
        $this->email = SystemSetting::get('binance_personal_email', $config['email'] ?? 'payments@hostnibo.com');
        $this->usdtAddress = SystemSetting::get('binance_personal_usdt_address', $config['usdt_address'] ?? '');
        $this->network = SystemSetting::get('binance_personal_network', $config['network'] ?? 'TRC20 / BEP20');
        $this->apiKey = SystemSetting::get('binance_personal_api_key', $config['api_key'] ?? '');
        $this->secretKey = SystemSetting::get('binance_personal_secret_key', $config['secret_key'] ?? '');
    }

    public function getName(): string
    {
        return 'binance_personal';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'Binance Pay (Personal / Direct Pay ID)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['USDT', 'USD'];
    }

    public function getPayId(): string
    {
        return $this->payId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsdtAddress(): string
    {
        return $this->usdtAddress;
    }

    public function getNetwork(): string
    {
        return $this->network;
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        $redirectUrl = route('payment.binance-personal.show', [
            'invoice' => $request->invoiceId
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET',
            gatewayReferenceId: 'BNP-' . strtoupper(bin2hex(random_bytes(4)))
        );
    }

    /**
     * Query Binance User API to auto-match transaction if API key is supplied.
     */
    public function tryAutoVerify(string $txId, float $expectedAmount): ?array
    {
        if (empty($this->apiKey) || empty($this->secretKey)) {
            return null;
        }

        try {
            $timestamp = round(microtime(true) * 1000);
            $query = "timestamp={$timestamp}";
            $signature = hash_hmac('sha256', $query, $this->secretKey);

            // 1. Check Binance Pay user transactions
            $payUrl = "https://api.binance.com/sapi/v1/pay/transactions?{$query}&signature={$signature}";
            $payRes = Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])->get($payUrl)->json();

            if (!empty($payRes['data'])) {
                foreach ($payRes['data'] as $txn) {
                    if (($txn['orderId'] ?? '') === $txId || ($txn['transactionId'] ?? '') === $txId) {
                        return [
                            'verified' => true,
                            'amount' => (float) ($txn['amount'] ?? $expectedAmount),
                            'orderId' => $txId,
                            'raw' => $txn,
                        ];
                    }
                }
            }

            // 2. Check Deposit history
            $depUrl = "https://api.binance.com/sapi/v1/capital/deposit/hisrec?{$query}&signature={$signature}";
            $depRes = Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])->get($depUrl)->json();

            if (is_array($depRes)) {
                foreach ($depRes as $dep) {
                    if (($dep['txId'] ?? '') === $txId && ($dep['status'] ?? 0) === 1) {
                        return [
                            'verified' => true,
                            'amount' => (float) ($dep['amount'] ?? $expectedAmount),
                            'orderId' => $txId,
                            'raw' => $dep,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::channel('payment')->error('Binance Personal API auto-verify error: ' . $e->getMessage());
        }

        return null;
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $invoiceId = $request->input('invoice_id', '');
        $txId = trim($request->input('transaction_id', ''));
        $amount = (float) $request->input('amount', 0.0);

        $autoMatched = $this->tryAutoVerify($txId, $amount);
        $isSuccess = !empty($txId);

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: $txId ?: ('BNP-' . bin2hex(random_bytes(4))),
            amountPaid: $autoMatched['amount'] ?? $amount,
            currency: 'USD',
            errorMessage: $isSuccess ? null : 'Binance Order ID / TxID is required',
            rawPayload: $autoMatched['raw'] ?? $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        return new PaymentWebhookResultDTO(
            handled: false,
            isSuccessful: false,
            invoiceId: '',
            transactionId: '',
            message: 'Binance Personal uses polling or manual transaction verification'
        );
    }

    public function queryTransaction(string $transactionId): PaymentStatusDTO
    {
        return new PaymentStatusDTO(
            transactionId: $transactionId,
            status: 'pending',
            amount: 0.0,
            currency: 'USD'
        );
    }
}
