<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ApiKey;
use App\Models\Currency;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usd = Currency::where('code', 'USD')->first();

        // 1. Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@domainreseller.local'],
            [
                'uuid' => (string) Str::uuid(),
                'role' => 'admin',
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'phone' => '+8801700000001',
                'company_name' => 'Domain Reseller BD HQ',
                'country' => 'BD',
                'city' => 'Dhaka',
                'balance' => 1000.0000,
                'currency_id' => $usd?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2. Reseller
        $reseller = User::updateOrCreate(
            ['email' => 'reseller@domainreseller.local'],
            [
                'uuid' => (string) Str::uuid(),
                'role' => 'reseller',
                'name' => 'TopHost Reseller',
                'password' => Hash::make('password123'),
                'phone' => '+8801700000002',
                'company_name' => 'TopHost Bangladesh',
                'country' => 'BD',
                'city' => 'Chittagong',
                'balance' => 250.0000,
                'currency_id' => $usd?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Generate default API key for reseller (API Key: 'dr_live_demo1234567890abcdef')
        ApiKey::updateOrCreate(
            ['user_id' => $reseller->id, 'key_name' => 'Default WHMCS Integration'],
            [
                'api_key' => hash('sha256', 'dr_live_demo1234567890abcdef'),
                'api_secret' => 'demo_secret_key_for_whmcs_hmac_signing_12345',
                'ip_whitelist' => null, // null allows any IP or restrict to server IP
                'allowed_endpoints' => ['*'],
                'status' => 'active',
            ]
        );

        // 3. Client
        User::updateOrCreate(
            ['email' => 'client@domainreseller.local'],
            [
                'uuid' => (string) Str::uuid(),
                'role' => 'client',
                'name' => 'Rakib Hasan',
                'password' => Hash::make('password123'),
                'phone' => '+8801700000003',
                'company_name' => 'Rakib Tech Ltd',
                'country' => 'BD',
                'city' => 'Dhaka',
                'balance' => 50.0000,
                'currency_id' => $usd?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
