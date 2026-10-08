<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        int $quantity = 1,
    ): Order {
        return DB::transaction(function () use ($product, $user, $guestEmail, $guestName, $couponCode, $notes, $quantity) {
            $quantity = min(max($quantity, 1), (int) config('checkout.max_quantity', 99));
            $subtotal = (int) ($product->price * $quantity);
            $discount = 0;

            if ($couponCode) {
                $coupon = $this->couponService->validateForUse($couponCode, $product);
                if ($coupon) {
                    $discount = $this->couponService->calculateDiscount($coupon, $subtotal);
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

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->title,
                'price' => $product->price,
                'quantity' => $quantity,
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
        });
    }

    public function markAsPaid(Order $order, string $gateway, string $transactionId, ?string $orderId = null): void
    {
        DB::transaction(function () use ($order, $gateway, $transactionId, $orderId) {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === OrderStatus::Paid) {
                return;
            }

            $lockedOrder->update([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
            ]);

            $data = [
                'gateway' => $gateway,
                'gateway_transaction_id' => $transactionId,
                'gateway_status' => 'success',
                'status' => PaymentStatus::Success->value,
                'paid_at' => now(),
                'currency_code' => $lockedOrder->currency_code,
                'amount' => (int) $lockedOrder->total,
            ];

            $lockedOrder->payments()->updateOrCreate(
                ['gateway_transaction_id' => $orderId ?? $transactionId],
                $data,
            );
        });
    }
}
