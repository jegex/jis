<?php

declare(strict_types=1);

use App\Console\Commands\ReleasePreordersCommand;
use App\Enums\EmailTemplateType;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Services\DownloadService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function createPreorderProduct(): Product
{
    $currency = Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    return Product::factory()->create([
        'currency_id' => $currency->id,
        'price' => 100000,
        'release_date' => Carbon::now()->addDays(7),
    ]);
}

it('identifies a product as preorder when release_date is in the future', function () {
    $product = createPreorderProduct();

    expect($product->isPreorder())->toBeTrue()
        ->and($product->isReleased())->toBeFalse();
});

it('identifies a product as released when release_date is null', function () {
    $product = Product::factory()->create(['release_date' => null]);

    expect($product->isPreorder())->toBeFalse()
        ->and($product->isReleased())->toBeTrue();
});

it('identifies a product as released when release_date is in the past', function () {
    $product = Product::factory()->create(['release_date' => Carbon::now()->subDay()]);

    expect($product->isPreorder())->toBeFalse()
        ->and($product->isReleased())->toBeTrue();
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

it('releases preorders and sends emails via artisan command', function () {
    Storage::fake('local');
    Mail::fake();

    EmailTemplate::factory()->create([
        'type' => EmailTemplateType::PreorderRelease,
        'subject' => ['id' => 'Produk ready!', 'en' => 'Product ready!'],
        'body' => ['id' => 'Halo {customer_name}', 'en' => 'Hi {customer_name}'],
        'is_active' => true,
    ]);

    $product = createPreorderProduct();
    $product->update(['release_date' => Carbon::now()->subDay()]);

    $order = Order::factory()->forUser()->paid()->create([
        'preorder_released_at' => null,
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->title,
        'price' => $product->price,
        'quantity' => 1,
    ]);

    $exitCode = Artisan::call(ReleasePreordersCommand::class);

    expect($exitCode)->toBe(0);

    $order->refresh();

    expect($order->preorder_released_at)->not->toBeNull();
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
