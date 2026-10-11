<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderAccessService;
use Illuminate\Support\Facades\URL;

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

it('lets a guest download a paid order item with a valid signed url', function () {
    [$order, $product] = paidOrderWithProduct();

    expect($order->user_id)->toBeNull();

    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, 'fake file content');
    $product->addMedia($tempFile)->usingName('test-download.txt')->toMediaCollection('file');

    $url = URL::temporarySignedRoute('payment.download', now()->addHours(24), [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    $this->get($url)->assertStatus(200);
});

it('rejects a download without a valid signature when unauthenticated', function () {
    [$order, $product] = paidOrderWithProduct();

    $this->get(route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
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
