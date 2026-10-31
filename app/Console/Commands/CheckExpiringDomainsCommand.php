<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Jobs\SendEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringDomainsCommand extends Command
{
    protected $signature = 'domains:check-expiry {--days=30,15,7,3,1}';
    protected $description = 'Scan domains table for upcoming expirations and dispatch reminder notifications';

    public function handle(): int
    {
        $daysList = array_map('intval', explode(',', $this->option('days')));
        $today = Carbon::today();

        $this->info('Checking expiring domains for reminder thresholds: ' . implode(', ', $daysList) . ' days.');

        foreach ($daysList as $days) {
            $targetDate = $today->copy()->addDays($days)->toDateString();

            $expiringDomains = Domain::where('status', 'active')
                ->whereDate('expires_at', $targetDate)
                ->with('user')
                ->get();

            $this->line("Found {$expiringDomains->count()} domains expiring in {$days} days.");

            foreach ($expiringDomains as $domain) {
                if (!$domain->user) continue;

                $data = [
                    'user_name'    => $domain->user->name,
                    'client_name'  => $domain->user->name,
                    'domain_name'  => $domain->domain_name,
                    'expires_at'   => $domain->expires_at->format('M d, Y'),
                    'expiry_date'  => $domain->expires_at->format('M d, Y'),
                    'days_left'    => $days,
                    'days_remain'  => $days,
                    'renew_url'    => route('client.domains.show', $domain->id),
                    'renewal_url'  => route('client.domains.show', $domain->id),
                    'company_name' => config('app.name', 'HostNibo Domain Hub'),
                ];

                SendEmailJob::dispatch($domain->user->email, 'domain_expiring', $data, $domain->user->id);

                try {
                    app(\App\Services\Webhook\WebhookDispatcher::class)->dispatch('domain.expiring_soon', [
                        'event'      => 'domain.expiring_soon',
                        'domain'     => $domain->domain_name,
                        'days_left'  => $days,
                        'expires_at' => $domain->expires_at->toIso8601String(),
                        'timestamp'  => now()->toIso8601String(),
                    ], $domain->user);
                } catch (\Throwable $e) {}
            }
        }

        $this->info('Expiring domain scan completed successfully.');
        return Command::SUCCESS;
    }
}
