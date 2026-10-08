<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\PaymentSuccess;
use App\Models\Order;
use App\Models\Product;
use App\Services\CouponService;
use App\Services\CurrencyService;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Str;
use Livewire\Component;

final class CheckoutForm extends Component
{
    public Product $product;

    public int $quantity = 1;

    public string $couponCode = '';

    public string $appliedCode = '';

    public int $discount = 0;

    public int $subtotal = 0;

    public int $total = 0;

    public bool $processing = false;

    public function mount(Product $product): void
    {
        if (! $product->isPublished()) {
            abort(404);
        }

        if (! config('checkout.allow_guest') && ! auth()->check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->product = $product->load('category', 'media');
        $this->recalculate();
    }

    public function updatedQuantity(): void
    {
        $this->quantity = min(max((int) $this->quantity, 1), $this->maxQuantity());
        $this->recalculate();
    }

    public function incrementQuantity(): void
    {
        $this->quantity = min($this->quantity + 1, $this->maxQuantity());
        $this->recalculate();
    }

    public function decrementQuantity(): void
    {
        $this->quantity = max($this->quantity - 1, 1);
        $this->recalculate();
    }

    public function maxQuantity(): int
    {
        return (int) config('checkout.max_quantity', 99);
    }

    public function updatedCouponCode(): void
    {
        if ($this->couponCode !== $this->appliedCode) {
            $this->resetValidation('couponCode');
            $this->appliedCode = '';
        }
    }

    public function applyCoupon(): void
    {
        $this->resetValidation('couponCode');

        if (blank($this->couponCode)) {
            $this->appliedCode = '';
            $this->recalculate();

            return;
        }

        $service = app(CouponService::class);
        $error = $service->getValidationError($this->couponCode, $this->product);

        if ($error) {
            $this->appliedCode = '';
            $this->recalculate();
            $this->addError('couponCode', $error);

            return;
        }

        $coupon = $service->validateCoupon($this->couponCode, $this->product);

        if ($coupon) {
            $this->appliedCode = $this->couponCode;
            $this->recalculate();
        }
    }

    public function pay(): void
    {
        $this->resetValidation('payment');
        $this->processing = true;

        try {
            $user = auth()->user();

            $order = app(OrderService::class)->createOrder(
                product: $this->product,
                user: $user,
                guestEmail: $user ? null : session('guest_email'),
                guestName: $user ? null : session('guest_name'),
                couponCode: $this->appliedCode ?: null,
                quantity: $this->quantity,
            );

            $order->update(['status' => OrderStatus::CreatingPayment]);

            if ((int) $order->total <= 0) {
                $this->completeFreeOrder($order);

                $this->redirect(route('payment.success'));

                return;
            }

            $defaultGateway = config('payment.default', 'midtrans');
            $targetCurrency = config("payment.types.{$defaultGateway}.target_currency", 'IDR');

            $params = [];
            $exchangeRate = 1;

            if ($order->currency_code !== $targetCurrency) {
                $item = $order->items->first();

                $exchangeRate = app(CurrencyService::class)->convert(
                    1, $order->currency_code, $targetCurrency,
                );
                $params['converted_amount'] = app(CurrencyService::class)->convert(
                    $order->total, $order->currency_code, $targetCurrency,
                );
                $params['converted_currency'] = $targetCurrency;
                $params['converted_item_price'] = app(CurrencyService::class)->convert(
                    $item->price, $order->currency_code, $targetCurrency,
                );

                if ($order->discount > 0) {
                    $params['converted_discount'] = app(CurrencyService::class)->convert(
                        $order->discount, $order->currency_code, $targetCurrency,
                    );
                }
            }

            $order->update([
                'exchange_rate' => $exchangeRate,
                'payment_currency_code' => $targetCurrency,
            ]);

            if ($order->discount > 0) {
                $params['discount'] = $order->discount;
            }

            $paymentResult = app('payment')->charge($order, $params);

            if (! $paymentResult->success) {
                $order->update(['status' => OrderStatus::Pending]);
                $this->addError('payment', $paymentResult->errorMessage ?? 'Payment failed');
                $this->processing = false;

                return;
            }

            $order->payments()->create([
                'gateway' => 'midtrans',
                'gateway_transaction_id' => $paymentResult->transactionId,
                'gateway_status' => 'pending',
                'snap_token' => $paymentResult->snapToken,
                'redirect_url' => $paymentResult->redirectUrl,
                'currency_code' => $params['converted_currency'] ?? $order->currency_code,
                'amount' => (int) ($params['converted_amount'] ?? $order->total),
                'status' => PaymentStatus::Pending,
            ]);

            $order->update(['status' => OrderStatus::AwaitingPayment]);

            session()->flash('snap_token', $paymentResult->snapToken);

            $this->dispatch('snap-token-ready', token: $paymentResult->snapToken);
        } catch (Exception $e) {
            if (isset($order)) {
                $order->update(['status' => OrderStatus::Pending]);
            }
            $this->addError('payment', 'An error occurred. Please try again.');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        $gateway = app('payment')->driver();
        $snapJsUrl = method_exists($gateway, 'getSnapJsUrl') ? $gateway->getSnapJsUrl() : '';
        $clientKey = method_exists($gateway, 'getClientKey') ? $gateway->getClientKey() : '';

        return view('livewire.checkout-form', compact('snapJsUrl', 'clientKey'))
            ->layout('layouts.app', [
                'model' => $this->product,
            ]);
    }

    private function recalculate(): void
    {
        $this->subtotal = (int) ($this->product->price * $this->quantity);
        $this->discount = 0;

        if ($this->appliedCode !== '') {
            $service = app(CouponService::class);
            $coupon = $service->validateCoupon($this->appliedCode, $this->product);

            if ($coupon) {
                $this->discount = $service->calculateDiscount($coupon, $this->subtotal);
            } else {
                $this->appliedCode = '';
            }
        }

        $this->total = $this->subtotal - $this->discount;
    }

    private function completeFreeOrder(Order $order): void
    {
        $orderService = app(OrderService::class);

        $orderService->markAsPaid(
            $order,
            'free',
            'FREE-'.Str::upper($order->order_number),
            orderId: $order->order_number,
        );

        PaymentSuccess::dispatch($order->refresh());
    }
}
