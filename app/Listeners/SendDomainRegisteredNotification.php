<?php

namespace App\Listeners;

use App\Events\DomainRegistered;
use App\Jobs\SendEmailJob;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendDomainRegisteredNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected WebhookDispatcher $webhookDispatcher
    ) {}

    public function handle(DomainRegistered $event): void
    {
        $domain = $event->domain;
        $user = $event->user;

        // 1. Dispatch Customer Email
        try {
            SendEmailJob::dispatch($user->email, 'domain_registered', [
                'user_name'    => $user->name,
                'domain_name'  => $domain->domain_name,
                'years'        => $domain->registration_years ?? 1,
                'expires_at'   => $domain->expires_at ? $domain->expires_at->format('M d, Y') : 'N/A',
                'manage_url'   => route('client.domains.show', $domain->id),
                'company_name' => config('app.name', 'HostNibo Domain Hub'),
            ], $user->id);
        } catch (\Throwable $e) {
            Log::error("Failed to queue domain_registered email for {$domain->domain_name}: " . $e->getMessage());
        }

        // 2. Dispatch Reseller Webhook
        try {
            $this->webhookDispatcher->dispatch('domain.registered', [
                'event'         => 'domain.registered',
                'domain'        => $domain->domain_name,
                'status'        => $domain->status,
                'registered_at' => $domain->registered_at?->toIso8601String(),
                'expires_at'    => $domain->expires_at?->toIso8601String(),
                'timestamp'     => now()->toIso8601String(),
            ], $user);
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch reseller webhook domain.registered for {$domain->domain_name}: " . $e->getMessage());
        }
    }
}
