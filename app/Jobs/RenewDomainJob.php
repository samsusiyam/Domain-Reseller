<?php

namespace App\Jobs;

use App\Models\OrderItem;
use App\Models\Domain;
use App\DTOs\DomainRenewalDTO;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RenewDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        protected int $orderItemId
    ) {}

    public function handle(RegistrarManager $registrarManager): void
    {
        $orderItem = OrderItem::with(['order.user', 'registrar', 'domain'])->find($this->orderItemId);
        if (!$orderItem || $orderItem->status === 'completed') {
            return;
        }

        $domain = $orderItem->domain ?? Domain::where('domain_name', $orderItem->domain_name)->first();
        if (!$domain) {
            $orderItem->update(['status' => 'failed', 'registrar_response' => ['error' => 'Domain record not found']]);
            return;
        }

        try {
            $registrar = $registrarManager->driver($domain->registrar->slug);
            $dto = new DomainRenewalDTO(domain: $domain->domain_name, years: $orderItem->years);
            $result = $registrar->renew($dto);

            if ($result->success) {
                $domain->update([
                    'status' => 'active',
                    'expires_at' => $result->expiresAt ?? $domain->expires_at->addYears($orderItem->years),
                    'last_sync_at' => now(),
                ]);

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
            Log::channel('registrar')->error("Failed to renew domain {$domain->domain_name}: " . $e->getMessage());
            throw $e;
        }
    }
}
