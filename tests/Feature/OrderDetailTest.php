<?php

declare(strict_types=1);

use App\Livewire\OrderDetail;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

function createOrderDetailProduct(): Product
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
    ]);
}

it('shows download button for paid order items', function () {
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
