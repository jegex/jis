<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\CheckoutForm;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function createFreeProduct(): Product
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
        'price' => 0,
    ]);
}

it('identifies a product as free when price is zero', function () {
    $product = createFreeProduct();

    expect($product->isFree())->toBeTrue()
        ->and($product->display_price)->toBe('Free');
});

it('identifies a product as not free when price is above zero', function () {
    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    $product = Product::factory()->create([
        'currency_id' => $currency->id,
        'price' => 50000,
    ]);

    expect($product->isFree())->toBeFalse()
        ->and($product->display_price)->toBe('Rp 50.000');
});

it('completes a free order without calling a payment gateway', function () {
    Queue::fake();

    $user = User::factory()->create();
    $product = createFreeProduct();

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('user_id', $user->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Paid)
        ->and((int) $order->total)->toBe(0);

    $payment = $order->payments()->first();

    expect($payment)->not->toBeNull()
        ->and($payment->gateway)->toBe('free')
        ->and($payment->status)->toBe(PaymentStatus::Success);

    expect($order->preorder_released_at)->not->toBeNull();
});
