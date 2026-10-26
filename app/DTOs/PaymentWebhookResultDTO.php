<?php

namespace App\DTOs;

class PaymentWebhookResultDTO
{
    public function __construct(
        public bool $handled,
        public bool $isSuccessful,
        public string $invoiceId,
        public string $transactionId,
        public ?string $message = null,
        public array $raw = []
    ) {}
}
