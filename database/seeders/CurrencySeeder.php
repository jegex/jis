<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

final class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 17000,
            'decimal_place' => 2,
            'is_default' => true,
        ]);

        Currency::create([
            'code' => 'IDR',
            'name' => 'Indonesian Rupiah',
            'symbol' => 'Rp',
            'exchange_rate' => 1,
            'decimal_place' => 0,
            'is_default' => false,
        ]);
    }
}
