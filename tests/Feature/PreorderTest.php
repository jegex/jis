<?php

declare(strict_types=1);

use App\Enums\EmailTemplateType;
use App\Enums\PreorderInterval;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Services\DownloadService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function createPreorderProduct(int $duration = 1, PreorderInterval $interval = PreorderInterval::Week): Product
{
    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    return Product::factory()->preorder($duration, $interval)->create([
        'currency_id' => $currency->id,
        'price' => 100000,
    ]);
}

it('identifies a product as preorder when is_preorder is true', function () {
    $product = createPreorderProduct();

    expect($product->isPreorder())->toBeTrue();
});

it('identifies a product as not preorder when is_preorder is false', function () {
    $product = Product::factory()->create([
        'is_preorder' => false,
    ]);

    expect($product->isPreorder())->toBeFalse();
});

it('computes preorder release date from paid_at', function () {
    $product = createPreorderProduct(2, PreorderInterval::Week);
    $paidAt = now();

    $releaseDate = $product->preorderReleaseDate($paidAt);

    expect($releaseDate->toDateString())->toBe($paidAt->copy()->addWeeks(2)->toDateString());
});

it('returns null preorder release date for non-preorder product', function () {
    $product = Product::factory()->create(['is_preorder' => false]);

    expect($product->preorderReleaseDate())->toBeNull();
});

it('blocks download for preorder products until released', function () {
    Storage::fake('local');
    $product = createPreorderProduct();

    $order = Order::factory()->forUser()->paid()->create();
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    $canDownload = app(DownloadService::class)->canDownload($order, $product);

    expect($canDownload)->toBeFalse();
});

it('allows download for preorder products after release', function () {
    Storage::fake('local');
    $product = createPreorderProduct();

    $order = Order::factory()->forUser()->paid()->create([
        'preorder_released_at' => now(),
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    $canDownload = app(DownloadService::class)->canDownload($order, $product);

    expect($canDownload)->toBeTrue();
});

it('sends preorder release email manually', function () {
    Storage::fake('local');
    Mail::fake();

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::PreorderRelease,
        'subject' => ['id' => 'Produk ready!', 'en' => 'Product ready!'],
        'body' => ['id' => 'Halo {customer_name}', 'en' => 'Hi {customer_name}'],
        'is_active' => true,
    ]);

    $product = createPreorderProduct();

    $order = Order::factory()->forUser()->paid()->create([
        'preorder_released_at' => null,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    app(App\Services\EmailService::class)->sendPreorderRelease($order);

    $order->refresh();

    expect($order->preorder_released_at)->toBeNull();
});

it('includes preorder variables in confirmation email', function () {
    Storage::fake('local');
    Mail::fake();

    $product = createPreorderProduct();

    $order = Order::factory()->forUser()->paid()->create();
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    app(App\Services\InvoicePdfGenerator::class)->generate($order->refresh());

    $html = view('invoice.pdf', app(App\Services\InvoicePdfGenerator::class)->buildViewData($order))->render();

    expect($html)->toContain($product->title);
});
