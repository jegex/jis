<?php

declare(strict_types=1);

use App\Livewire\CheckoutForm;
use App\Livewire\OrderDetail;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function createCheckoutQuantityProduct(int $price): Product
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
        'price' => $price,
    ])->load('currency');
}

it('recalculates subtotal and total when quantity changes', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->set('quantity', 3)
        ->assertSet('subtotal', 150000)
        ->assertSet('total', 150000);
});

it('clamps quantity between one and the configured maximum', function () {
    config(['checkout.max_quantity' => 5]);

    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->set('quantity', 10)
        ->assertSet('quantity', 5)
        ->set('quantity', 0)
        ->assertSet('quantity', 1);
});

it('stores the chosen quantity and multiplies the subtotal on the order', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

    $order = app(OrderService::class)->createOrder(product: $product, user: $user, quantity: 3);

    expect((int) $order->subtotal)->toBe(150000)
        ->and((int) $order->total)->toBe(150000);

    expect($order->items()->first()->quantity)->toBe(3);
});

it('passes the selected quantity through checkout to the order item', function () {
    Queue::fake();

    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(0);

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->set('quantity', 2)
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('user_id', $user->id)->first();

    expect($order)->not->toBeNull()
        ->and((int) $order->total)->toBe(0)
        ->and($order->items()->first()->quantity)->toBe(2);
});

it('recalculates an applied coupon discount when quantity changes', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);
    $coupon = Coupon::factory()->percentage()->create(['value' => 10]);

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->set('couponCode', $coupon->code)
        ->call('applyCoupon')
        ->assertSet('discount', 5000)
        ->set('quantity', 3)
        ->assertSet('subtotal', 150000)
        ->assertSet('discount', 15000)
        ->assertSet('total', 135000);
});

it('applies the coupon discount to the multiplied subtotal when the order is created', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);
    $coupon = Coupon::factory()->percentage()->create(['value' => 10]);

    $order = app(OrderService::class)->createOrder(
        product: $product,
        user: $user,
        couponCode: $coupon->code,
        quantity: 3,
    );

    expect((int) $order->subtotal)->toBe(150000)
        ->and((int) $order->discount)->toBe(15000)
        ->and((int) $order->total)->toBe(135000);
});

it('clamps the quantity to the configured maximum in the order service', function () {
    config(['checkout.max_quantity' => 5]);

    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

    $order = app(OrderService::class)->createOrder(product: $product, user: $user, quantity: 9999);

    expect($order->items()->first()->quantity)->toBe(5)
        ->and((int) $order->subtotal)->toBe(250000);
});

it('shows the quantity on the order detail when it is greater than one', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

    $order = Order::factory()->forUser()->paid()->create(['user_id' => $user->id]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 3,
    ]);

    Livewire::actingAs($user)
        ->test(OrderDetail::class, ['order' => $order])
        ->assertOk()
        ->assertSee('× 3');
});

it('shows the quantity on the order detail even when it is one', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

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
        ->assertSee('× 1');
});

it('adjusts quantity with the stepper actions', function () {
    $user = User::factory()->create();
    $product = createCheckoutQuantityProduct(50000);

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->call('incrementQuantity')
        ->assertSet('quantity', 2)
        ->call('decrementQuantity')
        ->assertSet('quantity', 1)
        ->call('decrementQuantity')
        ->assertSet('quantity', 1);
});
