<?php

namespace App\Services\Domain;

use App\Models\Domain;
use App\Models\DnsRecord;
use App\Models\ChildNameserver;
use App\Models\EmailForward;
use App\Models\UrlForward;
use App\DTOs\DnsRecordDTO;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Support\Collection;

class DomainManagementService
{
    public function __construct(
        protected RegistrarManager $registrarManager
    ) {}

    /**
     * Update domain authoritative nameservers.
     */
    public function updateNameservers(Domain $domain, array $nameservers): bool
    {
        $registrar = $this->registrarManager->driver($domain->registrar->slug);
        $success = $registrar->setNameservers($domain->domain_name, $nameservers);

        if ($success) {
            $domain->update(['nameservers' => array_values(array_filter($nameservers))]);
        }

        return $success;
    }

    /**
     * Sync DNS records from database to registrar or vice versa.
     */
    public function getDnsRecords(Domain $domain): Collection
    {
        return $domain->dnsRecords;
    }

    public function addDnsRecord(Domain $domain, array $data): DnsRecord
    {
        $record = $domain->dnsRecords()->create($data);

        // Sync to upstream registrar
        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $dto = new DnsRecordDTO(
                type: $record->type,
                name: $record->host,
                content: $record->value,
                priority: $record->priority,
                ttl: $record->ttl
            );
            $result = $registrar->addDnsRecord($domain->domain_name, $dto);
            if ($result->id) {
                $record->update(['registrar_record_id' => $result->id]);
            }
        } catch (\Throwable $e) {
            // Log and allow local persist
        }

        return $record;
    }

    public function updateDnsRecord(DnsRecord $record, array $data): bool
    {
        $record->update($data);

        try {
            $domain = $record->domain;
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $dto = new DnsRecordDTO(
                id: $record->registrar_record_id,
                type: $record->type,
                name: $record->host,
                content: $record->value,
                priority: $record->priority,
                ttl: $record->ttl
            );
            $registrar->updateDnsRecord($domain->domain_name, $record->registrar_record_id ?? $record->id, $dto);
        } catch (\Throwable $e) {
        }

        return true;
    }

    public function deleteDnsRecord(DnsRecord $record): bool
    {
        try {
            $domain = $record->domain;
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $registrar->deleteDnsRecord($domain->domain_name, $record->registrar_record_id ?? $record->id);
        } catch (\Throwable $e) {
        }

        return (bool) $record->delete();
    }

    /**
     * Child nameservers (glue records).
     */
    public function createChildNameserver(Domain $domain, string $hostname, string $ip): ChildNameserver
    {
        $child = $domain->childNameservers()->create([
            'hostname' => $hostname,
            'ip_address' => $ip,
        ]);

        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $registrar->createChildNameserver($domain->domain_name, $hostname, $ip);
        } catch (\Throwable $e) {
        }

        return $child;
    }

    public function deleteChildNameserver(ChildNameserver $child): bool
    {
        try {
            $domain = $child->domain;
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $registrar->deleteChildNameserver($domain->domain_name, $child->hostname);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to delete child nameserver at registrar: " . $e->getMessage());
        }

        return (bool) $child->delete();
    }

    public function updateChildNameserver(ChildNameserver $child, string $newIp): bool
    {
        $child->update(['ip_address' => $newIp]);

        try {
            $domain = $child->domain;
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $registrar->updateChildNameserver($domain->domain_name, $child->hostname, $newIp);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to update child nameserver at registrar: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Domain security locks & privacy.
     */
    public function toggleLock(Domain $domain, bool $locked): bool
    {
        $registrar = $this->registrarManager->driver($domain->registrar->slug);
        $success = $registrar->setLock($domain->domain_name, $locked);

        if ($success) {
            $domain->update(['is_locked' => $locked]);
        }

        return $success;
    }

    public function toggleIdProtection(Domain $domain, bool $enabled): bool
    {
        $registrar = $this->registrarManager->driver($domain->registrar->slug);
        $success = $registrar->setIdProtection($domain->domain_name, $enabled);

        if ($success) {
            $domain->update(['is_private' => $enabled]);
        }

        return $success;
    }

    public function getAuthCode(Domain $domain): string
    {
        if (!empty($domain->auth_code) && !str_starts_with($domain->auth_code, 'DYN-EPP-') && !str_starts_with($domain->auth_code, 'DD-EPP-')) {
            return $domain->auth_code;
        }

        $registrar = $this->registrarManager->driver($domain->registrar->slug);
        $epp = $registrar->getEppCode($domain->domain_name);
        $domain->update(['auth_code' => $epp]);

        return $epp;
    }

    public function getWhois(Domain $domain): \App\DTOs\WhoisDataDTO
    {
        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            return $registrar->getWhois($domain->domain_name);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to fetch WHOIS for {$domain->domain_name}: " . $e->getMessage());
            return new \App\DTOs\WhoisDataDTO();
        }
    }

    public function updateWhois(Domain $domain, array $contactData): bool
    {
        try {
            $registrar = $this->registrarManager->driver($domain->registrar->slug);
            $dto = new \App\DTOs\WhoisDataDTO(
                registrant: $contactData,
                admin:      $contactData,
                tech:       $contactData,
                billing:    $contactData
            );
            return $registrar->updateWhois($domain->domain_name, $dto);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to update WHOIS for {$domain->domain_name}: " . $e->getMessage());
            return false;
        }
    }
}
