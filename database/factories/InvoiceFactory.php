<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'number' => sprintf(
                'INV/%s/%s/%04d',
                now()->format('Y'),
                now()->format('m'),
                fake()->unique()->randomNumber(4),
            ),
            'issued_at' => now(),
        ];
    }
}
