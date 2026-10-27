<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TldPricing;
use App\Models\RegistrarModule;

class TldPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultRegistrar = RegistrarModule::where("slug", "namecheap")->first()
            ?? RegistrarModule::first();

        if (!$defaultRegistrar) {
            return;
        }

        $tlds = [
    // Core
    ['tld' => '.com', 'cost_register' => 9.48, 'cost_renew' => 10.98, 'cost_transfer' => 9.48, 'client_register' => 12.99, 'client_renew' => 14.49, 'client_transfer' => 12.99, 'default_reseller_register' => 10.49, 'default_reseller_renew' => 11.99, 'default_reseller_transfer' => 10.49],
    ['tld' => '.net', 'cost_register' => 11.28, 'cost_renew' => 12.98, 'cost_transfer' => 11.28, 'client_register' => 14.99, 'client_renew' => 16.49, 'client_transfer' => 14.99, 'default_reseller_register' => 12.49, 'default_reseller_renew' => 13.99, 'default_reseller_transfer' => 12.49],
    ['tld' => '.org', 'cost_register' => 10.28, 'cost_renew' => 12.48, 'cost_transfer' => 10.28, 'client_register' => 13.99, 'client_renew' => 15.99, 'client_transfer' => 13.99, 'default_reseller_register' => 11.49, 'default_reseller_renew' => 13.49, 'default_reseller_transfer' => 11.49],
    ['tld' => '.xyz', 'cost_register' => 1.98, 'cost_renew' => 11.98, 'cost_transfer' => 10.48, 'client_register' => 3.49, 'client_renew' => 13.99, 'client_transfer' => 12.49, 'default_reseller_register' => 2.49, 'default_reseller_renew' => 12.49, 'default_reseller_transfer' => 11.49],
    ['tld' => '.io', 'cost_register' => 34.98, 'cost_renew' => 38.98, 'cost_transfer' => 34.98, 'client_register' => 42.99, 'client_renew' => 45.99, 'client_transfer' => 42.99, 'default_reseller_register' => 37.99, 'default_reseller_renew' => 41.99, 'default_reseller_transfer' => 37.99],
    ['tld' => '.co', 'cost_register' => 11.48, 'cost_renew' => 24.98, 'cost_transfer' => 21.98, 'client_register' => 14.99, 'client_renew' => 28.99, 'client_transfer' => 24.99, 'default_reseller_register' => 12.99, 'default_reseller_renew' => 26.49, 'default_reseller_transfer' => 22.99],
    // Bangladesh Local
    ['tld' => '.com.bd', 'cost_register' => 28.00, 'cost_renew' => 28.00, 'cost_transfer' => 28.00, 'client_register' => 33.33, 'client_renew' => 33.33, 'client_transfer' => 33.33, 'default_reseller_register' => 30.00, 'default_reseller_renew' => 30.00, 'default_reseller_transfer' => 30.00],
    ['tld' => '.bd', 'cost_register' => 28.00, 'cost_renew' => 28.00, 'cost_transfer' => 28.00, 'client_register' => 33.33, 'client_renew' => 33.33, 'client_transfer' => 33.33, 'default_reseller_register' => 30.00, 'default_reseller_renew' => 30.00, 'default_reseller_transfer' => 30.00],
    // Trending & Tech
    ['tld' => '.ai', 'cost_register' => 79.00, 'cost_renew' => 79.00, 'cost_transfer' => 79.00, 'client_register' => 89.00, 'client_renew' => 89.00, 'client_transfer' => 89.00, 'default_reseller_register' => 82.00, 'default_reseller_renew' => 82.00, 'default_reseller_transfer' => 82.00],
    ['tld' => '.store', 'cost_register' => 1.99, 'cost_renew' => 39.99, 'cost_transfer' => 39.99, 'client_register' => 2.99, 'client_renew' => 44.99, 'client_transfer' => 44.99, 'default_reseller_register' => 2.49, 'default_reseller_renew' => 41.99, 'default_reseller_transfer' => 41.99],
    ['tld' => '.shop', 'cost_register' => 1.99, 'cost_renew' => 32.99, 'cost_transfer' => 32.99, 'client_register' => 2.99, 'client_renew' => 36.99, 'client_transfer' => 36.99, 'default_reseller_register' => 2.49, 'default_reseller_renew' => 34.00, 'default_reseller_transfer' => 34.00],
    ['tld' => '.online', 'cost_register' => 1.88, 'cost_renew' => 34.99, 'cost_transfer' => 34.99, 'client_register' => 2.49, 'client_renew' => 39.99, 'client_transfer' => 39.99, 'default_reseller_register' => 2.19, 'default_reseller_renew' => 36.99, 'default_reseller_transfer' => 36.99],
    ['tld' => '.tech', 'cost_register' => 2.99, 'cost_renew' => 44.99, 'cost_transfer' => 44.99, 'client_register' => 3.99, 'client_renew' => 49.99, 'client_transfer' => 49.99, 'default_reseller_register' => 3.49, 'default_reseller_renew' => 46.99, 'default_reseller_transfer' => 46.99],
    ['tld' => '.info', 'cost_register' => 3.99, 'cost_renew' => 21.99, 'cost_transfer' => 21.99, 'client_register' => 4.99, 'client_renew' => 24.99, 'client_transfer' => 24.99, 'default_reseller_register' => 4.49, 'default_reseller_renew' => 22.99, 'default_reseller_transfer' => 22.99],
    ['tld' => '.me', 'cost_register' => 7.99, 'cost_renew' => 19.99, 'cost_transfer' => 19.99, 'client_register' => 9.99, 'client_renew' => 22.99, 'client_transfer' => 22.99, 'default_reseller_register' => 8.99, 'default_reseller_renew' => 20.99, 'default_reseller_transfer' => 20.99],
    ['tld' => '.cloud', 'cost_register' => 2.88, 'cost_renew' => 21.99, 'cost_transfer' => 21.99, 'client_register' => 3.99, 'client_renew' => 24.99, 'client_transfer' => 24.99, 'default_reseller_register' => 3.29, 'default_reseller_renew' => 22.99, 'default_reseller_transfer' => 22.99],
    ['tld' => '.app', 'cost_register' => 12.00, 'cost_renew' => 15.00, 'cost_transfer' => 15.00, 'client_register' => 14.99, 'client_renew' => 17.99, 'client_transfer' => 17.99, 'default_reseller_register' => 13.49, 'default_reseller_renew' => 16.49, 'default_reseller_transfer' => 16.49],
    ['tld' => '.dev', 'cost_register' => 12.00, 'cost_renew' => 15.00, 'cost_transfer' => 15.00, 'client_register' => 14.99, 'client_renew' => 17.99, 'client_transfer' => 17.99, 'default_reseller_register' => 13.49, 'default_reseller_renew' => 16.49, 'default_reseller_transfer' => 16.49],
    ['tld' => '.site', 'cost_register' => 1.88, 'cost_renew' => 29.99, 'cost_transfer' => 29.99, 'client_register' => 2.49, 'client_renew' => 34.99, 'client_transfer' => 34.99, 'default_reseller_register' => 2.19, 'default_reseller_renew' => 31.99, 'default_reseller_transfer' => 31.99],
    ['tld' => '.top', 'cost_register' => 1.99, 'cost_renew' => 4.99, 'cost_transfer' => 4.99, 'client_register' => 2.99, 'client_renew' => 5.99, 'client_transfer' => 5.99, 'default_reseller_register' => 2.49, 'default_reseller_renew' => 5.49, 'default_reseller_transfer' => 5.49],
];

        foreach ($tlds as $tldData) {
            TldPricing::updateOrCreate(
                ["tld" => $tldData["tld"]],
                array_merge($tldData, [
                    "registrar_id" => $defaultRegistrar->id,
                    "min_years" => 1,
                    "max_years" => 10,
                    "is_active" => true,
                    "dns_management_available" => true,
                    "email_forwarding_available" => true,
                    "id_protection_available" => true,
                    "id_protection_cost" => 0.00,
                    "epp_code_required" => true,
                    "grace_period_days" => 30,
                    "redemption_period_days" => 30,
                    "redemption_fee" => 80.00,
                ])
            );
        }
    }
}
