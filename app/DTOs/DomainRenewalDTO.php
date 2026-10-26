<?php

namespace App\DTOs;

class DomainRenewalDTO
{
    public function __construct(
        public string $domain,
        public int $years = 1,
        public ?string $currentExpiryDate = null
    ) {}
}
