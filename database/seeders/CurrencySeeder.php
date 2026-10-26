<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::updateOrCreate(['code' => 'USD'], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 1.000000,
            'is_primary' => true,
            'is_active' => true,
            'decimal_places' => 2,
            'symbol_position' => 'before',
            'last_updated_at' => now(),
        ]);

        Currency::updateOrCreate(['code' => 'BDT'], [
            'name' => 'Bangladeshi Taka',
            'symbol' => '৳',
            'exchange_rate' => 120.000000,
            'is_primary' => false,
            'is_active' => true,
            'decimal_places' => 2,
            'symbol_position' => 'after',
            'last_updated_at' => now(),
        ]);
    }
}
