<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NumberCounter;
use Illuminate\Database\Eloquent\Factories\Factory;

final class NumberCounterFactory extends Factory
{
    protected $model = NumberCounter::class;

    public function definition(): array
    {
        return [
            'key' => 'seq:M:'.fake()->unique()->dateTimeThisDecade()->format('Y-m'),
            'value' => fake()->numberBetween(0, 999),
        ];
    }
}
