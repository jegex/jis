<?php

declare(strict_types=1);

use App\Livewire\CheckoutForm;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function createCheckoutAsGuestProduct(): Product
{
    return Product::factory()->create([
        'currency_id' => ensureIdrCurrency()->id,
        'price' => 0,
    ])->load('currency');
}

beforeEach(function () {
    config()->set('checkout.allow_guest', true);
});

it('shows checkout to an unauthenticated visitor with guest contact fields', function () {
    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->assertOk()
        ->assertSee('guest_email')
        ->assertSee('guest_phone')
        ->assertSee('guest_address');
});

it('lets an unauthenticated visitor buy as a guest, storing the trimmed contact details', function () {
    Queue::fake();

    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->set('guestEmail', '  guest@example.com  ')
        ->set('guestName', '  Guest User  ')
        ->set('guestPhone', '  +628123456789  ')
        ->set('guestAddress', '  123 Main Street  ')
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('guest_email', 'guest@example.com')->first();

    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBeNull()
        ->and($order->guest_name)->toBe('Guest User')
        ->and($order->guest_phone)->toBe('+628123456789')
        ->and($order->guest_address)->toBe('123 Main Street');
});

it('stores null guest phone and address when only whitespace is provided', function () {
    Queue::fake();

    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->set('guestEmail', 'anon@example.com')
        ->set('guestPhone', '   ')
        ->set('guestAddress', '   ')
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('guest_email', 'anon@example.com')->first();

    expect($order)->not->toBeNull()
        ->and($order->guest_phone)->toBeNull()
        ->and($order->guest_address)->toBeNull();
});

it('creates a guest order without a name when none is provided', function () {
    Queue::fake();

    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->set('guestEmail', 'anon@example.com')
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('guest_email', 'anon@example.com')->first();

    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBeNull()
        ->and($order->guest_name)->toBeNull();
});

it('stores a null guest name when only whitespace is provided', function () {
    Queue::fake();

    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->set('guestEmail', 'anon@example.com')
        ->set('guestName', '   ')
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('guest_email', 'anon@example.com')->first();

    expect($order)->not->toBeNull()
        ->and($order->guest_name)->toBeNull();
});

it('blocks guest checkout when the email is missing or invalid', function () {
    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->call('pay')
        ->assertHasErrors('guestEmail');

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->set('guestEmail', 'not-an-email')
        ->call('pay')
        ->assertHasErrors('guestEmail');

    expect(Order::query()->count())->toBe(0);
});

it('redirects an unauthenticated visitor to login when guest checkout is disabled', function () {
    config()->set('checkout.allow_guest', false);

    $product = createCheckoutAsGuestProduct();

    Livewire::test(CheckoutForm::class, ['product' => $product])
        ->assertRedirect(route('login'));
});

it('lets a logged-in user with an unverified email check out', function () {
    Queue::fake();

    $user = User::factory()->unverified()->create();
    $product = createCheckoutAsGuestProduct();

    Livewire::actingAs($user)
        ->test(CheckoutForm::class, ['product' => $product])
        ->call('pay')
        ->assertHasNoErrors()
        ->assertRedirect();

    $order = Order::query()->where('user_id', $user->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->guest_email)->toBeNull();
});
