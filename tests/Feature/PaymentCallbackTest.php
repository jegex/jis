<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Events\PaymentSuccess;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->serverKey = 'test-server-key-abc123';
    config(['payment.types.midtrans.server_key' => $this->serverKey]);
});

function makeMidtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
{
    return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
}

it('updates order status to paid on settlement callback', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::AwaitingPayment,
        'total' => 150000,
    ]);

    $midtransOrderId = 'ORDER-'.$order->id.'-'.time();

    Payment::create([
        'order_id' => $order->id,
        'gateway' => 'midtrans',
        'gateway_transaction_id' => $midtransOrderId,
        'gateway_status' => 'pending',
        'status' => 'pending',
        'currency_code' => 'IDR',
        'amount' => 150000,
    ]);

    Event::fake([PaymentSuccess::class]);

    $statusCode = '200';
    $grossAmount = '150000';
    $signature = makeMidtransSignature($midtransOrderId, $statusCode, $grossAmount, $this->serverKey);

    $payload = [
        'order_id' => $midtransOrderId,
        'transaction_id' => 'tx-123456',
        'transaction_status' => 'settlement',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'fraud_status' => 'accept',
        'payment_type' => 'credit_card',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->paid_at)->not->toBeNull();

    Event::assertDispatched(PaymentSuccess::class);
});

it('updates order status to paid on capture callback with accept fraud', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::AwaitingPayment,
        'total' => 200000,
    ]);

    $midtransOrderId = 'ORDER-'.$order->id.'-'.time();

    Payment::create([
        'order_id' => $order->id,
        'gateway' => 'midtrans',
        'gateway_transaction_id' => $midtransOrderId,
        'gateway_status' => 'pending',
        'status' => 'pending',
        'currency_code' => 'IDR',
        'amount' => 200000,
    ]);

    Event::fake([PaymentSuccess::class]);

    $statusCode = '200';
    $grossAmount = '200000';
    $signature = makeMidtransSignature($midtransOrderId, $statusCode, $grossAmount, $this->serverKey);

    $payload = [
        'order_id' => $midtransOrderId,
        'transaction_id' => 'tx-789012',
        'transaction_status' => 'capture',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'fraud_status' => 'accept',
        'payment_type' => 'credit_card',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid);
});

it('does not mark order as paid on capture with challenge fraud', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::AwaitingPayment,
        'total' => 100000,
    ]);

    $midtransOrderId = 'ORDER-'.$order->id.'-'.time();

    Payment::create([
        'order_id' => $order->id,
        'gateway' => 'midtrans',
        'gateway_transaction_id' => $midtransOrderId,
        'gateway_status' => 'pending',
        'status' => 'pending',
        'currency_code' => 'IDR',
        'amount' => 100000,
    ]);

    $statusCode = '200';
    $grossAmount = '100000';
    $signature = makeMidtransSignature($midtransOrderId, $statusCode, $grossAmount, $this->serverKey);

    $payload = [
        'order_id' => $midtransOrderId,
        'transaction_id' => 'tx-345678',
        'transaction_status' => 'capture',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'fraud_status' => 'challenge',
        'payment_type' => 'credit_card',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::AwaitingPayment);
});

it('marks order as failed on deny callback', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::AwaitingPayment,
        'total' => 50000,
    ]);

    $midtransOrderId = 'ORDER-'.$order->id.'-'.time();

    Payment::create([
        'order_id' => $order->id,
        'gateway' => 'midtrans',
        'gateway_transaction_id' => $midtransOrderId,
        'gateway_status' => 'pending',
        'status' => 'pending',
        'currency_code' => 'IDR',
        'amount' => 50000,
    ]);

    $statusCode = '200';
    $grossAmount = '50000';
    $signature = makeMidtransSignature($midtransOrderId, $statusCode, $grossAmount, $this->serverKey);

    $payload = [
        'order_id' => $midtransOrderId,
        'transaction_id' => 'tx-999999',
        'transaction_status' => 'deny',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'payment_type' => 'bca_va',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Failed);
});

it('does not overwrite paid status with deny callback', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total' => 75000,
    ]);

    $midtransOrderId = 'ORDER-'.$order->id.'-'.time();

    Payment::create([
        'order_id' => $order->id,
        'gateway' => 'midtrans',
        'gateway_transaction_id' => $midtransOrderId,
        'gateway_status' => 'success',
        'status' => 'success',
        'currency_code' => 'IDR',
        'amount' => 75000,
    ]);

    $statusCode = '200';
    $grossAmount = '75000';
    $signature = makeMidtransSignature($midtransOrderId, $statusCode, $grossAmount, $this->serverKey);

    $payload = [
        'order_id' => $midtransOrderId,
        'transaction_id' => 'tx-late-deny',
        'transaction_status' => 'deny',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'payment_type' => 'bca_va',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid);
});

it('returns 200 for unknown order_id', function () {
    $statusCode = '200';
    $grossAmount = '100000';
    $fakeOrderId = 'ORDER-99999-0000000000';
    $signature = makeMidtransSignature($fakeOrderId, $statusCode, $grossAmount, $this->serverKey);

    $payload = [
        'order_id' => $fakeOrderId,
        'transaction_id' => 'tx-unknown',
        'transaction_status' => 'settlement',
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();
});

it('returns 200 on invalid signature', function () {
    $payload = [
        'order_id' => 'ORDER-1-0000000000',
        'transaction_id' => 'tx-invalid',
        'transaction_status' => 'settlement',
        'status_code' => '200',
        'gross_amount' => '100000',
        'signature_key' => 'invalid-signature',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();
});

it('returns 200 for non-ORDER prefixed order_id', function () {
    $payload = [
        'order_id' => 'SOMEOTHER-123',
        'transaction_id' => 'tx-other',
        'transaction_status' => 'settlement',
        'status_code' => '200',
        'gross_amount' => '100000',
        'signature_key' => 'some-sig',
    ];

    $response = $this->postJson(route('payment.callback'), $payload);

    $response->assertOk();
});
