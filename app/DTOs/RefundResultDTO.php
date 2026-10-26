<?php

namespace App\DTOs;

class RefundResultDTO
{
    public function __construct(
        public bool $isSuccessful,
        public string $refundTransactionId,
        public ?string $errorMessage = null,
        public array $raw = []
    ) {}
}
