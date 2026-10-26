<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\RegistrarModule;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDomainStatusCommand extends Command
{
    protected $signature = 'domains:sync-status {--registrar=} {--limit=100}';
    protected $description = 'Poll upstream registrar APIs to sync status, expiry date, lock, and privacy';

    public function handle(RegistrarManager $registrarManager): int
    {
        $limit = (int) $this->option('limit');
        $query = Domain::whereIn('status', ['active', 'pending_transfer', 'expired'])->with('registrar');

        if ($this->option('registrar')) {
            $regSlug = $this->option('registrar');
            $query->whereHas('registrar', function ($q) use ($regSlug) {
                $q->where('slug', $regSlug);
            });
        }

        $domains = $query->take($limit)->get();
        $this->info("Starting status synchronization for {$domains->count()} domains...");

        $synced = 0;
        $failed = 0;

        foreach ($domains as $domain) {
            if (!$domain->registrar) {
                continue;
            }

            try {
                $driver = $registrarManager->driver($domain->registrar->slug);
                $details = $driver->getDomainDetails($domain->domain_name);

                $domain->update([
                    'status'     => $details->status,
                    'expires_at' => $details->expiresAt ?? $domain->expires_at,
                    'is_locked'  => $details->isLocked,
                    'is_private' => $details->isPrivate,
                    'nameservers'=> $details->nameservers ?? $domain->nameservers,
                ]);

                $synced++;
                $this->line("Synced: {$domain->domain_name} -> {$details->status}");
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Failed {$domain->domain_name}: " . $e->getMessage());
                Log::warning("Domain sync failed for {$domain->domain_name}: " . $e->getMessage());
            }
        }

        $this->info("Domain synchronization finished: {$synced} synced, {$failed} failed.");
        return Command::SUCCESS;
    }
}
