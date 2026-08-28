<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Jobs\GenerateInvoicePdf;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Services\InvoicePdfGenerator;
use Illuminate\Support\Facades\Storage;

function createPaidOrderWithItem(): Order
{
    Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    $order = Order::factory()->paid()->create([
        'subtotal' => 150000,
        'discount' => 0,
        'total' => 150000,
        'locale' => 'id',
    ]);

    $order->items()->create([
        'product_id' => Product::factory()->create(['currency_id' => Currency::query()->whereKey('IDR')->value('id')])->id,
        'product_name' => 'Produk Uji Coba',
        'price' => 150000,
        'quantity' => 1,
    ]);

    return $order;
}

it('generates an invoice pdf with a sequential number and stores it', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    app(InvoicePdfGenerator::class)->generate($order->refresh());

    $invoice = $order->invoice()->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0001')
        ->and($invoice->issued_at?->toDateString())->toBe($order->paid_at->toDateString());

    $media = $order->getFirstMedia(InvoicePdfGenerator::MEDIA_COLLECTION);

    expect($media)->not->toBeNull()
        ->and($media->file_name)->toBe('INV-'.now()->format('Y').'-'.now()->format('m').'-0001.pdf')
        ->and($media->mime_type)->toBe('application/pdf');

    $stored = Storage::disk($media->disk)->get($media->getPathRelativeToRoot());

    expect($stored)->not->toBeNull()
        ->and(mb_substr((string) $stored, 0, 4))->toBe('%PDF');
});

it('is idempotent when generating the same invoice twice', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);

    $first = $generator->generate($order);
    $second = $generator->generate($order);

    expect($second->id)->toBe($first->id)
        ->and(Invoice::count())->toBe(1)
        ->and($order->getMedia(InvoicePdfGenerator::MEDIA_COLLECTION)->count())->toBe(1);
});

it('skips generation for orders that are not paid', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->update(['status' => OrderStatus::AwaitingPayment, 'paid_at' => null]);

    (new GenerateInvoicePdf($order))->handle(app(InvoicePdfGenerator::class));

    expect($order->invoice()->exists())->toBeFalse()
        ->and(Invoice::count())->toBe(0);
});

it('renders localized invoice content in indonesian', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    app()->setLocale('id');
    $html = view('invoice.pdf', $generator->buildViewData($order))->render();
    app()->setLocale(config('app.fallback_locale'));

    expect($html)->toContain('FAKTUR')
        ->toContain($order->invoice->number)
        ->toContain('Produk Uji Coba')
        ->toContain('Pelanggan')
        ->toContain('Rp 150.000');
});

it('renders localized invoice content in english', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->update(['locale' => 'en']);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    app()->setLocale('en');
    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('INVOICE')
        ->toContain('Customer');
});

it('embeds the configured logo as a data uri', function () {
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('public')->put('invoice-logo.png', "\x89PNG\r\n\x1a\n");
    App\Models\Setting::set('invoice_logo', 'invoice-logo.png');

    $order = createPaidOrderWithItem();
    app(InvoicePdfGenerator::class)->generate($order->refresh());

    $html = view('invoice.pdf', app(InvoicePdfGenerator::class)->buildViewData($order))->render();

    expect($html)->toContain('data:image/png;base64,');
});

it('falls back to the company name when no logo is configured', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    app(InvoicePdfGenerator::class)->generate($order->refresh());

    $html = view('invoice.pdf', app(InvoicePdfGenerator::class)->buildViewData($order))->render();

    expect($html)->not->toContain('data:')
        ->toContain((string) setting('invoice_company_name', config('app.name')));
});

it('shows order numbers and placeholder customer contacts', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    app(InvoicePdfGenerator::class)->generate($order->refresh());

    $html = view('invoice.pdf', app(InvoicePdfGenerator::class)->buildViewData($order))->render();

    expect($html)->toContain($order->order_number)
        ->toContain('<td>-</td>');
});

it('includes payment information on the invoice', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $order->payments()->create([
        'gateway' => 'midtrans',
        'gateway_transaction_id' => 'tx-invoice-123',
        'gateway_status' => 'success',
        'status' => 'success',
        'currency_code' => 'IDR',
        'amount' => 150000,
        'paid_at' => now(),
    ]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    app()->setLocale('id');
    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('tx-invoice-123')
        ->toContain('Informasi pembayaran')
        ->not->toContain('Metode pembayaran')
        ->not->toContain('Payment method');
});

it('dispatches a chained invoice and email jobs on payment success', function () {
    Storage::fake('local');
    Bus::fake();

    $order = createPaidOrderWithItem();

    (new App\Listeners\TriggerOrderEmails)->handle(new App\Events\PaymentSuccess($order));

    Bus::assertChained([
        GenerateInvoicePdf::class,
        App\Jobs\SendOrderEmail::class,
        App\Jobs\SendOrderEmail::class,
    ]);
});
