<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Services\InvoicePdfGenerator;
use Illuminate\Support\Facades\Storage;

it('regenerates the invoice pdf when the invoice is updated', function () {
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

    $originalMedia = $generator->storedPdf($order->refresh());

    expect($originalMedia)->not->toBeNull();

    $invoice->update(['number' => 'INV-EDIT-9999']);

    $regeneratedMedia = $generator->storedPdf($order->refresh());

    expect($regeneratedMedia)->not->toBeNull()
        ->and($regeneratedMedia->id)->not->toBe($originalMedia->id)
        ->and($regeneratedMedia->file_name)->toBe($invoice->fresh()->fileName())
        ->and($order->getMedia(InvoicePdfGenerator::MEDIA_COLLECTION))->toHaveCount(1)
        ->and(Storage::disk('local')->exists($regeneratedMedia->getPathRelativeToRoot()))->toBeTrue();
});
