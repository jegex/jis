<?php

declare(strict_types=1);

use App\Livewire\CustomerDownloads;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    config(['checkout.allow_guest' => false]);
});

it('prevents downloading order belonging to another user without signature', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $order = Order::factory()->forUser()->paid()->create([
        'user_id' => $owner->id,
    ]);
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $url = route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    $this->actingAs($other)
        ->get($url)
        ->assertStatus(401);
});

it('allows owner to download without a token', function () {
    $owner = User::factory()->create();

    $order = Order::factory()->forUser()->paid()->create([
        'user_id' => $owner->id,
    ]);
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $fileContent = 'fake file content';
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tempFile, $fileContent);
    $product->addMedia($tempFile)
        ->usingName('test-download.txt')
        ->toMediaCollection('file');

    $url = route('payment.download', [
        'order' => $order->order_number,
        'product' => $product->id,
    ]);

    $this->actingAs($owner)
        ->get($url)
        ->assertStatus(200);
});

it('prevents getDownloadUrl from generating url for other users order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $order = Order::factory()->forUser()->paid()->create([
        'user_id' => $owner->id,
    ]);
    $product = Product::factory()->create();
    $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test', 'price' => 10000, 'quantity' => 1]);

    $this->actingAs($other);

    $component = new CustomerDownloads;
    $url = $component->getDownloadUrl($order->id, $product->id);

    expect($url)->toBe('#');
});
