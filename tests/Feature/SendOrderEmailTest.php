<?php

declare(strict_types=1);

use App\Enums\EmailTemplateType;
use App\Events\PaymentSuccess;
use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendOrderEmail;
use App\Listeners\TriggerOrderEmails;
use App\Mail\OrderConfirmationMail;
use App\Models\Currency;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Services\EmailService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function createPaidOrderForResend(): Order
{
    Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    $order = Order::factory()->forUser()->paid()->create([
        'locale' => 'id',
    ]);

    $order->items()->create([
        'product_id' => Product::factory()->create(['currency_id' => Currency::query()->whereKey('IDR')->value('id')])->id,
        'product_name' => 'Produk Uji Coba',
        'price' => 100000,
        'quantity' => 1,
    ]);

    return $order;
}

it('can dispatch the same order email multiple times', function () {
    Bus::fake();
    $order = createPaidOrderForResend();

    SendOrderEmail::dispatch($order, EmailTemplateType::OrderConfirmation);
    SendOrderEmail::dispatch($order, EmailTemplateType::OrderConfirmation);
    SendOrderEmail::dispatch($order, EmailTemplateType::OrderConfirmation);

    Bus::assertDispatched(SendOrderEmail::class, 3);
});

it('ignores repeated payment success events within the lock window', function () {
    Storage::fake('local');
    Bus::fake();
    $order = createPaidOrderForResend();

    $listener = new TriggerOrderEmails;

    $listener->handle(new PaymentSuccess($order));
    $listener->handle(new PaymentSuccess($order));

    Bus::assertChained([
        GenerateInvoicePdf::class,
        SendOrderEmail::class,
        SendOrderEmail::class,
    ]);
});

it('allows a new chain after the lock is released', function () {
    Storage::fake('local');
    Bus::fake();
    $order = createPaidOrderForResend();

    $listener = new TriggerOrderEmails;

    $listener->handle(new PaymentSuccess($order));

    Cache::lock('emails-chain:'.$order->getKey())->forceRelease();

    $listener->handle(new PaymentSuccess($order));

    Bus::assertChained([
        GenerateInvoicePdf::class,
        SendOrderEmail::class,
        SendOrderEmail::class,
    ]);
});

it('logs every sent order confirmation email', function () {
    Storage::fake('local');
    Mail::fake();

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::OrderConfirmation,
        'subject' => ['id' => 'Konfirmasi {order_number}', 'en' => 'Confirmation {order_number}'],
        'body' => ['id' => 'Halo {customer_name}', 'en' => 'Hi {customer_name}'],
        'is_active' => true,
    ]);

    $order = createPaidOrderForResend();
    app(EmailService::class)->sendOrderConfirmation($order);

    Mail::assertSent(OrderConfirmationMail::class);

    $log = EmailLog::query()
        ->where('order_id', $order->id)
        ->where('type', EmailTemplateType::OrderConfirmation->value)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->recipient)->toBe($order->user->email)
        ->and((string) $log->subject)->toContain($order->order_number)
        ->and($log->sent_at)->not->toBeNull();
});
