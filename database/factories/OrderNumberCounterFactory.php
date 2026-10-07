<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrderNumberCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OrderNumberCounterFactory extends Factory
{
    protected $model = OrderNumberCounter::class;

    public function definition(): array
    {
        return [
            'key' => 'seq:M:'.fake()->unique()->dateTimeThisDecade()->format('Y-m'),
            'value' => fake()->numberBetween(0, 999),
        ];
    }
}
