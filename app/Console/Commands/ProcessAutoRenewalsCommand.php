<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\TldPricing;
use App\Services\Domain\DomainOrderService;
use App\Services\Domain\DomainPricingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoRenewalsCommand extends Command
{
    protected $signature = 'domains:process-autorenew';
    protected $description = 'Process automatic domain renewals for domains expiring within 7 days with auto-renew enabled';

    public function handle(
        DomainOrderService $orderService,
        DomainPricingService $pricingService
    ): int {
        $threshold = Carbon::now()->addDays(7);

        $domains = Domain::where('status', 'active')
            ->where('auto_renew', true)
            ->where('expires_at', '<=', $threshold)
            ->with(['user', 'tld'])
            ->get();

        $this->info("Found {$domains->count()} domains eligible for automatic renewal.");

        foreach ($domains as $domain) {
            $user = $domain->user;
            if (!$user) continue;

            $parts = explode('.', $domain->domain_name, 2);
            $tld = '.' . ($parts[1] ?? 'com');

            $pricing = $pricingService->calculateItemPrice($tld, 'renew', 1, $user);
            $costUsd = $pricing['total_usd'];

            if (!$user->hasSufficientBalance($costUsd)) {
                $this->warn("User {$user->email} has insufficient balance (\${$user->balance}) to auto-renew {$domain->domain_name} (\${$costUsd}).");
                Log::warning("Auto-renew failed for {$domain->domain_name}: insufficient balance for user {$user->id}.");
                continue;
            }

            try {
                $items = [
                    [
                        'action'    => 'renew',
                        'domain'    => $domain->domain_name,
                        'domain_id' => $domain->id,
                        'years'     => 1,
                    ]
                ];

                $orderData = $orderService->createOrder($user, $items, null, 'auto_renewal');
                $orderService->processPaymentWithWallet($orderData['invoice']);

                $this->info("Successfully triggered auto-renewal for {$domain->domain_name}.");
            } catch (\Throwable $e) {
                $this->error("Failed to auto-renew {$domain->domain_name}: " . $e->getMessage());
                Log::error("Auto-renew exception on {$domain->domain_name}: " . $e->getMessage());
            }
        }

        $this->info('Auto-renewal processing completed.');
        return Command::SUCCESS;
    }
}
