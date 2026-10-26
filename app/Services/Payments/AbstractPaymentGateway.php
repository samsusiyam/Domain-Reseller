<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use App\DTOs\PaymentRefundDTO;
use App\DTOs\RefundResultDTO;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected Client $client;
    protected array $config = [];
    protected bool $enabled = true;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->enabled = (bool) ($config['enabled'] ?? true);
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    public function isAvailable(): bool
    {
        return $this->enabled;
    }

    protected function logPayment(string $action, array $params, mixed $response): void
    {
        Log::channel('payment')->info("[Payment: {$this->getName()}] {$action}", [
            'params' => $params,
            'response' => $response,
        ]);
    }

    public function refund(PaymentRefundDTO $refundRequest): RefundResultDTO
    {
        return new RefundResultDTO(
            isSuccessful: true,
            refundTransactionId: 'REF-' . strtoupper(bin2hex(random_bytes(6)))
        );
    }
}
