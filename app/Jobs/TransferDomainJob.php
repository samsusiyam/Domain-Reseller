<?php

namespace App\Jobs;

use App\Models\OrderItem;
use App\Models\Domain;
use App\DTOs\DomainTransferDTO;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TransferDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
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

        $payload = $orderItem->registrar_payload ?? [];
        $eppCode = $payload['epp_code'] ?? '';

        try {
            $registrar = $registrarManager->driver($orderItem->registrar->slug);
            $dto = new DomainTransferDTO(domain: $orderItem->domain_name, eppCode: $eppCode);
            $result = $registrar->transfer($dto);

            if ($result->success) {
                $domain = Domain::updateOrCreate(
                    ['domain_name' => $orderItem->domain_name],
                    [
                        'user_id' => $orderItem->order->user_id,
                        'registrar_id' => $orderItem->registrar_id,
                        'tld' => '.' . explode('.', $orderItem->domain_name)[1],
                        'registration_years' => 1,
                        'status' => 'transferring_in',
                        'registrar_order_id' => $result->registrarOrderId,
                        'auth_code' => $eppCode,
                        'registered_at' => now(),
                        'expires_at' => $result->expiresAt ?? now()->addYear(),
                    ]
                );

                $orderItem->update([
                    'status' => 'completed',
                    'domain_id' => $domain->id,
                    'registrar_response' => $result->toArray(),
                ]);

                if ($orderItem->order->items()->where('status', '!=', 'completed')->count() === 0) {
                    $orderItem->order->update(['status' => 'completed']);
                }
            } else {
                $orderItem->update(['status' => 'failed', 'registrar_response' => ['error' => $result->errorMessage]]);
            }
        } catch (\Throwable $e) {
            Log::channel('registrar')->error("Failed to transfer domain {$orderItem->domain_name}: " . $e->getMessage());
            throw $e;
        }
    }
}
