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
        $defaultRegistrar = RegistrarModule::where('slug', 'namecheap')->first()
            ?? RegistrarModule::first();

        if (!$defaultRegistrar) {
            return;
        }

        $tlds = [
            [
                'tld' => '.com',
                'cost_register' => 9.48,
                'cost_renew' => 10.98,
                'cost_transfer' => 9.48,
                'client_register' => 12.99,
                'client_renew' => 14.49,
                'client_transfer' => 12.99,
                'default_reseller_register' => 10.49,
                'default_reseller_renew' => 11.99,
                'default_reseller_transfer' => 10.49,
            ],
            [
                'tld' => '.net',
                'cost_register' => 11.28,
                'cost_renew' => 12.98,
                'cost_transfer' => 11.28,
                'client_register' => 14.99,
                'client_renew' => 16.49,
                'client_transfer' => 14.99,
                'default_reseller_register' => 12.49,
                'default_reseller_renew' => 13.99,
                'default_reseller_transfer' => 12.49,
            ],
            [
                'tld' => '.org',
                'cost_register' => 10.28,
                'cost_renew' => 12.48,
                'cost_transfer' => 10.28,
                'client_register' => 13.99,
                'client_renew' => 15.99,
                'client_transfer' => 13.99,
                'default_reseller_register' => 11.49,
                'default_reseller_renew' => 13.49,
                'default_reseller_transfer' => 11.49,
            ],
            [
                'tld' => '.xyz',
                'cost_register' => 1.98,
                'cost_renew' => 11.98,
                'cost_transfer' => 10.48,
                'client_register' => 3.49,
                'client_renew' => 13.99,
                'client_transfer' => 12.49,
                'default_reseller_register' => 2.49,
                'default_reseller_renew' => 12.49,
                'default_reseller_transfer' => 11.49,
            ],
            [
                'tld' => '.io',
                'cost_register' => 34.98,
                'cost_renew' => 38.98,
                'cost_transfer' => 34.98,
                'client_register' => 42.99,
                'client_renew' => 45.99,
                'client_transfer' => 42.99,
                'default_reseller_register' => 37.99,
                'default_reseller_renew' => 41.99,
                'default_reseller_transfer' => 37.99,
            ],
            [
                'tld' => '.co',
                'cost_register' => 11.48,
                'cost_renew' => 24.98,
                'cost_transfer' => 21.98,
                'client_register' => 14.99,
                'client_renew' => 28.99,
                'client_transfer' => 24.99,
                'default_reseller_register' => 12.99,
                'default_reseller_renew' => 26.49,
                'default_reseller_transfer' => 22.99,
            ],
        ];

        foreach ($tlds as $tldData) {
            TldPricing::updateOrCreate(
                ['tld' => $tldData['tld']],
                array_merge($tldData, [
                    'registrar_id' => $defaultRegistrar->id,
                    'min_years' => 1,
                    'max_years' => 10,
                    'is_active' => true,
                    'dns_management_available' => true,
                    'email_forwarding_available' => true,
                    'id_protection_available' => true,
                    'id_protection_cost' => 0.00,
                    'epp_code_required' => true,
                    'grace_period_days' => 30,
                    'redemption_period_days' => 30,
                    'redemption_fee' => 80.00,
                ])
            );
        }
    }
}
