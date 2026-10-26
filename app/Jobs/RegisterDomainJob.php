<?php

namespace App\Jobs;

use App\Models\OrderItem;
use App\Models\Domain;
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

    public int $tries = 3;
    public int $timeout = 120;

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
        $registrarSlug = $orderItem->registrar->slug ?? 'namecheap';
        $user = $orderItem->order->user;

        try {
            $registrar = $registrarManager->driver($registrarSlug);

            $payload = $orderItem->registrar_payload ?? [];
            $nameservers = $payload['nameservers'] ?? config('registrars.default_nameservers', []);

            $dto = new DomainRegistrationDTO(
                domain: $domainName,
                years: $orderItem->years,
                nameservers: $nameservers,
                idProtection: (bool) ($payload['id_protection'] ?? false)
            );

            $result = $registrar->register($dto);

            if ($result->success) {
                // Create or activate domain in database
                $domain = Domain::updateOrCreate(
                    ['domain_name' => $domainName],
                    [
                        'user_id' => $user->id,
                        'registrar_id' => $orderItem->registrar_id,
                        'tld' => '.' . explode('.', $domainName)[1],
                        'registration_years' => $orderItem->years,
                        'status' => 'active',
                        'registrar_order_id' => $result->registrarOrderId,
                        'registrar_domain_id' => $result->registrarDomainId,
                        'nameservers' => $nameservers,
                        'registered_at' => now(),
                        'expires_at' => $result->expiresAt ?? now()->addYears($orderItem->years),
                        'registrar_cost' => $orderItem->registrar_cost,
                        'selling_price' => $orderItem->total_price,
                        'last_sync_at' => now(),
                    ]
                );

                $orderItem->update([
                    'status' => 'completed',
                    'domain_id' => $domain->id,
                    'registrar_response' => $result->toArray(),
                ]);

                // Check if all order items are finished to complete order
                if ($orderItem->order->items()->where('status', '!=', 'completed')->count() === 0) {
                    $orderItem->order->update(['status' => 'completed']);
                }

                // Fire event
                event(new DomainRegistered($domain, $orderItem->order, $user));
            } else {
                $orderItem->update([
                    'status' => 'failed',
                    'registrar_response' => ['error' => $result->errorMessage],
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('registrar')->error("Failed to register domain {$domainName}: " . $e->getMessage());
            $orderItem->update([
                'status' => 'failed',
                'registrar_response' => ['error' => $e->getMessage()],
            ]);
            throw $e;
        }
    }
}
