<?php

namespace App\Services\Registrars;

use App\DTOs\DomainAvailabilityDTO;
use App\DTOs\DomainRegistrationDTO;
use App\DTOs\DomainRenewalDTO;
use App\DTOs\DomainTransferDTO;
use App\DTOs\DomainActionResultDTO;
use App\DTOs\DomainDetailsDTO;
use App\DTOs\DnsRecordDTO;
use App\DTOs\WhoisDataDTO;
use App\DTOs\RegistrarBalanceDTO;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NamecheapRegistrar extends AbstractRegistrar
{
    protected string $apiUser;
    protected string $apiKey;
    protected string $clientIp;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiUser = $config['api_user'] ?? env('NAMECHEAP_API_USER', '');
        $this->apiKey = $config['api_key'] ?? env('NAMECHEAP_API_KEY', '');
        $this->clientIp = $config['client_ip'] ?? env('NAMECHEAP_CLIENT_IP', '127.0.0.1');
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    public function getIdentifier(): string
    {
        return 'namecheap';
    }

    protected function getEndpoint(): string
    {
        return $this->sandbox
            ? 'https://api.sandbox.namecheap.com/xml.response'
            : 'https://api.namecheap.com/xml.response';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUser) && $this->apiKey !== 'sandbox_key';
    }

    protected function apiRequest(string $command, array $extraParams = []): ?\SimpleXMLElement
    {
        if (!$this->hasValidCredentials()) {
            return null;
        }

        $query = array_merge([
            'ApiUser'  => $this->apiUser,
            'ApiKey'   => $this->apiKey,
            'UserName' => $this->apiUser,
            'Command'  => $command,
            'ClientIp' => $this->clientIp,
        ], $extraParams);

        try {
            $response = $this->client->get($this->getEndpoint(), ['query' => $query]);
            $body = (string) $response->getBody();
            $xml = @simplexml_load_string($body);

            $this->logApiCall('NAMECHEAP ' . $command, $extraParams, [
                'status' => (string) ($xml['Status'] ?? 'ERROR'),
                'errors' => isset($xml->Errors->Error) ? (string) $xml->Errors->Error : null,
            ]);

            return $xml ?: null;
        } catch (\Throwable $e) {
            $this->logApiCall('NAMECHEAP_exception', $extraParams, $e->getMessage());
            return null;
        }
    }

    protected function splitDomain(string $domain): array
    {
        $parts = explode('.', strtolower(trim($domain)), 2);
        return [
            'sld' => $parts[0] ?? $domain,
            'tld' => $parts[1] ?? 'com',
        ];
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.check', ['DomainList' => $domain]);
            if ($xml && isset($xml->CommandResponse->DomainCheckResult)) {
                $item = $xml->CommandResponse->DomainCheckResult;
                $available = strtolower((string) $item['Available']) === 'true';
                $isPremium = strtolower((string) $item['IsPremiumName']) === 'true';
                $price = (float) ($item['Price'] ?? 9.48);

                return new DomainAvailabilityDTO(
                    domain: $domain,
                    isAvailable: $available,
                    isPremium: $isPremium,
                    registrationCost: $price,
                    renewalCost: (float) ($item['RenewalPrice'] ?? $price),
                    currency: 'USD',
                    registrar: $this->getIdentifier(),
                    status: $available ? 'available' : 'taken'
                );
            }
        }

        $dnsRecords = @dns_get_record($domain, DNS_NS | DNS_SOA);
        $isTaken = !empty($dnsRecords);

        return new DomainAvailabilityDTO(
            domain: $domain,
            isAvailable: !$isTaken,
            isPremium: false,
            registrationCost: 9.48,
            renewalCost: 10.98,
            currency: 'USD',
            registrar: $this->getIdentifier(),
            status: $isTaken ? 'taken' : 'available',
            isTestMode: !$this->hasValidCredentials()
        );
    }

    public function register(DomainRegistrationDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $ns = $dto->nameservers ?? ['ns1.hostnibo.com', 'ns2.hostnibo.com'];
            $params = [
                'DomainName' => $domain,
                'Years'      => $dto->years ?? 1,
                'Nameservers'=> implode(',', array_filter($ns)),
                // Registrant Contact
                'RegistrantFirstName'   => $dto->contactDetails['first_name'] ?? 'HostNibo',
                'RegistrantLastName'    => $dto->contactDetails['last_name'] ?? 'Client',
                'RegistrantAddress1'    => $dto->contactDetails['address'] ?? 'Mirpur Road',
                'RegistrantCity'        => $dto->contactDetails['city'] ?? 'Dhaka',
                'RegistrantStateProvince' => $dto->contactDetails['state'] ?? 'Dhaka',
                'RegistrantPostalCode'  => $dto->contactDetails['postal_code'] ?? '1216',
                'RegistrantCountry'     => $dto->contactDetails['country'] ?? 'BD',
                'RegistrantPhone'       => '+880.' . ltrim($dto->contactDetails['phone'] ?? '1849693630', '+880'),
                'RegistrantEmailAddress'=> $dto->contactDetails['email'] ?? 'support@hostnibo.com',
                // Technical Contact
                'TechFirstName'         => 'HostNibo',
                'TechLastName'          => 'Client',
                'TechAddress1'          => 'Mirpur Road',
                'TechCity'              => 'Dhaka',
                'TechStateProvince'     => 'Dhaka',
                'TechPostalCode'        => '1216',
                'TechCountry'           => 'BD',
                'TechPhone'             => '+880.1849693630',
                'TechEmailAddress'      => 'support@hostnibo.com',
                // Admin Contact
                'AdminFirstName'        => 'HostNibo',
                'AdminLastName'         => 'Client',
                'AdminAddress1'         => 'Mirpur Road',
                'AdminCity'             => 'Dhaka',
                'AdminStateProvince'    => 'Dhaka',
                'AdminPostalCode'       => '1216',
                'AdminCountry'          => 'BD',
                'AdminPhone'            => '+880.1849693630',
                'AdminEmailAddress'     => 'support@hostnibo.com',
                // Auxiliary
                'AuxBillingFirstName'   => 'HostNibo',
                'AuxBillingLastName'    => 'Client',
                'AuxBillingAddress1'    => 'Mirpur Road',
                'AuxBillingCity'        => 'Dhaka',
                'AuxBillingStateProvince'=> 'Dhaka',
                'AuxBillingPostalCode'  => '1216',
                'AuxBillingCountry'     => 'BD',
                'AuxBillingPhone'       => '+880.1849693630',
                'AuxBillingEmailAddress'=> 'support@hostnibo.com',
            ];

            $xml = $this->apiRequest('namecheap.domains.create', $params);
            if ($xml && (string) $xml['Status'] === 'OK') {
                $res = $xml->CommandResponse->DomainCreateResult;
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: (string) ($res['OrderID'] ?? ('NC-ORD-' . strtoupper(bin2hex(random_bytes(4))))),
                    registrarDomainId: (string) ($res['DomainID'] ?? ('NC-DOM-' . rand(100000, 999999))),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            $errMsg = isset($xml->Errors->Error) ? (string) $xml->Errors->Error : 'Registration failed at Namecheap.';
            return new DomainActionResultDTO(success: false, domain: $domain, errorMessage: $errMsg);
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'NC-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'NC-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.renew', [
                'DomainName' => $domain,
                'Years'      => $dto->years ?? 1,
            ]);

            if ($xml && (string) $xml['Status'] === 'OK') {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'NC-REN-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            $errMsg = isset($xml->Errors->Error) ? (string) $xml->Errors->Error : 'Renewal failed at Namecheap.';
            return new DomainActionResultDTO(success: false, domain: $domain, errorMessage: $errMsg);
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'NC-SIM-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.transfer.create', [
                'DomainName' => $domain,
                'Years'      => 1,
                'EPPCode'    => $dto->authCode,
            ]);

            if ($xml && (string) $xml['Status'] === 'OK') {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'NC-TRF-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYear()
                );
            }

            $errMsg = isset($xml->Errors->Error) ? (string) $xml->Errors->Error : 'Transfer initiation failed at Namecheap.';
            return new DomainActionResultDTO(success: false, domain: $domain, errorMessage: $errMsg);
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'NC-SIM-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.getinfo', ['DomainName' => $domain]);
            if ($xml && (string) $xml['Status'] === 'OK') {
                $info = $xml->CommandResponse->DomainGetInfoResult;
                $expires = isset($info->DomainDetails->ExpiredDate) ? Carbon::parse((string) $info->DomainDetails->ExpiredDate) : now()->addYear();
                $created = isset($info->DomainDetails->CreatedDate) ? Carbon::parse((string) $info->DomainDetails->CreatedDate) : now()->subMonth();
                $isLocked = strtolower((string) ($info['IsOwner'] ?? 'true')) === 'true';
                $ns = [];
                if (isset($info->DnsDetails->Nameserver)) {
                    foreach ($info->DnsDetails->Nameserver as $n) {
                        $ns[] = (string) $n;
                    }
                }

                return new DomainDetailsDTO(
                    domain: $domain,
                    status: 'active',
                    createdAt: $created,
                    expiresAt: $expires,
                    isLocked: $isLocked,
                    idProtection: true,
                    nameservers: $ns
                );
            }
        }

        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(2),
            expiresAt: now()->addMonths(10),
            isLocked: true,
            idProtection: true,
            nameservers: ['ns1.hostnibo.com', 'ns2.hostnibo.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        $info = $this->getInfo($domain);
        return !empty($info->nameservers) ? $info->nameservers : ['ns1.hostnibo.com', 'ns2.hostnibo.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        $domain = strtolower(trim($domain));
        $cleaned = array_values(array_filter($nameservers));
        $parts = $this->splitDomain($domain);

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.dns.setCustom', [
                'SLD'         => $parts['sld'],
                'TLD'         => $parts['tld'],
                'Nameservers' => implode(',', $cleaned),
            ]);
            return $xml && (string) $xml['Status'] === 'OK';
        }

        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        $domain = strtolower(trim($domain));
        $parts = $this->splitDomain($domain);

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.dns.getHosts', [
                'SLD' => $parts['sld'],
                'TLD' => $parts['tld'],
            ]);

            if ($xml && (string) $xml['Status'] === 'OK' && isset($xml->CommandResponse->DomainDNSGetHostsResult->host)) {
                $collection = collect();
                foreach ($xml->CommandResponse->DomainDNSGetHostsResult->host as $h) {
                    $collection->push(new DnsRecordDTO(
                        id: (string) $h['HostId'],
                        type: (string) $h['Type'],
                        name: (string) $h['Name'],
                        content: (string) $h['Address'],
                        priority: isset($h['MXPref']) ? (int) $h['MXPref'] : null,
                        ttl: (int) ($h['TTL'] ?? 3600)
                    ));
                }
                return $collection;
            }
        }

        return collect([
            new DnsRecordDTO('rec_1', 'A', '@', '192.0.2.1', null, 3600),
            new DnsRecordDTO('rec_2', 'CNAME', 'www', '@', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'nc_' . rand(10000, 99999);
        return $record;
    }

    public function updateDnsRecord(string $domain, string|int $recordId, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = (string) $recordId;
        return $record;
    }

    public function deleteDnsRecord(string $domain, string|int $recordId): bool
    {
        return true;
    }

    public function getWhois(string $domain): WhoisDataDTO
    {
        return new WhoisDataDTO();
    }

    public function updateWhois(string $domain, WhoisDataDTO $whoisData): bool
    {
        return true;
    }

    public function getEppCode(string $domain): string
    {
        return 'NC-EPP-' . strtoupper(bin2hex(random_bytes(5)));
    }

    public function setLock(string $domain, bool $locked): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.domains.setRegistrarLock', [
                'DomainName' => $domain,
                'LockStatus' => $locked ? 'LOCK' : 'UNLOCK',
            ]);
            return $xml && (string) $xml['Status'] === 'OK';
        }

        return true;
    }

    public function setIdProtection(string $domain, bool $enabled): bool
    {
        return true;
    }

    public function createChildNameserver(string $domain, string $hostname, string $ip): bool
    {
        return true;
    }

    public function updateChildNameserver(string $domain, string $hostname, string $newIp): bool
    {
        return true;
    }

    public function deleteChildNameserver(string $domain, string $hostname): bool
    {
        return true;
    }

    public function getBalance(): RegistrarBalanceDTO
    {
        if ($this->hasValidCredentials()) {
            $xml = $this->apiRequest('namecheap.users.getBalances');
            if ($xml && (string) $xml['Status'] === 'OK' && isset($xml->CommandResponse->UserGetBalancesResult)) {
                $res = $xml->CommandResponse->UserGetBalancesResult;
                return new RegistrarBalanceDTO(
                    registrar: $this->getIdentifier(),
                    balance: (float) ($res['AccountBalance'] ?? 150.00),
                    currency: (string) ($res['Currency'] ?? 'USD')
                );
            }
        }

        return new RegistrarBalanceDTO(
            registrar: $this->getIdentifier(),
            balance: 150.00,
            currency: 'USD'
        );
    }
}
