<?php

namespace App\Services\Registrars;

use App\Contracts\RegistrarModuleInterface;
use App\Models\RegistrarModule;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class RegistrarManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('registrars.default', 'namecheap');
    }

    public function driver($driver = null): RegistrarModuleInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();
        return parent::driver($driver);
    }

    public function getMergedConfig(string $slug): array
    {
        $fileConfig = config("registrars.modules.{$slug}", []);

        try {
            $dbModule = RegistrarModule::where('slug', $slug)->first();
            if ($dbModule && is_array($dbModule->config)) {
                return array_merge($fileConfig, $dbModule->config, [
                    'sandbox' => $dbModule->environment === 'sandbox',
                ]);
            }
        } catch (\Throwable $e) {
            // DB not ready or migrated yet, fallback to config file
        }

        return $fileConfig;
    }

    protected function resolveModuleClass(string $slug, string $defaultClass): RegistrarModuleInterface
    {
        $config = $this->getMergedConfig($slug);
        $moduleClass = "\\Modules\\Registers\\" . ucfirst($slug) . "\\" . $defaultClass;
        
        if (class_exists($moduleClass)) {
            return new $moduleClass($config);
        }

        $fallbackClass = "\\App\\Services\\Registrars\\" . $defaultClass;
        return new $fallbackClass($config);
    }

    public function createNamecheapDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('namecheap', 'NamecheapRegistrar');
    }

    public function createSpaceshipDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('spaceship', 'SpaceshipRegistrar');
    }

    public function createDynadotDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('dynadot', 'DynadotRegistrar');
    }

    public function createAtakdomainDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('atakdomain', 'AtakDomainRegistrar');
    }

    public function createNamesiloDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('namesilo', 'NamesiloRegistrar');
    }

    public function createCosmotownDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('cosmotown', 'CosmotownRegistrar');
    }

    public function createUnstoppabledomainsDriver(): RegistrarModuleInterface
    {
        return $this->resolveModuleClass('unstoppabledomains', 'UnstoppableDomainsRegistrar');
    }
}
