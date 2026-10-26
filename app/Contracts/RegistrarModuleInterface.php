<?php

namespace App\Contracts;

use App\DTOs\DomainAvailabilityDTO;
use App\DTOs\DomainRegistrationDTO;
use App\DTOs\DomainRenewalDTO;
use App\DTOs\DomainTransferDTO;
use App\DTOs\DomainActionResultDTO;
use App\DTOs\DomainDetailsDTO;
use App\DTOs\DnsRecordDTO;
use App\DTOs\WhoisDataDTO;
use App\DTOs\RegistrarBalanceDTO;
use Illuminate\Support\Collection;

interface RegistrarModuleInterface
{
    /**
     * Get unique slug identifier for the registrar driver.
     */
    public function getIdentifier(): string;

    /**
     * Check single domain availability.
     */
    public function checkAvailability(string $domain): DomainAvailabilityDTO;

    /**
     * Check availability for multiple domains in bulk.
     *
     * @param array<string> $domains
     * @return Collection<string, DomainAvailabilityDTO>
     */
    public function checkBulkAvailability(array $domains): Collection;

    /**
     * Register a new domain name.
     */
    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO;

    /**
     * Renew an active domain.
     */
    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO;

    /**
     * Initiate domain transfer into registrar.
     */
    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO;

    /**
     * Retrieve authoritative live domain metadata from registrar.
     */
    public function getInfo(string $domain): DomainDetailsDTO;

    /**
     * Get active nameservers for a domain.
     *
     * @return array<string>
     */
    public function getNameservers(string $domain): array;

    /**
     * Set authoritative nameservers for a domain.
     *
     * @param array<string> $nameservers
     */
    public function setNameservers(string $domain, array $nameservers): bool;

    /**
     * Fetch DNS zone records hosted at the registrar.
     *
     * @return Collection<int, DnsRecordDTO>
     */
    public function getDnsRecords(string $domain): Collection;

    /**
     * Add a DNS record to registrar hosted zone.
     */
    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO;

    /**
     * Update an existing DNS record.
     */
    public function updateDnsRecord(string $domain, string|int $recordId, DnsRecordDTO $record): DnsRecordDTO;

    /**
     * Delete a DNS record by upstream ID.
     */
    public function deleteDnsRecord(string $domain, string|int $recordId): bool;

    /**
     * Retrieve WHOIS contact details for a domain.
     */
    public function getWhois(string $domain): WhoisDataDTO;

    /**
     * Update WHOIS contact information at registry.
     */
    public function updateWhois(string $domain, WhoisDataDTO $whoisData): bool;

    /**
     * Fetch or request domain transfer authorization (EPP) code.
     */
    public function getEppCode(string $domain): string;

    /**
     * Enable or disable registry transfer lock (clientTransferProhibited).
     */
    public function setLock(string $domain, bool $locked): bool;

    /**
     * Enable or disable WHOIS privacy/ID protection.
     */
    public function setIdProtection(string $domain, bool $enabled): bool;

    /**
     * Register a child nameserver (glue record).
     */
    public function createChildNameserver(string $domain, string $hostname, string $ip): bool;

    /**
     * Update IP address of an existing child nameserver.
     */
    public function updateChildNameserver(string $domain, string $hostname, string $newIp): bool;

    /**
     * Delete a child nameserver from registry.
     */
    public function deleteChildNameserver(string $domain, string $hostname): bool;

    /**
     * Query live registrar account balance.
     */
    public function getBalance(): RegistrarBalanceDTO;
}
