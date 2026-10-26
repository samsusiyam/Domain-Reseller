<?php

namespace App\DTOs;

class PaymentRedirectDTO
{
    public function __construct(
        public bool $isRedirect = true,
        public ?string $redirectUrl = null,
        public string $method = 'GET',
        public array $formParameters = [],
        public ?string $gatewayReferenceId = null,
        public ?string $errorMessage = null
    ) {}
}
