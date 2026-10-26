<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegistrarModule;

class RegistrarModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Namecheap',
                'slug' => 'namecheap',
                'is_active' => true,
                'is_default' => true,
                'environment' => 'sandbox',
                'config' => [
                    'api_user' => env('NAMECHEAP_API_USER', 'sandbox_user'),
                    'api_key' => env('NAMECHEAP_API_KEY', 'sandbox_key'),
                    'username' => env('NAMECHEAP_USERNAME', 'sandbox_user'),
                    'client_ip' => env('NAMECHEAP_CLIENT_IP', '127.0.0.1'),
                    'sandbox' => true,
                ],
                'balance' => 150.0000,
                'currency' => 'USD',
                'last_balance_sync_at' => now(),
            ],
            [
                'name' => 'Spaceship',
                'slug' => 'spaceship',
                'is_active' => true,
                'is_default' => false,
                'environment' => 'sandbox',
                'config' => [
                    'api_key' => env('SPACESHIP_API_KEY', 'sandbox_key'),
                    'api_secret' => env('SPACESHIP_API_SECRET', 'sandbox_secret'),
                    'sandbox' => true,
                ],
                'balance' => 200.0000,
                'currency' => 'USD',
                'last_balance_sync_at' => now(),
            ],
            [
                'name' => 'Dynadot',
                'slug' => 'dynadot',
                'is_active' => true,
                'is_default' => false,
                'environment' => 'sandbox',
                'config' => [
                    'api_key' => env('DYNADOT_API_KEY', 'sandbox_key'),
                    'sandbox' => true,
                ],
                'balance' => 75.0000,
                'currency' => 'USD',
                'last_balance_sync_at' => now(),
            ],
            [
                'name' => 'AtakDomain',
                'slug' => 'atakdomain',
                'is_active' => true,
                'is_default' => false,
                'environment' => 'sandbox',
                'config' => [
                    'api_key' => env('ATAKDOMAIN_API_KEY', 'sandbox_key'),
                    'api_secret' => env('ATAKDOMAIN_API_SECRET', 'sandbox_secret'),
                    'sandbox' => true,
                ],
                'balance' => 50.0000,
                'currency' => 'USD',
                'last_balance_sync_at' => now(),
            ],
            [
                'name' => 'NameSilo',
                'slug' => 'namesilo',
                'is_active' => true,
                'is_default' => false,
                'environment' => 'sandbox',
                'config' => [
                    'api_key' => env('NAMESILO_API_KEY', 'sandbox_key'),
                    'sandbox' => true,
                ],
                'balance' => 120.0000,
                'currency' => 'USD',
                'last_balance_sync_at' => now(),
            ],
        ];

        foreach ($modules as $module) {
            RegistrarModule::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}
