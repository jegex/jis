<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

final class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'type' => 'order_confirmation',
            'subject' => [app()->getLocale() => 'Order #{order_id} Confirmation'],
            'body' => [app()->getLocale() => 'Hello {customer_name}, your order {order_id} has been confirmed.'],
            'variables' => ['customer_name', 'order_id', 'product_name'],
            'is_active' => true,
        ];
    }
}
