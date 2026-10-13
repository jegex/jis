<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Services\InvoicePdfGenerator;
use Illuminate\Support\Facades\Storage;

it('deletes the stored invoice pdf when the invoice is deleted', function () {
    Storage::fake('local');

    $currency = ensureIdrCurrency();
    $order = Order::factory()->forUser()->paid()->create(['locale' => 'id']);

    $order->items()->create([
        'product_id' => Product::factory()->create(['currency_id' => $currency->id])->id,
        'product_name' => 'Produk Uji Coba',
        'price' => 100000,
        'quantity' => 1,
    ]);

    $generator = app(InvoicePdfGenerator::class);
    $invoice = $generator->generate($order->refresh());

    $media = $generator->storedPdf($order->refresh());

    expect($media)->not->toBeNull();

    $path = $media->getPathRelativeToRoot();

    expect(Storage::disk('local')->exists($path))->toBeTrue();

    $invoice->delete();

    expect($order->refresh()->getMedia(InvoicePdfGenerator::MEDIA_COLLECTION))->toHaveCount(0)
        ->and(Storage::disk('local')->exists($path))->toBeFalse()
        ->and(Invoice::query()->whereKey($invoice->id)->exists())->toBeFalse();
});
