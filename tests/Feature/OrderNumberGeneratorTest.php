<?php

declare(strict_types=1);

use App\Models\NumberCounter;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates the first order number of the month with the default format', function () {
    $number = app(OrderNumberGenerator::class)->generate();

    expect($number)->toBe('ORD-'.now()->format('Ym').'-0001');
});

it('increments the sequence within the same period', function () {
    $generator = app(OrderNumberGenerator::class);

    $generator->generate();

    expect($generator->generate())->toBe('ORD-'.now()->format('Ym').'-0002');
});

it('resets the sequence in a new month', function () {
    $generator = app(OrderNumberGenerator::class);

    $generator->generate();

    $this->travelTo(now()->addMonth());

    expect($generator->generate())->toBe('ORD-'.now()->format('Ym').'-0001');
});

it('never resets a global sequence', function () {
    Setting::set('order_number_format', 'ORD-{SEQ}');

    $generator = app(OrderNumberGenerator::class);

    $generator->generate();

    $this->travelTo(now()->addMonthsNoOverflow(3));

    expect($generator->generate())->toBe('ORD-0002');
});

it('applies the configured padding', function () {
    Setting::set('order_number_padding', 6);

    expect(app(OrderNumberGenerator::class)->generate())
        ->toBe('ORD-'.now()->format('Ym').'-000001');
});

it('skips an order number that already exists', function () {
    Order::factory()->create([
        'order_number' => 'ORD-'.now()->format('Ym').'-0001',
    ]);

    expect(app(OrderNumberGenerator::class)->generate())
        ->toBe('ORD-'.now()->format('Ym').'-0002');
});

it('falls back to the default format when the saved format is invalid', function () {
    Setting::set('order_number_format', 'NOT-{VALID}');

    expect(app(OrderNumberGenerator::class)->generate())
        ->toBe('ORD-'.now()->format('Ym').'-0001');
});

it('continues the sequence when the format changes', function () {
    NumberCounter::factory()->create([
        'key' => 'seq:M:'.now()->format('Y-m'),
        'value' => 41,
    ]);

    Setting::set('order_number_format', 'ORD-{YYYY}{MM}-{SEQ:M}');

    expect(app(OrderNumberGenerator::class)->generate())
        ->toBe('ORD-'.now()->format('Ym').'-0042');

    Setting::set('order_number_format', 'SHOP/{YYYY}-{SEQ:M}');

    expect(app(OrderNumberGenerator::class)->generate())
        ->toBe('SHOP/'.now()->format('Y').'-0043');
});

it('previews the next numbers without consuming the sequence', function () {
    $generator = app(OrderNumberGenerator::class);

    expect($generator->preview(3))->toBe([
        'ORD-'.now()->format('Ym').'-0001',
        'ORD-'.now()->format('Ym').'-0002',
        'ORD-'.now()->format('Ym').'-0003',
    ]);

    expect($generator->generate())->toBe('ORD-'.now()->format('Ym').'-0001');
});

it('previews using a pattern and padding that are not saved yet', function () {
    $preview = app(OrderNumberGenerator::class)->preview(
        count: 2,
        pattern: 'SHOP/{YYYY}/{SEQ:Y}',
        padding: 3,
    );

    expect($preview)->toBe([
        'SHOP/'.now()->format('Y').'/001',
        'SHOP/'.now()->format('Y').'/002',
    ]);
});

it('assigns an order number automatically when creating an order', function () {
    $order = Order::create([
        'guest_email' => 'guest@example.com',
        'guest_name' => 'Guest User',
        'currency_code' => 'IDR',
        'subtotal' => 100000,
        'discount' => 0,
        'total' => 100000,
        'status' => 'pending',
    ]);

    expect($order->order_number)->toBe('ORD-'.now()->format('Ym').'-0001');
});

it('keeps a manually provided order number', function () {
    $order = Order::create([
        'order_number' => 'ORD-CUSTOM-1',
        'guest_email' => 'guest@example.com',
        'guest_name' => 'Guest User',
        'currency_code' => 'IDR',
        'subtotal' => 100000,
        'discount' => 0,
        'total' => 100000,
        'status' => 'pending',
    ]);

    expect($order->order_number)->toBe('ORD-CUSTOM-1');
});
