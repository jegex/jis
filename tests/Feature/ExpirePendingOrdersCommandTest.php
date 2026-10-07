<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('expires unpaid orders older than two hours', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'created_at' => now()->subHours(3),
    ]);

    $this->artisan('orders:expire-pending')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::Expired);
});

it('keeps unpaid orders younger than two hours', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::AwaitingPayment,
        'created_at' => now()->subHour(),
    ]);

    $this->artisan('orders:expire-pending')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::AwaitingPayment);
});

it('keeps orders in other statuses untouched', function () {
    $order = Order::factory()->paid()->create([
        'created_at' => now()->subDays(7),
    ]);

    $this->artisan('orders:expire-pending')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});
