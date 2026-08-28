<?php

declare(strict_types=1);

use App\Livewire\OrderDetail;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function createOrderDetailProduct(?Carbon $releaseDate = null): Product
{
    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    return Product::factory()->create([
        'currency_id' => $currency->id,
        'price' => 100000,
        'release_date' => $releaseDate,
    ]);
}

it('hides download button for unreleased preorder items', function () {
    $user = User::factory()->create();
    $product = createOrderDetailProduct(Carbon::now()->addDays(7));

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
        ->assertSee('Pending Release')
        ->assertDontSeeText('Download');
});

it('shows download button for released preorder items', function () {
    $user = User::factory()->create();
    $product = createOrderDetailProduct(Carbon::now()->addDays(7));

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
    $product = createOrderDetailProduct();

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
