<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\Currency;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Currency::factory()->create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'decimal_place' => 2,
        'is_default' => true,
    ]);
});

it('prevents coupon use when max_uses reached inside transaction', function () {
    $coupon = Coupon::factory()->create([
        'max_uses' => 1,
        'used_count' => 1,
    ]);

    $service = app(CouponService::class);
    $result = $service->validateForUse($coupon->code);

    expect($result)->toBeNull();
});

it('allows coupon use when under max_uses', function () {
    $coupon = Coupon::factory()->create([
        'max_uses' => 5,
        'used_count' => 2,
    ]);

    $service = app(CouponService::class);
    $result = $service->validateForUse($coupon->code);

    expect($result)->not->toBeNull()
        ->and($result->used_count)->toBe(3);
});

it('returns null for expired coupon in validateForUse', function () {
    $coupon = Coupon::factory()->expired()->create();

    $service = app(CouponService::class);
    $result = $service->validateForUse($coupon->code);

    expect($result)->toBeNull();
});

it('deduplicates CouponService validation logic', function () {
    $coupon = Coupon::factory()->create([
        'max_uses' => 10,
        'used_count' => 0,
        'expires_at' => now()->addDays(7),
    ]);

    $service = app(CouponService::class);

    $validated = $service->validateCoupon($coupon->code);
    expect($validated)->not->toBeNull();

    $used = $service->validateForUse($coupon->code);
    expect($used)->not->toBeNull()
        ->and($used->fresh()->used_count)->toBe(1);
});
