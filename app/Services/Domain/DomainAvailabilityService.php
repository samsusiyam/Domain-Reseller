<?php

namespace App\Services\Domain;

use App\DTOs\DomainAvailabilityDTO;
use App\Models\TldPricing;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Support\Collection;

class DomainAvailabilityService
{
    public function __construct(
        protected RegistrarManager $registrarManager
    ) {}

    /**
     * Parse domain and check availability via assigned registrar module.
     */
    public function check(string $domainName): DomainAvailabilityDTO
    {
        $domainName = strtolower(trim($domainName));
        $tld = $this->extractTld($domainName);

        $tldConfig = TldPricing::where('tld', $tld)->where('is_active', true)->with('registrar')->first();

        $registrarSlug = $tldConfig?->registrar?->slug ?? config('registrars.default', 'namecheap');
        $registrar = $this->registrarManager->driver($registrarSlug);

        $availability = $registrar->checkAvailability($domainName);

        // Reconcile with local catalog pricing
        if ($tldConfig) {
            $availability->registrationCost = (float) $tldConfig->client_register;
            $availability->renewalCost = (float) $tldConfig->client_renew;
        }

        return $availability;
    }

    /**
     * Check multiple domains or single domain across multiple popular TLDs.
     *
     * @param array<string> $domains
     * @return Collection<string, DomainAvailabilityDTO>
     */
    public function checkBulk(array $domains): Collection
    {
        $results = collect();
        foreach ($domains as $domain) {
            $clean = strtolower(trim($domain));
            if (!empty($clean)) {
                $results->put($clean, $this->check($clean));
            }
        }
        return $results;
    }

    /**
     * Extract TLD from domain (e.g. "example.com" -> ".com", "test.com.bd" -> ".com.bd").
     */
    public function extractTld(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $knownTlds = TldPricing::pluck('tld')->toArray();

        // Sort descending by length so multi-part TLDs (.com.bd) match before .bd
        usort($knownTlds, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($knownTlds as $tld) {
            if (str_ends_with($domain, $tld)) {
                return $tld;
            }
        }

        $parts = explode('.', $domain);
        return count($parts) > 1 ? '.' . end($parts) : '.com';
    }
}
