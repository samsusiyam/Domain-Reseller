<?php

namespace App\Services\Domain;

use App\DTOs\DomainAvailabilityDTO;
use App\Models\TldPricing;
use App\Services\Registrars\RegistrarManager;
use App\Services\Whois\RdapWhoisService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DomainAvailabilityService
{
    public function __construct(
        protected RegistrarManager $registrarManager,
        protected RdapWhoisService $rdapService
    ) {}

    /**
     * Parse domain and check availability via assigned registrar module or RDAP fallback.
     */
    public function check(string $domainName): DomainAvailabilityDTO
    {
        $domainName = strtolower(trim($domainName));
        $tld = $this->extractTld($domainName);

        $tldConfig = TldPricing::where('tld', $tld)->where('is_active', true)->with('registrar')->first();

        $registrarSlug = $tldConfig?->registrar?->slug ?? config('registrars.default', 'namecheap');
        
        try {
            $registrar = $this->registrarManager->driver($registrarSlug);
            $availability = $registrar->checkAvailability($domainName);
        } catch (\Throwable $e) {
            Log::warning("Registrar driver [{$registrarSlug}] threw exception: " . $e->getMessage());
            $availability = new DomainAvailabilityDTO(
                domain: $domainName,
                isAvailable: false,
                status: 'error',
                errorMessage: $e->getMessage(),
                registrar: $registrarSlug
            );
        }

        // Fallback to Authoritative RDAP if upstream registrar failed or is unconfigured
        if ($availability->isCheckFailed() && !config('registrars.test_mode', false)) {
            try {
                $rdap = $this->rdapService->lookup($domainName);
                if (!empty($rdap['success'])) {
                    if (!$rdap['is_registered']) {
                        $availability = new DomainAvailabilityDTO(
                            domain: $domainName,
                            isAvailable: true,
                            isPremium: false,
                            registrationCost: (float) ($tldConfig?->client_register ?? 10.49),
                            renewalCost: (float) ($tldConfig?->client_renew ?? 10.49),
                            currency: 'USD',
                            registrar: $registrarSlug,
                            status: 'available'
                        );
                    } else {
                        $availability = new DomainAvailabilityDTO(
                            domain: $domainName,
                            isAvailable: false,
                            isPremium: false,
                            registrationCost: 0,
                            renewalCost: 0,
                            currency: 'USD',
                            registrar: $registrarSlug,
                            status: 'taken'
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("RDAP fallback also failed for [{$domainName}]: " . $e->getMessage());
            }
        }

        // Reconcile with local catalog pricing (single source of truth)
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
