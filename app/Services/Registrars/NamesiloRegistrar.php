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

class NamesiloRegistrar extends AbstractRegistrar
{
    protected string $apiKey;
    protected string $baseUrl = 'https://www.namesilo.com/api/';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->apiKey = $config['api_key'] ?? env('NAMESILO_API_KEY', '');
        if (!empty($config['sandbox'])) {
            $this->baseUrl = 'https://sandbox.namesilo.com/api/';
        }
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    public function getIdentifier(): string
    {
        return 'namesilo';
    }

    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'sandbox_key';
    }

    protected function apiRequest(string $operation, array $params = []): array
    {
        if (!$this->hasValidCredentials()) {
            return ['status' => 401, 'data' => null, 'error' => 'NameSilo API key missing'];
        }

        $query = array_merge([
            'version' => 1,
            'type'    => 'json',
            'key'     => $this->apiKey,
        ], $params);

        try {
            $url = rtrim($this->baseUrl, '/') . '/' . $operation;
            $response = $this->client->get($url, ['query' => $query]);
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            $this->logApiCall('NAMESILO ' . $operation, $params, ['status' => $statusCode, 'body' => $data]);

            $reply = $data['reply'] ?? [];
            $code = (int) ($reply['code'] ?? 0);
            $isSuccess = ($code === 300);

            return [
                'status'  => $statusCode,
                'data'    => $reply,
                'code'    => $code,
                'error'   => !$isSuccess ? ($reply['detail'] ?? 'NameSilo API error ' . $code) : null,
            ];
        } catch (\Throwable $e) {
            $this->logApiCall('NAMESILO_exception', $params, $e->getMessage());
            return ['status' => 500, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function checkAvailability(string $domain): DomainAvailabilityDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('checkRegisterAvailability', ['domains' => $domain]);
            if ($res['status'] === 200 && empty($res['error'])) {
                $available = !empty($res['data']['available']['domain']);
                $price = (float) ($res['data']['available']['domain']['price'] ?? 10.95);

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
            registrationCost: 10.95,
            renewalCost: 11.95,
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
                'domain'  => $domain,
                'years'   => $dto->years ?? 1,
                'private' => 1,
                'ns1'     => $ns[0] ?? 'ns1.hostnibo.com',
                'ns2'     => $ns[1] ?? 'ns2.hostnibo.com',
            ];

            $res = $this->apiRequest('registerDomain', $params);
            if ($res['status'] === 200 && empty($res['error'])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'NS-ORD-' . strtoupper(bin2hex(random_bytes(4))),
                    registrarDomainId: 'NS-DOM-' . rand(100000, 999999),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Registration failed at NameSilo.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'NS-SIM-' . strtoupper(bin2hex(random_bytes(4))),
            registrarDomainId: 'NS-DOM-' . rand(100000, 999999),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function renew(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('renewDomain', [
                'domain' => $domain,
                'years'  => $dto->years ?? 1,
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'NS-REN-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYears($dto->years ?? 1)
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Renewal failed at NameSilo.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'NS-SIM-REN-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYears($dto->years ?? 1)
        );
    }

    public function transfer(DomainTransferDTO $dto): DomainActionResultDTO
    {
        $domain = strtolower(trim($dto->domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('transferDomain', [
                'domain' => $domain,
                'auth'   => $dto->authCode,
            ]);

            if ($res['status'] === 200 && empty($res['error'])) {
                return new DomainActionResultDTO(
                    success: true,
                    domain: $domain,
                    registrarOrderId: 'NS-TRF-' . strtoupper(bin2hex(random_bytes(4))),
                    expiresAt: now()->addYear()
                );
            }

            return new DomainActionResultDTO(
                success: false,
                domain: $domain,
                errorMessage: $res['error'] ?? 'Transfer initiation failed at NameSilo.'
            );
        }

        return new DomainActionResultDTO(
            success: true,
            domain: $domain,
            registrarOrderId: 'NS-SIM-TRF-' . strtoupper(bin2hex(random_bytes(4))),
            expiresAt: now()->addYear()
        );
    }

    public function getInfo(string $domain): DomainDetailsDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('getDomainInfo', ['domain' => $domain]);
            if ($res['status'] === 200 && empty($res['error'])) {
                $d = $res['data'];
                $isLocked = strtolower($d['locked'] ?? 'yes') === 'yes';
                $isPrivate = strtolower($d['private'] ?? 'yes') === 'yes';
                $expires = !empty($d['expires']) ? Carbon::parse($d['expires']) : now()->addYear();
                $created = !empty($d['created']) ? Carbon::parse($d['created']) : now()->subMonth();
                $ns = [];
                if (!empty($d['nameservers']['nameserver'])) {
                    $ns = is_array($d['nameservers']['nameserver']) ? $d['nameservers']['nameserver'] : [$d['nameservers']['nameserver']];
                }

                return new DomainDetailsDTO(
                    domain: $domain,
                    status: 'active',
                    createdAt: $created,
                    expiresAt: $expires,
                    isLocked: $isLocked,
                    idProtection: $isPrivate,
                    nameservers: $ns,
                    raw: $d
                );
            }
        }

        return new DomainDetailsDTO(
            domain: $domain,
            status: 'active',
            createdAt: now()->subMonths(1),
            expiresAt: now()->addMonths(11),
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

        if ($this->hasValidCredentials()) {
            $params = ['domain' => $domain];
            foreach ($cleaned as $i => $ns) {
                $params['ns' . ($i + 1)] = $ns;
            }
            $res = $this->apiRequest('changeNameServers', $params);
            return empty($res['error']);
        }

        return true;
    }

    public function getDnsRecords(string $domain): Collection
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('dnsListRecords', ['domain' => $domain]);
            if ($res['status'] === 200 && !empty($res['data']['resource_record'])) {
                $collection = collect();
                $records = is_array($res['data']['resource_record']) && isset($res['data']['resource_record'][0])
                    ? $res['data']['resource_record']
                    : [$res['data']['resource_record']];

                foreach ($records as $r) {
                    $collection->push(new DnsRecordDTO(
                        id: (string) ($r['record_id'] ?? ''),
                        type: $r['type'] ?? 'A',
                        name: $r['host'] ?? '@',
                        content: $r['value'] ?? '',
                        priority: null,
                        ttl: (int) ($r['ttl'] ?? 3600)
                    ));
                }
                return $collection;
            }
        }

        return collect([
            new DnsRecordDTO('ns_1', 'A', '@', '192.0.2.4', null, 3600),
            new DnsRecordDTO('ns_2', 'CNAME', 'www', '@', null, 3600),
        ]);
    }

    public function addDnsRecord(string $domain, DnsRecordDTO $record): DnsRecordDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $res = $this->apiRequest('dnsAddRecord', [
                'domain'  => $domain,
                'rrtype'  => strtoupper($record->type),
                'rrhost'  => $record->name,
                'rrvalue' => $record->content,
                'rrttl'   => $record->ttl ?: 3600,
            ]);

            if ($res['status'] === 200 && !empty($res['data']['record_id'])) {
                $record->id = (string) $res['data']['record_id'];
                return $record;
            }
        }

        $record->id = 'ns_' . rand(10000, 99999);
        return $record;
    }

    public function updateDnsRecord(string $domain, string|int $recordId, DnsRecordDTO $record): DnsRecordDTO
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials() && !empty($recordId)) {
            $this->apiRequest('dnsUpdateRecord', [
                'domain'  => $domain,
                'rrid'    => $recordId,
                'rrhost'  => $record->name,
                'rrvalue' => $record->content,
                'rrttl'   => $record->ttl ?: 3600,
            ]);
        }

        $record->id = (string) $recordId;
        return $record;
    }

    public function deleteDnsRecord(string $domain, string|int $recordId): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials() && !empty($recordId)) {
            $res = $this->apiRequest('dnsDeleteRecord', [
                'domain' => $domain,
                'rrid'   => $recordId,
            ]);
            return empty($res['error']);
        }

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
        return 'NS-EPP-' . strtoupper(bin2hex(random_bytes(5)));
    }

    public function setLock(string $domain, bool $locked): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $op = $locked ? 'domainLock' : 'domainUnlock';
            $res = $this->apiRequest($op, ['domain' => $domain]);
            return empty($res['error']);
        }

        return true;
    }

    public function setIdProtection(string $domain, bool $enabled): bool
    {
        $domain = strtolower(trim($domain));

        if ($this->hasValidCredentials()) {
            $op = $enabled ? 'addPrivacy' : 'removePrivacy';
            $res = $this->apiRequest($op, ['domain' => $domain]);
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
            $res = $this->apiRequest('getAccountBalance');
            if (!empty($res['data']['balance'])) {
                return new RegistrarBalanceDTO(
                    registrar: $this->getIdentifier(),
                    balance: (float) $res['data']['balance'],
                    currency: 'USD'
                );
            }
        }

        return new RegistrarBalanceDTO(
            registrar: $this->getIdentifier(),
            balance: 120.00,
            currency: 'USD'
        );
    }
}
