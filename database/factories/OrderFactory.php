<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.mb_str_pad((string) fake()->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT),
            'currency_code' => 'IDR',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => 'pending',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function asGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_email' => fake()->email(),
            'guest_name' => fake()->name(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
