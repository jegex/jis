<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmailTemplateType;
use App\Models\EmailLog;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 */
final class EmailLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => EmailTemplateType::OrderConfirmation,
            'recipient' => fake()->safeEmail(),
            'subject' => fake()->sentence(),
            'sent_at' => now(),
        ];
    }
}
