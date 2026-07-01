<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;

final class CouponService
{
    public function validateCoupon(string $code, ?Product $product = null): ?Coupon
    {
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->first();

        if (! $coupon || ! $coupon->isValid()) {
            return null;
        }

        if ($coupon->applies_to->value === 'specific_product' && $coupon->product_id !== $product?->id) {
            return null;
        }

        return $coupon;
    }

    public function getValidationError(string $code, ?Product $product = null): ?string
    {
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->first();

        if (! $coupon) {
            return __('Invalid coupon code');
        }

        if ($coupon->max_uses !== null && $coupon->max_uses !== 0 && $coupon->used_count >= $coupon->max_uses) {
            return __('Coupon has reached its usage limit');
        }

        if ($coupon->expires_at !== null && $coupon->expires_at->isPast()) {
            return __('Coupon has expired');
        }

        if ($coupon->applies_to->value === 'specific_product' && $coupon->product_id !== $product?->id) {
            return __('Coupon is not applicable for this product');
        }

        return null;
    }

    public function calculateDiscount(Coupon $coupon, int $subtotal): int
    {
        return match ($coupon->type->value) {
            'fixed' => min($coupon->value, $subtotal),
            'percentage' => (int) round($subtotal * $coupon->value / 100),
            default => 0,
        };
    }
}
