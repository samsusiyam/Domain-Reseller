<?php

namespace App\Console\Commands;

use App\Models\RegistrarModule;
use App\Models\User;
use App\Jobs\SendEmailJob;
use App\Services\Registrars\RegistrarManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckRegistrarBalancesCommand extends Command
{
    protected $signature = 'registrars:check-balances {--threshold=50}';
    protected $description = 'Query live account balance for all active registrars and alert if below threshold';

    public function handle(RegistrarManager $registrarManager): int
    {
        $threshold = (float) $this->option('threshold');
        $registrars = RegistrarModule::where('is_active', true)->get();

        $this->info("Checking live balances for {$registrars->count()} active registrar modules...");

        foreach ($registrars as $registrar) {
            try {
                $driver = $registrarManager->driver($registrar->slug);
                $balanceDto = $driver->getAccountBalance();

                $registrar->update([
                    'balance'              => $balanceDto->balance,
                    'currency'             => $balanceDto->currency,
                    'last_balance_sync_at' => now(),
                ]);

                $this->line("{$registrar->name}: {$balanceDto->formatted()}");

                if ($balanceDto->balance < $threshold) {
                    $this->warn("LOW BALANCE WARNING: {$registrar->name} balance is \${$balanceDto->balance} (Below \${$threshold})");
                    Log::alert("Low balance on registrar {$registrar->name}: \${$balanceDto->balance}");

                    // Send email to all admin accounts
                    $admins = User::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        SendEmailJob::dispatch('registrar_balance_low', $admin->email, [
                            'registrar_name' => $registrar->name,
                            'current_balance'=> '$' . number_format($balanceDto->balance, 2),
                            'threshold'      => '$' . number_format($threshold, 2),
                            'admin_name'     => $admin->name,
                        ], $admin->id);
                    }
                }
            } catch (\Throwable $e) {
                $this->error("Error querying {$registrar->name}: " . $e->getMessage());
                Log::error("Failed to query registrar balance for {$registrar->name}: " . $e->getMessage());
            }
        }

        $this->info('Registrar balance check completed.');
        return Command::SUCCESS;
    }
}
