<?php

namespace App\Console\Commands;

use App\Services\Currency\CurrencyConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCurrencyRatesCommand extends Command
{
    protected $signature = 'currency:update-rates {--force}';
    protected $description = 'Fetch live USD/BDT exchange rates from external API and update currencies table';

    public function handle(CurrencyConverter $converter): int
    {
        $this->info('Starting currency exchange rate update...');

        try {
            $updated = $converter->syncRates();

            if ($updated) {
                $this->info('Successfully synchronized live currency exchange rates.');
                Log::info('Currency exchange rates successfully synchronized.');
            } else {
                $this->warn('Currency rate update skipped or no changes detected.');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to sync currency rates: ' . $e->getMessage());
            Log::error('Currency sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
