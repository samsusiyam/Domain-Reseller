<?php

namespace App\Services\Registrars;

use App\Contracts\RegistrarModuleInterface;
use App\DTOs\DomainAvailabilityDTO;
use App\DTOs\DomainRegistrationDTO;
use App\DTOs\DomainRenewalDTO;
use App\DTOs\DomainTransferDTO;
use App\DTOs\DomainActionResultDTO;
use App\DTOs\DomainDetailsDTO;
use App\DTOs\DnsRecordDTO;
use App\DTOs\WhoisDataDTO;
use App\DTOs\RegistrarBalanceDTO;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

abstract class AbstractRegistrar implements RegistrarModuleInterface
{
    protected Client $client;
    protected array $config = [];
    protected bool $sandbox = true;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->sandbox = (bool) ($config['sandbox'] ?? true);
        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'connect_timeout' => 10,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    public function checkBulkAvailability(array $domains): Collection
    {
        $results = collect();
        foreach ($domains as $domain) {
            $results->put($domain, $this->checkAvailability(trim($domain)));
        }
        return $results;
    }

    protected function logApiCall(string $action, array $params, mixed $response): void
    {
        Log::channel('registrar')->debug("[{$this->getIdentifier()}] {$action}", [
            'params' => $params,
            'response' => $response,
            'sandbox' => $this->sandbox,
        ]);
    }

    public function getAccountBalance(): RegistrarBalanceDTO
    {
        return $this->getBalance();
    }

    public function getDomainDetails(string $domain): DomainDetailsDTO
    {
        return $this->getInfo($domain);
    }

    public function renewDomain(DomainRenewalDTO $dto): DomainActionResultDTO
    {
        return $this->renew($dto);
    }

    public function lockDomain(string $domain): bool
    {
        return $this->setLock($domain, true);
    }

    public function unlockDomain(string $domain): bool
    {
        return $this->setLock($domain, false);
    }

    public function toggleIdProtection(string $domain, bool $enabled): bool
    {
        return $this->setIdProtection($domain, $enabled);
    }

    public function getAuthCode(string $domain): string
    {
        return $this->getEppCode($domain);
    }

    public function updateNameservers(string $domain, array $nameservers): bool
    {
        return $this->setNameservers($domain, $nameservers);
    }
}
