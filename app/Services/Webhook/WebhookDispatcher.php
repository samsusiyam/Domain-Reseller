<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Models\User;
use App\Jobs\DispatchWebhookJob;

class WebhookDispatcher
{
    /**
     * Dispatch an event to reseller webhooks.
     */
    public function dispatch(string $event, array $payload, ?User $targetUser = null): void
    {
        $query = Webhook::where('is_active', true);

        if ($targetUser) {
            $query->where('user_id', $targetUser->id);
        }

        $webhooks = $query->get();

        foreach ($webhooks as $webhook) {
            if ($webhook->isSubscribedTo($event)) {
                DispatchWebhookJob::dispatch($webhook->id, $event, $payload);
            }
        }
    }
}
