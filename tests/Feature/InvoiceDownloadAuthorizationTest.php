<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Order;
use App\Models\Product;
use App\Services\InvoicePdfGenerator;
use Illuminate\Support\Facades\Storage;

function createPaidOrderForDownload(): Order
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

    app(InvoicePdfGenerator::class)->generate($order->refresh());

    return $order;
}

it('allows the owner to download their invoice', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();

    $response = $this->actingAs($order->user)
        ->get(route('invoices.download', $order->invoice));

    $response->assertSuccessful();
});

it('allows an admin to download any invoice', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();
    $admin = App\Models\User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get(route('invoices.download', $order->invoice));

    $response->assertSuccessful();
});

it('forbids other users from downloading an invoice', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();
    $stranger = App\Models\User::factory()->create();

    $this->actingAs($stranger)
        ->get(route('invoices.download', $order->invoice))
        ->assertForbidden();
});

it('redirects guests to login', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();

    $this->get(route('invoices.download', $order->invoice))
        ->assertRedirect();
});

it('forbids the owner when the order is not paid', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();
    $order->update(['status' => 'refunded']);

    $this->actingAs($order->user)
        ->get(route('invoices.download', $order->invoice))
        ->assertForbidden();
});

it('returns 404 when the invoice file is missing and the order is not paid', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();
    $order->getFirstMedia(InvoicePdfGenerator::MEDIA_COLLECTION)->delete();
    $order->update(['status' => 'refunded']);
    $admin = App\Models\User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('invoices.download', $order->invoice))
        ->assertNotFound();
});

it('regenerates the pdf on demand when the media is missing for a paid order', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();
    $order->getFirstMedia(InvoicePdfGenerator::MEDIA_COLLECTION)->delete();

    $response = $this->actingAs($order->user)
        ->get(route('invoices.download', $order->invoice));

    $response->assertSuccessful();

    $media = $order->refresh()->getFirstMedia(InvoicePdfGenerator::MEDIA_COLLECTION);

    expect($media)->not->toBeNull()
        ->and(Storage::disk($media->disk)->exists($media->getPathRelativeToRoot()))->toBeTrue();
});

it('serves the invoice inline when requested', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();

    $response = $this->actingAs($order->user)
        ->get(route('invoices.download', ['invoice' => $order->invoice, 'inline' => 1]));

    $response->assertSuccessful()
        ->assertHeader('Content-Disposition', 'inline; filename='.$order->invoice->fileName());
});

it('serves the invoice as an attachment by default', function () {
    Storage::fake('local');
    $order = createPaidOrderForDownload();

    $response = $this->actingAs($order->user)
        ->get(route('invoices.download', $order->invoice));

    $response->assertSuccessful()
        ->assertHeader('Content-Disposition', 'attachment; filename='.$order->invoice->fileName());
});
