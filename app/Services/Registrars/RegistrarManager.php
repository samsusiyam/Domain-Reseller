<?php

namespace App\Services\Registrars;

use App\Contracts\RegistrarModuleInterface;
use App\Models\RegistrarModule;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class RegistrarManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('registrars.default', 'namecheap');
    }

    /**
     * Resolve registrar driver with dynamic config from DB if available.
     */
    public function driver($driver = null): RegistrarModuleInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();
        return parent::driver($driver);
    }

    protected function getMergedConfig(string $slug): array
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

    public function createNamecheapDriver(): RegistrarModuleInterface
    {
        return new NamecheapRegistrar($this->getMergedConfig('namecheap'));
    }

    public function createSpaceshipDriver(): RegistrarModuleInterface
    {
        return new SpaceshipRegistrar($this->getMergedConfig('spaceship'));
    }

    public function createDynadotDriver(): RegistrarModuleInterface
    {
        return new DynadotRegistrar($this->getMergedConfig('dynadot'));
    }

    public function createAtakdomainDriver(): RegistrarModuleInterface
    {
        return new AtakDomainRegistrar($this->getMergedConfig('atakdomain'));
    }

    public function createNamesiloDriver(): RegistrarModuleInterface
    {
        return new NamesiloRegistrar($this->getMergedConfig('namesilo'));
    }
}
