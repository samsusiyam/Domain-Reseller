<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            SystemSettingSeeder::class,
            RegistrarModuleSeeder::class,
            TldPricingSeeder::class,
            EmailTemplateSeeder::class,
            UserSeeder::class,
        ]);
    }
}
