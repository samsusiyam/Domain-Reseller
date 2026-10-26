<?php

namespace App\DTOs;

class PaymentRequestDTO
{
    public function __construct(
        public string $invoiceId,
        public float $amount,
        public string $currency,
        public string $returnUrl,
        public string $cancelUrl,
        public string $customerEmail,
        public string $customerName,
        public ?string $customerPhone = null,
        public array $metadata = []
    ) {}
}
