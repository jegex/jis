<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Services\CurrencyService;

beforeEach(function () {
    Currency::factory()->create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'decimal_place' => 2,
        'is_default' => true,
    ]);

    Currency::factory()->create([
        'code' => 'IDR',
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 18000,
        'decimal_place' => 0,
        'is_default' => false,
    ]);
});

it('converts from USD to IDR correctly', function () {
    $result = app(CurrencyService::class)->convert(64.0, 'USD', 'IDR');

    expect($result)->toBe(1152000.0);
});

it('converts from IDR to USD correctly', function () {
    $result = app(CurrencyService::class)->convert(1152000.0, 'IDR', 'USD');

    expect($result)->toBe(64.0);
});

it('returns same amount when converting to same currency', function () {
    $result = app(CurrencyService::class)->convert(100.0, 'USD', 'USD');

    expect($result)->toBe(100.0);
});

it('converts with different amounts correctly', function () {
    expect(app(CurrencyService::class)->convert(1.0, 'USD', 'IDR'))->toBe(18000.0);
    expect(app(CurrencyService::class)->convert(50.0, 'USD', 'IDR'))->toBe(900000.0);
    expect(app(CurrencyService::class)->convert(100.0, 'IDR', 'USD'))->toBe(0.01);
});

it('converts integer amounts correctly', function () {
    $service = app(CurrencyService::class);

    expect($service->convert(1, 'USD', 'IDR'))->toBe(18000.0);
    expect($service->convert(18000, 'IDR', 'USD'))->toBe(1.0);
});
