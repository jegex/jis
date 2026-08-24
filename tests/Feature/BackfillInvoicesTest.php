<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

function createPaidOrderWithoutInvoice(): Order
{
    Currency::query()->firstOrCreate(['code' => 'IDR'], [
        'name' => 'Indonesian Rupiah',
        'symbol' => 'Rp',
        'exchange_rate' => 1,
        'decimal_place' => 0,
        'is_default' => true,
    ]);

    $order = Order::factory()->asGuest()->paid()->create();

    $order->items()->create([
        'product_id' => Product::factory()->create(['currency_id' => Currency::query()->whereKey('IDR')->value('id')])->id,
        'product_name' => 'Produk Uji Coba',
        'price' => 100000,
        'quantity' => 1,
    ]);

    return $order;
}

it('generates invoices for paid orders without one', function () {
    Storage::fake('local');
    createPaidOrderWithoutInvoice();

    expect(Invoice::count())->toBe(0);

    $this->artisan('invoices:backfill')->assertSuccessful();

    expect(Invoice::count())->toBe(1);
});

it('does nothing with the dry-run flag', function () {
    Storage::fake('local');
    createPaidOrderWithoutInvoice();

    $this->artisan('invoices:backfill', ['--dry-run' => true])->assertSuccessful();

    expect(Invoice::count())->toBe(0);
});

it('is idempotent when run twice', function () {
    Storage::fake('local');
    createPaidOrderWithoutInvoice();

    $this->artisan('invoices:backfill')->assertSuccessful();
    $this->artisan('invoices:backfill')->assertSuccessful();

    expect(Invoice::count())->toBe(1);
});

it('reports success when there is nothing to backfill', function () {
    $this->artisan('invoices:backfill')
        ->expectsOutputToContain('Nothing to do.')
        ->assertSuccessful();

    expect(Invoice::count())->toBe(0);
});
