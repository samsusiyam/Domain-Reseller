<?php

namespace Modules\PaymentGateways\Bkashpersonal;

use App\Services\Payments\AbstractPaymentGateway;
use App\Contracts\PaymentGatewayInterface;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentRedirectDTO;
use App\DTOs\PaymentVerificationDTO;
use App\DTOs\PaymentWebhookResultDTO;
use App\DTOs\PaymentStatusDTO;
use App\Models\Invoice;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class BkashPersonalPaymentGateway extends AbstractPaymentGateway
{
    public function getConfigFields(): array
    {
        return json_decode('{"personal_number": {"label": "bKash Personal Mobile Number", "type": "text", "placeholder": "01849693630", "required": true}}', true);
    }


    public function getName(): string
    {
        return 'bkash_personal';
    }

    public function getDisplayName(): string
    {
        return $this->config['name'] ?? 'bKash Personal (Send Money)';
    }

    public function getSupportedCurrencies(): array
    {
        return ['BDT', 'USD'];
    }

    public function getPersonalNumber(): string
    {
        return SystemSetting::get('bkash_personal_number', $this->config['personal_number'] ?? '01849693630');
    }

    public function initializePayment(PaymentRequestDTO $request): PaymentRedirectDTO
    {
        // Redirect client to dedicated bKash Personal payment confirmation screen
        $redirectUrl = route('payment.bkash-personal.show', [
            'invoice' => $request->invoiceId
        ]);

        return new PaymentRedirectDTO(
            isRedirect: true,
            redirectUrl: $redirectUrl,
            method: 'GET',
            gatewayReferenceId: 'BKP-' . strtoupper(bin2hex(random_bytes(4)))
        );
    }

    public function verifyCallback(Request $request): PaymentVerificationDTO
    {
        $invoiceId = $request->input('invoice_id', '');
        $trxId = strtoupper(trim($request->input('transaction_id', '')));
        $senderPhone = trim($request->input('sender_phone', ''));
        $amount = (float) $request->input('amount', 0.0);

        $isSuccess = !empty($trxId);

        return new PaymentVerificationDTO(
            isSuccessful: $isSuccess,
            invoiceId: (string) $invoiceId,
            transactionId: $trxId ?: ('BKP-' . bin2hex(random_bytes(4))),
            amountPaid: $amount,
            currency: 'BDT',
            errorMessage: $isSuccess ? null : 'Transaction ID is required for bKash Personal submission',
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
            message: 'bKash Personal utilizes manual/SMS transaction matching'
        );
    }

    public function queryTransaction(string $transactionId): PaymentStatusDTO
    {
        return new PaymentStatusDTO(
            transactionId: $transactionId,
            status: 'pending',
            amount: 0.0,
            currency: 'BDT'
        );
    }
}
