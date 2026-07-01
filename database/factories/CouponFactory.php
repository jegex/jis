<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => mb_strtoupper(fake()->bothify('DISKON??##')),
            'type' => 'fixed',
            'value' => 10000,
            'applies_to' => 'all',
            'max_uses' => 100,
            'used_count' => 0,
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'value' => 10,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
