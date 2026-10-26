<?php

namespace App\DTOs;

class PaymentVerificationDTO
{
    public function __construct(
        public bool $isSuccessful,
        public string $invoiceId,
        public string $transactionId,
        public float $amountPaid,
        public string $currency,
        public ?string $errorMessage = null,
        public array $rawPayload = []
    ) {}
}
