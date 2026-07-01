<?php

declare(strict_types=1);

namespace App\Payments\Gateways;

use App\Models\Order;
use App\Payments\PaymentGateway;
use App\Payments\PaymentNotification;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatusDto;
use Exception;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

final class MidtransGateway implements PaymentGateway
{
    private array $config = [];

    public function setConfig(array $config): static
    {
        $this->config = $config;

        Config::$serverKey = $config['server_key'] ?? '';
        Config::$isProduction = $config['is_production'] ?? false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        return $this;
    }

    public function getClientKey(): string
    {
        return $this->config['client_key'] ?? '';
    }

    public function getSnapJsUrl(): string
    {
        return ($this->config['is_production'] ?? false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function charge(Order $order, array $params = []): PaymentResult
    {
        $item = $order->items->first();

        if (! $item) {
            return new PaymentResult(
                success: false,
                transactionId: '',
                redirectUrl: '',
                errorMessage: 'Order has no items.',
            );
        }

        $grossAmount = (int) ($params['converted_amount'] ?? $order->total);
        $itemPrice = (int) ($params['converted_item_price'] ?? $params['converted_amount'] ?? $item->price);

        $transactionDetails = [
            'order_id' => 'ORDER-'.$order->id.'-'.time(),
            'gross_amount' => $grossAmount,
        ];

        $customerDetails = [
            'first_name' => $order->user?->name ?? $order->guest_name ?? 'Guest',
            'email' => $order->user?->email ?? $order->guest_email ?? '',
        ];

        $items = [
            [
                'id' => (string) $item->product_id,
                'price' => $itemPrice,
                'quantity' => $item->quantity,
                'name' => $item->product_name,
            ],
        ];

        $discount = $params['converted_discount'] ?? $params['discount'] ?? $order->discount;

        if ((int) $discount > 0) {
            $items[] = [
                'id' => 'discount',
                'price' => -1 * (int) $discount,
                'quantity' => 1,
                'name' => 'Discount',
            ];
        }

        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $items,
            'callbacks' => [
                'finish' => route('payment.finish'),
                'unfinish' => route('payment.unfinish'),
                'error' => route('payment.error'),
            ],
            'notification_url' => route('payment.callback'),
        ];

        $enabledPayments = $this->config['enabled_payments'] ?? [];

        if ($enabledPayments) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        try {
            $response = Snap::createTransaction($payload);

            return new PaymentResult(
                success: true,
                transactionId: $transactionDetails['order_id'],
                redirectUrl: $response->redirect_url,
                snapToken: $response->token,
                rawResponse: $payload,
            );
        } catch (Exception $e) {
            return new PaymentResult(
                success: false,
                transactionId: '',
                redirectUrl: '',
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function callback(array $data): PaymentNotification
    {
        $orderId = $data['order_id'];
        $statusCode = $data['status_code'] ?? '';
        $grossAmount = $data['gross_amount'] ?? '';
        $serverKey = $this->config['server_key'] ?? '';
        $signature = $data['signature_key'] ?? '';

        $calculatedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($calculatedSignature !== $signature) {
            throw new RuntimeException('Invalid Midtrans signature');
        }

        return new PaymentNotification(
            transactionId: $data['transaction_id'],
            transactionStatus: $data['transaction_status'],
            orderId: $orderId,
            rawData: $data,
        );
    }

    public function checkStatus(string $transactionId): PaymentStatusDto
    {
        try {
            $status = Transaction::status($transactionId);

            return new PaymentStatusDto(
                transactionId: $transactionId,
                status: $status->transaction_status,
            );
        } catch (Exception $e) {
            Log::warning('Midtrans status check failed: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
            ]);

            return new PaymentStatusDto(
                transactionId: $transactionId,
                status: 'unknown',
                message: $e->getMessage(),
            );
        }
    }
}
