<?php

namespace App\DTOs;

class DomainRegistrationDTO
{
    /**
     * @param array<string> $nameservers
     */
    public function __construct(
        public string $domain,
        public int $years = 1,
        public array $nameservers = [],
        public ?WhoisDataDTO $contactDetails = null,
        public bool $idProtection = false,
        public ?string $eppCode = null
    ) {}
}
