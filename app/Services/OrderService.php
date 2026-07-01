<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

final class OrderService
{
    public function __construct(
        private CouponService $couponService,
    ) {}

    public function createOrder(
        Product $product,
        ?User $user = null,
        ?string $guestEmail = null,
        ?string $guestName = null,
        ?string $couponCode = null,
        ?string $notes = null,
    ): Order {
        $subtotal = $product->price;
        $discount = 0;

        if ($couponCode) {
            $coupon = $this->couponService->validateCoupon($couponCode, $product);
            if ($coupon) {
                $discount = $this->couponService->calculateDiscount($coupon, $subtotal);
                $coupon->increment('used_count');
            }
        }

        $total = $subtotal - $discount;

        $order = Order::create([
            'user_id' => $user?->id,
            'guest_email' => $guestEmail,
            'guest_name' => $guestName,
            'currency_code' => $product->currency_code,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon_id' => isset($coupon) ? $coupon->id : null,
            'status' => OrderStatus::Pending,
            'notes' => $notes,
            'locale' => app()->getLocale(),
        ]);

        $order->update([
            'order_number' => $this->generateOrderNumber(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->title,
            'price' => $product->price,
            'quantity' => 1,
        ]);

        if (isset($coupon) && $discount > 0) {
            $order->discounts()->create([
                'coupon_id' => $coupon->id,
                'coupon_snapshot' => [
                    'code' => $coupon->code,
                    'type' => $coupon->type->value,
                    'value' => $coupon->value,
                ],
                'amount' => $discount,
                'subtotal' => $subtotal,
            ]);
        }

        return $order;
    }

    public function generateOrderNumber(): string
    {
        return 'ORD-'.Str::random(8);
    }

    public function markAsPaid(Order $order, string $gateway, string $transactionId, ?string $orderId = null): void
    {
        if ($order->status === OrderStatus::Paid) {
            return;
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);

        $payment = $order->payments()
            ->where('gateway_transaction_id', $orderId ?? $transactionId)
            ->first();

        $data = [
            'gateway' => $gateway,
            'gateway_transaction_id' => $transactionId,
            'gateway_status' => 'success',
            'status' => PaymentStatus::Success->value,
            'paid_at' => now(),
        ];

        if ($payment) {
            $payment->update($data);
        } else {
            $order->payments()->create(array_merge($data, [
                'currency_code' => $order->currency_code,
                'amount' => (int) $order->total,
            ]));
        }
    }
}
