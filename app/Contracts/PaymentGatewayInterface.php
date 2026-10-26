<?php

namespace App\Contracts;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use App\DTOs\PaymentRefundDTO;
use App\DTOs\RefundResultDTO;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Return unique identifier for gateway.
     */
    public function getName(): string;

    /**
     * Return human-friendly display name.
     */
    public function getDisplayName(): string;

    /**
     * Check if gateway is enabled and credentials are fully configured.
     */
    public function isAvailable(): bool;

    /**
     * List supported currencies for this gateway.
     *
     * @return array<string>
     */
    public function getSupportedCurrencies(): array;

    /**
     * Initialize payment transaction and return redirect URL or form data.
     */
    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO;

    /**
     * Verify payment upon user return callback from gateway.
     */
    public function verifyCallback(Request $request): PaymentVerificationDTO;

    /**
     * Process asynchronous server-to-server webhook IPN callback.
     */
    public function processWebhook(Request $request): PaymentWebhookResultDTO;

    /**
     * Query gateway directly for transaction status.
     */
    public function queryTransaction(string $transactionId): PaymentStatusDTO;

    /**
     * Refund a paid transaction.
     */
    public function refund(PaymentRefundDTO $refundRequest): RefundResultDTO;
}
