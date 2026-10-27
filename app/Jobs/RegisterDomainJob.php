<?php

namespace App\Jobs;

use App\Models\OrderItem;
use App\Models\Domain;
use App\Models\RegistrarModule;
use App\DTOs\DomainRegistrationDTO;
use App\Services\Registrars\RegistrarManager;
use App\Events\DomainRegistered;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RegisterDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(
        protected int $orderItemId
    ) {}

    public function handle(RegistrarManager $registrarManager): void
    {
        $orderItem = OrderItem::with(['order.user', 'registrar'])->find($this->orderItemId);
        if (!$orderItem || $orderItem->status === 'completed') {
            return;
        }

        $domainName = $orderItem->domain_name;
        $user = $orderItem->order->user;
        $payload = $orderItem->registrar_payload ?? [];
        $nameservers = $payload['nameservers'] ?? config('registrars.default_nameservers', []);

        $dto = new DomainRegistrationDTO(
            domain: $domainName,
            years: $orderItem->years,
            nameservers: $nameservers,
            idProtection: (bool) ($payload['id_protection'] ?? false)
        );

        // Build list of candidates: Primary first, then other active modules for failover
        $primaryModule = $orderItem->registrar;
        $candidates = collect();
        if ($primaryModule && $primaryModule->is_active) {
            $candidates->push($primaryModule);
        }

        $fallbackModules = RegistrarModule::where('is_active', true)
            ->where('id', '!=', $primaryModule?->id ?? 0)
            ->orderBy('id')
            ->get();

        $candidates = $candidates->merge($fallbackModules);

        $success = false;
        $lastError = null;
        $usedRegistrar = null;
        $registrationResult = null;

        foreach ($candidates as $module) {
            try {
                Log::channel('registrar')->info("Attempting registration of {$domainName} via {$module->name} (ID: {$module->id})...");
                $driver = $registrarManager->driver($module->slug);
                $result = $driver->register($dto);

                if ($result->success) {
                    $success = true;
                    $usedRegistrar = $module;
                    $registrationResult = $result;
                    break;
                } else {
                    $lastError = $result->errorMessage ?? 'Unknown registrar error';
                    Log::channel('registrar')->warning("Registration of {$domainName} failed on {$module->name}: {$lastError}. Trying next failover registrar if available...");
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::channel('registrar')->error("Exception registering {$domainName} on {$module->name}: " . $e->getMessage());
            }
        }

        if ($success && $usedRegistrar && $registrationResult) {
            $tldParts = explode('.', $domainName);
            $tld = '.' . ($tldParts[1] ?? 'com');

            $domain = Domain::updateOrCreate(
                ['domain_name' => $domainName],
                [
                    'user_id'              => $user->id,
                    'registrar_id'          => $usedRegistrar->id,
                    'tld'                   => $tld,
                    'registration_years'    => $orderItem->years,
                    'status'                 => 'active',
                    'registrar_order_id'   => $registrationResult->registrarOrderId,
                    'registrar_domain_id'  => $registrationResult->registrarDomainId,
                    'nameservers'           => $nameservers,
                    'registered_at'        => now(),
                    'expires_at'            => $registrationResult->expiresAt ?? now()->addYears($orderItem->years),
                    'registrar_cost'        => $orderItem->registrar_cost,
                    'selling_price'         => $orderItem->total_price,
                    'last_sync_at'          => now(),
                ]
            );

            $resp = $registrationResult->toArray();
            if ($primaryModule && $usedRegistrar->id !== $primaryModule->id) {
                $resp['failover_applied'] = true;
                $resp['failover_from'] = $primaryModule->name;
                $resp['failover_to'] = $usedRegistrar->name;
            }

            $orderItem->update([
                'status'             => 'completed',
                'registrar_id'       => $usedRegistrar->id,
                'domain_id'          => $domain->id,
                'registrar_response' => $resp,
            ]);

            if ($orderItem->order->items()->where('status', '!=', 'completed')->count() === 0) {
                $orderItem->order->update(['status' => 'completed']);
            }

            event(new DomainRegistered($domain, $orderItem->order, $user));
            Log::channel('registrar')->info("Domain {$domainName} successfully registered on {$usedRegistrar->name}!");
        } else {
            $orderItem->update([
                'status'             => 'failed',
                'registrar_response' => ['error' => $lastError ?? 'All upstream registrars failed to register domain'],
            ]);
            Log::channel('registrar')->error("Domain {$domainName} failed registration across all candidate registrars: {$lastError}");
        }
    }
}
