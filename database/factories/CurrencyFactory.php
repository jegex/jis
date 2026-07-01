<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'code' => 'IDR',
            'name' => 'Indonesian Rupiah',
            'symbol' => 'Rp',
            'exchange_rate' => 1,
            'decimal_place' => 0,
            'is_default' => true,
        ];
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 16000,
            'decimal_place' => 2,
            'is_default' => false,
        ]);
    }
}
