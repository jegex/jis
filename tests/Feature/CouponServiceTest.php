<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Currency::factory()->create();
    $this->service = app(CouponService::class);
});

it('validates a valid coupon', function () {
    $coupon = Coupon::factory()->create([
        'max_uses' => 10,
        'used_count' => 0,
    ]);
    $product = Product::factory()->create(['currency_id' => $this->currency->id]);

    $result = $this->service->validateCoupon($coupon->code, $product);

    expect($result)->not->toBeNull()
        ->and($result->code)->toBe($coupon->code);
});

it('returns null for non-existent coupon', function () {
    $result = $this->service->validateCoupon('NONEXISTENT');

    expect($result)->toBeNull();
});

it('returns null for expired coupon', function () {
    $coupon = Coupon::factory()->expired()->create();

    $result = $this->service->validateCoupon($coupon->code);

    expect($result)->toBeNull();
});

it('returns null when specific product coupon used on wrong product', function () {
    $product = Product::factory()->create(['currency_id' => $this->currency->id]);
    $coupon = Coupon::factory()->create([
        'applies_to' => 'specific_product',
        'product_id' => $product->id,
    ]);
    $otherProduct = Product::factory()->create(['currency_id' => $this->currency->id]);

    $result = $this->service->validateCoupon($coupon->code, $otherProduct);

    expect($result)->toBeNull();
});

it('calculates fixed discount correctly', function () {
    $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 5000]);

    $discount = $this->service->calculateDiscount($coupon, 20000);

    expect($discount)->toBe(5000);
});

it('calculates percentage discount correctly', function () {
    $coupon = Coupon::factory()->percentage()->create(['value' => 10]);

    $discount = $this->service->calculateDiscount($coupon, 100000);

    expect($discount)->toBe(10000);
});

it('caps fixed discount at subtotal', function () {
    $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 50000]);

    $discount = $this->service->calculateDiscount($coupon, 20000);

    expect($discount)->toBe(20000);
});

it('returns correct validation error messages', function () {
    expect($this->service->getValidationError('NONEXISTENT'))->toBe('Invalid coupon code');

    $expired = Coupon::factory()->expired()->create();
    expect($this->service->getValidationError($expired->code))->toBe('Coupon has expired');

    $maxed = Coupon::factory()->create(['max_uses' => 1, 'used_count' => 1]);
    expect($this->service->getValidationError($maxed->code))->toBe('Coupon has reached its usage limit');
});
