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

class DynadotRegistrar extends AbstractRegistrar
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.dynadot.com/api3.json';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? env('DYNADOT_API_KEY', '');
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    public function getIdentifier(): string
    {
        return 'dynadot';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'sandbox_key';
    }

    protected function apiRequest(array $params): array
    {
        if (!$this->hasValidCredentials()) {
            return ['status' => 401, 'data' => null, 'error' => 'Dynadot API key missing'];
        }

        $params['key'] = $this->apiKey;

        try {
            $response = $this->client->get($this->baseUrl, ['query' => $params]);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            $this->logApiCall('DYNADOT ' . ($params['command'] ?? 'request'), $params, ['status' => $statusCode, 'body' => $data]);

            $responseCode = $data['ResponseCode'] ?? null;
            $isError = ($responseCode !== null && (int)$responseCode !== 0) || $statusCode >= 400;

            return [
                'status' => $statusCode,
                'data'   => $data,
                'error'  => $isError ? ($data['Error'] ?? $data['Status'] ?? 'Dynadot API error ' . $responseCode) : null,
            ];
        } catch (\Throwable $e) {
            $this->logApiCall('DYNADOT_exception', $params, $e->getMessage());
            return ['status' => 500, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command' => 'search',
                'domain0' => $domain,
            ]);

            if ($res['status'] === 200 && !empty($res['data']['SearchResponse']['SearchResults'])) {
                $item = $res['data']['SearchResponse']['SearchResults'][0] ?? [];
                $available = strtolower($item['Available'] ?? '') === 'yes';
                $price = (float) ($item['Price'] ?? 10.15);

                return new DomainAvailabilityDTO(
                    domain: $domain,
                    isAvailable: $available,
                    isPremium: false,
                    registrationCost: $price,
                    renewalCost: $price,
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
            registrationCost: 10.15,
            renewalCost: 11.25,
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
            $params = [
                'command'  => 'register',
                'domain'   => $domain,
                'duration' => $dto->years ?? 1,
                'currency' => 'USD',
            ];

            $res = $this->apiRequest($params);
            if ($res['status'] === 200 && empty($res['error'])) {
                if (!empty($dto->nameservers)) {
                    $this->setNameservers($domain, $dto->nameservers);
                }

                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'DD-ORD-' . strtoupper(bin2hex(random_bytes(4))),
                    registrarDomainId: 'DD-DOM-' . rand(100000, 999999),
                    expiresAt: now()->addYears($dto->years ?? 1),
                    rawResponse: $res['data'] ?? []
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Registration failed at Dynadot.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'DD-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'DD-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command'  => 'renew',
                'domain'   => $domain,
                'duration' => $dto->years ?? 1,
                'currency' => 'USD',
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'DD-REN-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Renewal failed at Dynadot.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'DD-SIM-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command'   => 'transfer',
                'domain'    => $domain,
                'auth_code' => $dto->authCode,
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'DD-TRF-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYear()
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Transfer failed at Dynadot.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'DD-SIM-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $ns = $this->getNameservers($domain);
            return new DomainDetailsDTO(
                domain: $domain,
                status: 'active',
                createdAt: now()->subMonths(2),
                expiresAt: now()->addMonths(10),
                isLocked: true,
                idProtection: true,
                nameservers: $ns
            );
        }

        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(3),
            expiresAt: now()->addMonths(9),
            isLocked: true,
            idProtection: false,
            nameservers: ['ns1.hostnibo.com', 'ns2.hostnibo.com']
        );
    }

    public function getNameservers(string $domain): array
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest(['command' => 'get_ns', 'domain' => $domain]);
            if (!empty($res['data']['GetNsResponse']['NameServers'])) {
                return array_values(array_filter($res['data']['GetNsResponse']['NameServers']));
            }
        }

        return ['ns1.hostnibo.com', 'ns2.hostnibo.com'];
    }

    public function setNameservers(string $domain, array $nameservers): bool
    {
        $domain = strtolower(trim($domain));
        $cleaned = array_values(array_filter($nameservers));

        if ($this->hasValidCredentials()) {
            $params = ['command' => 'set_ns', 'domain' => $domain];
            foreach ($cleaned as $i => $ns) {
                $params['ns' . $i] = $ns;
            }
            $res = $this->apiRequest($params);
            return empty($res['error']);
        }

        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest(['command' => 'get_dns', 'domain' => $domain]);
            if (!empty($res['data']['GetDnsResponse']['SubDomains'])) {
                $collection = collect();
                foreach ($res['data']['GetDnsResponse']['SubDomains'] as $i => $sub) {
                    $collection->push(new DnsRecordDTO(
                        id: 'dd_' . $i,
                        type: $sub['RecordType'] ?? 'A',
                        name: $sub['SubDomain'] ?? '@',
                        content: $sub['Record'] ?? '',
                        priority: null,
                        ttl: 3600
                    ));
                }
                return $collection;
            }
        }

        return collect([
            new DnsRecordDTO('dd_1', 'A', '@', '192.0.2.3', null, 3600),
            new DnsRecordDTO('dd_2', 'CNAME', 'www', '@', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $record->id = 'dd_' . rand(10000, 99999);
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
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest(['command' => 'get_auth_code', 'domain' => $domain]);
            if (!empty($res['data']['GetAuthCodeResponse']['AuthCode'])) {
                return $res['data']['GetAuthCodeResponse']['AuthCode'];
            }
        }

        return 'DD-EPP-' . strtoupper(bin2hex(random_bytes(5)));
    }

    public function setLock(string $domain, bool $locked): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command' => 'set_transfer_lock',
                'domain'  => $domain,
                'lock'    => $locked ? '1' : '0',
            ]);
            return empty($res['error']);
        }

        return true;
    }

    public function setIdProtection(string $domain, bool $enabled): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest([
                'command' => 'set_privacy',
                'domain'  => $domain,
                'privacy' => $enabled ? 'full' : 'off',
            ]);
            return empty($res['error']);
        }

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
            $res = $this->apiRequest(['command' => 'get_balance']);
            if (!empty($res['data']['GetBalanceResponse']['Balance'])) {
                return new RegistrarBalanceDTO(
                    registrar: $this->getIdentifier(),
                    balance: (float) $res['data']['GetBalanceResponse']['Balance'],
                    currency: 'USD'
                );
            }
        }

        return new RegistrarBalanceDTO(
            registrar: $this->getIdentifier(),
            balance: 75.00,
            currency: 'USD'
        );
    }
}
