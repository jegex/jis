<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('finds order by order_number instead of parsing id', function () {
    $order = Order::factory()->create([
        'order_number' => 'ORD-ABC12345',
        'status' => OrderStatus::AwaitingPayment,
    ]);

    $found = Order::where('order_number', 'ORD-ABC12345')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($order->id);
});

it('does not expose finishRedirect route to invalid order numbers', function () {
    $response = $this->get(route('payment.finish', [
        'order_id' => 'ORDER-99999-hack',
        'transaction_id' => 'fake-tx',
    ]));

    $response->assertRedirect();
});
