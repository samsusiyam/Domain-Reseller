<?php

namespace App\DTOs;

use Carbon\Carbon;

class DomainDetailsDTO
{
    /**
     * @param array<string> $nameservers
     */
    public function __construct(
        public string $domain,
        public string $status,
        public ?Carbon $createdAt = null,
        public ?Carbon $expiresAt = null,
        public bool $isLocked = true,
        public bool $idProtection = false,
        public array $nameservers = [],
        public array $raw = []
    ) {}

    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'status' => $this->status,
            'created_at' => $this->createdAt?->toIso8601String(),
            'expires_at' => $this->expiresAt?->toIso8601String(),
            'is_locked' => $this->isLocked,
            'id_protection' => $this->idProtection,
            'nameservers' => $this->nameservers,
        ];
    }
}
