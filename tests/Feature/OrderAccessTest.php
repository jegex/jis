<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderAccessService;

beforeEach(function () {
    config(['checkout.allow_guest' => false]);
});

function paidOrderWithProduct(): array
{
    $order = Order::factory()->paid()->create();
    $product = Product::factory()->create();
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => 'Digital Product',
        'price' => 10000,
        'quantity' => 1,
    ]);

    return [$order, $product];
}

it('issues a token, stores only its hash, and resolves it back', function () {
    $order = Order::factory()->paid()->create();
    $service = app(OrderAccessService::class);

    $token = $service->issue($order);
    $order->refresh();

    expect($order->access_token_hash)->toBe(hash('sha256', $token))
        ->and($order->access_token_hash)->not->toBe($token)
        ->and($order->access_token_issued_at)->not->toBeNull()
        ->and($service->resolve($token)?->id)->toBe($order->id)
        ->and($service->resolve('unknown-token'))->toBeNull();
});

it('rotates the token, invalidating the previous one', function () {
    $order = Order::factory()->paid()->create();
    $service = app(OrderAccessService::class);

    $old = $service->issue($order);
    $new = $service->rotate($order->fresh());

    expect($new)->not->toBe($old)
        ->and($service->resolve($old))->toBeNull()
        ->and($service->resolve($new)?->id)->toBe($order->id);
});

it('shows the order access page for a valid token', function () {
    [$order] = paidOrderWithProduct();
    $token = app(OrderAccessService::class)->issue($order);

    $this->get(route('order.access', $token))
        ->assertOk()
        ->assertSee('Digital Product');
});

it('returns 404 for an unknown access token', function () {
    $this->get(route('order.access', 'not-a-real-token'))->assertNotFound();
});

it('returns 404 for a rotated-out access token', function () {
    $order = Order::factory()->paid()->create();
    $service = app(OrderAccessService::class);

    $old = $service->issue($order);
    $service->rotate($order->fresh());

    $this->get(route('order.access', $old))->assertNotFound();
});

it('lets a guest download a paid order item with a valid access token', function () {
    [$order, $product] = paidOrderWithProduct();

    expect($order->user_id)->toBeNull();

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'fake file content');
    $product->addMedia($tempFile)->usingName('test-download.txt')->toMediaCollection('file');

    $token = app(OrderAccessService::class)->issue($order);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $token,
    ]))->assertStatus(200);
});

it('rejects a download without a valid token when unauthenticated', function () {
    [$order, $product] = paidOrderWithProduct();

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]))->assertStatus(401);
});

it('rejects a download when the token belongs to another order', function () {
    [$order, $product] = paidOrderWithProduct();
    $other = Order::factory()->paid()->create();

    $token = app(OrderAccessService::class)->issue($other);

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $token,
    ]))->assertStatus(401);
});

it('rejects a download after the access token has been rotated', function () {
    [$order, $product] = paidOrderWithProduct();

    $service = app(OrderAccessService::class);
    $old = $service->issue($order);
    $service->rotate($order->fresh());

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
        'token' => $old,
    ]))->assertStatus(401);
});

it('rate limits repeated download attempts by ip', function () {
    [$order, $product] = paidOrderWithProduct();

    $url = route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    for ($i = 0; $i < 10; $i++) {
        $this->get($url)->assertStatus(401);
    }

    $this->get($url)->assertStatus(429);
});

it('rate limits the order access page by ip', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->get(route('order.access', 'unknown-token'))->assertNotFound();
    }

    $this->get(route('order.access', 'unknown-token'))->assertStatus(429);
});

it('rate limits downloads per order, not globally by ip', function () {
    [$orderA, $product] = paidOrderWithProduct();

    $orderB = Order::factory()->paid()->create();
    $orderB->items()->create([
        'product_id' => $product->id,
        'product_name' => 'Digital Product',
        'price' => 10000,
        'quantity' => 1,
    ]);

    $urlA = route('payment.download', ['order' => $orderA->order_number, 'product' => $product->id]);
    $urlB = route('payment.download', ['order' => $orderB->order_number, 'product' => $product->id]);

    for ($i = 0; $i < 30; $i++) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.'.($i + 1)])
            ->get($urlA)
            ->assertStatus(401);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.250'])
        ->get($urlA)
        ->assertStatus(429);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.251'])
        ->get($urlB)
        ->assertStatus(401);
});
