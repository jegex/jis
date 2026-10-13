<?php

declare(strict_types=1);

use App\Filament\Resources\Invoices\Pages\ManageInvoices;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoicePdfGenerator;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('shows a view pdf action that previews the invoice inline', function () {
    Storage::fake('local');

    $currency = ensureIdrCurrency();
    $order = Order::factory()->forUser()->paid()->create(['locale' => 'id']);

    $order->items()->create([
        'product_id' => Product::factory()->create(['currency_id' => $currency->id])->id,
        'product_name' => 'Produk Uji Coba',
        'price' => 100000,
        'quantity' => 1,
    ]);

    $invoice = app(InvoicePdfGenerator::class)->generate($order->refresh());

    $user = User::factory()->admin()->create();
    grantPermissions($user, ['ViewAny:Invoice']);

    $component = Livewire::actingAs($user)->test(ManageInvoices::class);

    $component->assertActionVisible(TestAction::make('view_pdf')->table($invoice));

    $component->mountAction(TestAction::make('view_pdf')->table($invoice));

    $component->assertMountedActionModalSee(
        route('invoices.download', ['invoice' => $invoice, 'inline' => 1])
    );
});
