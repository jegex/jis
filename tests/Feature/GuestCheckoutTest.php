<?php

declare(strict_types=1);

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores guest email and name on an order', function () {
    $order = Order::create([
        'order_number' => 'ORD-TEST001',
        'guest_email' => 'guest@example.com',
        'guest_name' => 'Guest User',
        'currency_code' => 'IDR',
        'subtotal' => 100000,
        'discount' => 0,
        'total' => 100000,
        'status' => 'pending',
    ]);

    expect($order->guest_email)->toBe('guest@example.com')
        ->and($order->guest_name)->toBe('Guest User');
});
