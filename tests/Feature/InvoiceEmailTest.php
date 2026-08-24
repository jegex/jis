<?php

declare(strict_types=1);

use App\Enums\EmailTemplateType;
use App\Enums\OrderStatus;
use App\Mail\OrderConfirmationMail;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Services\EmailService;
use App\Services\InvoicePdfGenerator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function createOrderConfirmationTemplate(): EmailTemplate
{
    return EmailTemplate::factory()->create([
        'type' => EmailTemplateType::OrderConfirmation,
        'subject' => ['id' => 'Konfirmasi {order_number}', 'en' => 'Confirmation {order_number}'],
        'body' => [
            'id' => 'Halo {customer_name}, invoice {invoice_number} untuk pesanan {order_number}.',
            'en' => 'Hi {customer_name}, invoice {invoice_number} for order {order_number}.',
        ],
        'variables' => ['customer_name', 'order_number', 'invoice_number'],
        'is_active' => true,
    ]);
}

function createGuestPaidOrderForEmail(): Order
{
    Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    $order = Order::factory()->asGuest()->paid()->create([
        'locale' => 'id',
    ]);

    $order->items()->create([
        'product_id' => App\Models\Product::factory()->create(['currency_id' => Currency::query()->whereKey('IDR')->value('id')])->id,
        'product_name' => 'Produk Uji Coba',
        'price' => 100000,
        'quantity' => 1,
    ]);

    return $order;
}

it('attaches the invoice pdf to the order confirmation email', function () {
    Storage::fake('local');
    Mail::fake();
    createOrderConfirmationTemplate();

    $order = createGuestPaidOrderForEmail();
    app(InvoicePdfGenerator::class)->generate($order->refresh());
    $invoice = $order->invoice;

    app(EmailService::class)->sendOrderConfirmation($order);

    Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($order, $invoice) {
        $mail->build();

        return str_contains((string) $mail->subject, $order->order_number)
            && $mail->hasTo($order->guest_email)
            && collect($mail->rawAttachments)->contains(fn ($a) => $a['name'] === $invoice->fileName());
    });
});

it('replaces the invoice number variable in the email body', function () {
    Storage::fake('local');
    Mail::fake();
    createOrderConfirmationTemplate();

    $order = createGuestPaidOrderForEmail();
    app(InvoicePdfGenerator::class)->generate($order->refresh());
    $invoice = $order->invoice;

    app(EmailService::class)->sendOrderConfirmation($order);

    Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($invoice) {
        $mail->build();

        return str_contains((string) $mail->viewData['body'], $invoice->number)
            && ! str_contains((string) $mail->viewData['body'], '{invoice_number}');
    });
});

it('sends without an attachment when the order is not paid', function () {
    Storage::fake('local');
    Mail::fake();
    createOrderConfirmationTemplate();

    $order = createGuestPaidOrderForEmail();
    $order->update(['status' => OrderStatus::AwaitingPayment]);

    app(EmailService::class)->sendOrderConfirmation($order);

    Mail::assertSent(OrderConfirmationMail::class, fn ($mail) => count($mail->attachments) + count($mail->rawAttachments) === 0);
});
