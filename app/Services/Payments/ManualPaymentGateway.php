<?php

namespace App\Services\Payments;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use Illuminate\Http\Request;

class ManualPaymentGateway extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'manual';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'Manual Payment (Bank / Cash / bKash Personal)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['USD', 'BDT'];
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        // For manual payments, redirect to the client manual submission page
        $submissionUrl = route('client.invoices.pay', [
            'invoice' => $request->invoiceId,
            'gateway' => 'manual',
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $submissionUrl,
            method: 'GET',
            gatewayReferenceId: 'MANUAL-' . strtoupper(bin2hex(random_bytes(4)))
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $invoiceId = $request->input('invoice_id', '');
        $trxId = $request->input('transaction_id', 'MANUAL-' . strtoupper(bin2hex(random_bytes(4))));
        $amount = (float) $request->input('amount', 0.0);
        $currency = $request->input('currency', 'USD');

        return new PaymentVerificationDTO(
            isSuccessful: true,
            invoiceId: (string) $invoiceId,
            transactionId: $trxId,
            amountPaid: $amount,
            currency: $currency,
            rawPayload: $request->all()
        );
    }

    public function processWebhook(Request $request): PaymentWebhookResultDTO
    {
        return new PaymentWebhookResultDTO(
            handled: false,
            isSuccessful: false,
            invoiceId: '',
            transactionId: '',
            message: 'Manual payments do not have automated webhooks'
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
