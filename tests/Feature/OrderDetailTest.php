<?php

declare(strict_types=1);

use App\Livewire\OrderDetail;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

function createOrderDetailPreorderProduct(): Product
{
    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    return Product::factory()->preorder()->create([
        'currency_id' => $currency->id,
        'price' => 100000,
    ]);
}

it('hides download button for unreleased preorder items', function () {
    $user = User::factory()->create();
    $product = createOrderDetailPreorderProduct();

    $order = Order::factory()->forUser()->paid()->create(['user_id' => $user->id]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(OrderDetail::class, ['order' => $order])
        ->assertOk()
        ->assertSee('Pending Delivery')
        ->assertDontSeeText('Download');
});

it('shows download button for released preorder items', function () {
    $user = User::factory()->create();
    $product = createOrderDetailPreorderProduct();

    $order = Order::factory()->forUser()->paid()->create([
        'user_id' => $user->id,
        'preorder_released_at' => now(),
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(OrderDetail::class, ['order' => $order])
        ->assertOk()
        ->assertSee('Download');
});

it('shows download button for non-preorder items', function () {
    $user = User::factory()->create();
    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    $product = Product::factory()->create([
        'currency_id' => $currency->id,
        'price' => 100000,
        'is_preorder' => false,
    ]);

    $order = Order::factory()->forUser()->paid()->create(['user_id' => $user->id]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(OrderDetail::class, ['order' => $order])
        ->assertOk()
        ->assertSee('Download');
});
