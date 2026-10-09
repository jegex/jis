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

    expect($html)->toContain('<h1 class="document-title">FAKTUR</h1>')
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

    expect($html)->toContain('<h1 class="document-title">INVOICE</h1>')
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

it('shows the order item description on the invoice', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->items()->first()->update(['description' => "Hull Construction Drawings\nMachinery Drawings"]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Hull Construction Drawings')
        ->toContain('Machinery Drawings');
});

it('falls back to the product name when an item has no description', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Produk Uji Coba')
        ->not->toContain('Hull Construction Drawings');
});

it('falls back to the product name when the description is blank whitespace', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->items()->first()->update(['description' => '   ']);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Produk Uji Coba');
});

it('shows quotation, purchase order references and the due date', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->update([
        'quotation_number' => 'QT-2026-001',
        'po_number' => 'PO-2026-777',
    ]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    $dueDate = $order->invoice->issued_at->copy()->addDays(14)->translatedFormat('j F Y');

    expect($html)->toContain('QT-2026-001')
        ->toContain('PO-2026-777')
        ->toContain($dueDate)
        ->toContain(__('Quotation No.'))
        ->toContain(__('PO No.'));
});

it('derives the due date from the configured invoice due days', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_due_days', 30);
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    $dueDate = $order->invoice->issued_at->copy()->addDays(30)->translatedFormat('j F Y');

    expect($html)->toContain($dueDate);
});

it('renders a non-VAT line and bills the full base when no down payment is set', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Non-VAT')
        ->toContain(__('Total Amount'))
        ->toContain('Rp 150.000')
        ->not->toContain('DP ');
});

it('bills the down payment share when a down payment percentage is set', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->update(['down_payment_percent' => 50]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('DP 50%')
        ->toContain('Rp 75.000')
        ->toContain('Non-VAT');
});

it('applies VAT to the discounted base and totals the down payment plus tax', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_tax_percent', 11);
    $order = createPaidOrderWithItem();
    $order->update(['down_payment_percent' => 50, 'discount' => 50000]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('DP 50%')
        ->toContain('PPN 11%')
        ->toContain('Rp 61.000')
        ->not->toContain('Non-VAT');
});

it('bills the full base plus VAT when no down payment is set', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_tax_percent', 11);
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $totals = $generator->buildViewData($order)['totals'];

    expect($totals['downPaymentPercent'])->toBeNull()
        ->and($totals['downPayment'])->toBe(150000.0)
        ->and($totals['tax'])->toBe(16500.0)
        ->and($totals['total'])->toBe(166500.0);
});

it('treats a zero down payment percentage as no down payment', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_tax_percent', 11);
    $order = createPaidOrderWithItem();
    $order->update(['down_payment_percent' => 0]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $totals = $generator->buildViewData($order)['totals'];

    expect($totals['downPaymentPercent'])->toBeNull()
        ->and($totals['downPayment'])->toBe(150000.0)
        ->and($totals['total'])->toBe(166500.0);
});

it('computes base, down payment, tax and total at the view-data seam', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_tax_percent', 11);
    $order = createPaidOrderWithItem();
    $order->update(['down_payment_percent' => 50, 'discount' => 50000]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    expect($generator->buildViewData($order)['totals'])->toMatchArray([
        'subtotal' => 150000.0,
        'discount' => 50000.0,
        'base' => 100000.0,
        'downPaymentPercent' => 50.0,
        'downPayment' => 50000.0,
        'taxPercent' => 11.0,
        'tax' => 11000.0,
        'total' => 61000.0,
    ]);
});

it('rounds the down payment and tax to whole currency units', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_tax_percent', 11);
    $order = createPaidOrderWithItem();
    $order->update(['subtotal' => 100001, 'discount' => 0, 'down_payment_percent' => 33]);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $totals = $generator->buildViewData($order)['totals'];

    expect($totals['downPayment'])->toBe(33000.0)
        ->and($totals['tax'])->toBe(11000.0)
        ->and($totals['total'])->toBe(44000.0);
});

it('renders the total amount in words in the order locale', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    app()->setLocale('id');
    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Seratus lima puluh ribu Indonesian Rupiah (IDR)')
        ->toContain(__('Total Amount in Words'));
});

it('renders the total amount in words in english', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();
    $order->update(['locale' => 'en']);

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    app()->setLocale('en');
    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('One Hundred Fifty Thousand Indonesian Rupiah (IDR)');
});

it('shows the configured payment terms in the order locale', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_payment_terms', [
        'en' => '1. 50% down payment on order.',
        'id' => '1. DP 50% saat pemesanan.',
    ]);
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    app()->setLocale('id');
    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('DP 50% saat pemesanan')
        ->toContain(__('Payment Terms'));
});

it('hides the payment terms block when none are configured', function () {
    Storage::fake('local');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->not->toContain(__('Payment Terms'));
});

it('falls back to the first locale when the order locale is not configured', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_payment_terms', [
        'en' => 'Pay within 14 days.',
    ]);
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Pay within 14 days.');
});

it('embeds the configured banner and hides it when unset', function () {
    Storage::fake('local');
    Storage::fake('public');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $withoutBanner = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($withoutBanner)->not->toContain('class="banner"');

    Storage::disk('public')->put('invoice-banner.png', "\x89PNG\r\n\x1a\n");
    App\Models\Setting::set('invoice_banner', 'invoice-banner.png');

    $withBanner = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($withBanner)->toContain('class="banner"')
        ->toContain('data:image/png;base64,');
});

it('shows the signature block and company footer', function () {
    Storage::fake('local');
    App\Models\Setting::set('invoice_company_name', 'PT Pembuktian');
    $order = createPaidOrderWithItem();

    $generator = app(InvoicePdfGenerator::class);
    $generator->generate($order->refresh());

    $html = view('invoice.pdf', $generator->buildViewData($order))->render();

    expect($html)->toContain('Approved by')
        ->toContain('Name &amp; Signature')
        ->toContain('PT Pembuktian');
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
