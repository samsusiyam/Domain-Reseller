<?php

namespace App\DTOs;

use Carbon\Carbon;

class DomainActionResultDTO
{
    public function __construct(
        public bool $success,
        public string $domain,
        public ?string $registrarOrderId = null,
        public ?string $registrarDomainId = null,
        public ?string $errorMessage = null,
        public ?Carbon $expiresAt = null,
        public array $rawResponse = []
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'domain' => $this->domain,
            'registrar_order_id' => $this->registrarOrderId,
            'registrar_domain_id' => $this->registrarDomainId,
            'error_message' => $this->errorMessage,
            'expires_at' => $this->expiresAt?->toIso8601String(),
        ];
    }
}
