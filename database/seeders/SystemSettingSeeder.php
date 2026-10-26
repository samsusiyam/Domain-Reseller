<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General
            ['setting_key' => 'company_name', 'setting_value' => 'Domain Reseller BD', 'group' => 'general'],
            ['setting_key' => 'company_email', 'setting_value' => 'support@domainreseller.local', 'group' => 'general'],
            ['setting_key' => 'company_phone', 'setting_value' => '+8801700000000', 'group' => 'general'],
            ['setting_key' => 'company_address', 'setting_value' => 'Dhaka, Bangladesh', 'group' => 'general'],
            ['setting_key' => 'default_currency', 'setting_value' => 'USD', 'group' => 'general'],
            ['setting_key' => 'tax_percentage', 'setting_value' => '0', 'group' => 'general'],

            // Domain
            ['setting_key' => 'default_ns1', 'setting_value' => 'ns1.domainreseller.local', 'group' => 'domain'],
            ['setting_key' => 'default_ns2', 'setting_value' => 'ns2.domainreseller.local', 'group' => 'domain'],
            ['setting_key' => 'auto_renew_days_before', 'setting_value' => '7', 'group' => 'domain'],
            ['setting_key' => 'sync_interval_hours', 'setting_value' => '6', 'group' => 'domain'],

            // Security & Reseller
            ['setting_key' => 'reseller_default_rate_limit', 'setting_value' => '60', 'group' => 'security'],
            ['setting_key' => 'reseller_min_deposit', 'setting_value' => '10.00', 'group' => 'security'],
            ['setting_key' => 'client_min_deposit', 'setting_value' => '5.00', 'group' => 'security'],
            ['setting_key' => 'maintenance_mode', 'setting_value' => '0', 'group' => 'security'],

            // Automation
            ['setting_key' => 'send_expiry_reminders', 'setting_value' => '1', 'group' => 'automation'],
            ['setting_key' => 'auto_sync_domains', 'setting_value' => '1', 'group' => 'automation'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
    }
}
