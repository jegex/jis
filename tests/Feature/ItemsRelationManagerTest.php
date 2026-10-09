<?php

declare(strict_types=1);

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lets an admin set an item description', function () {
    $currency = ensureIdrCurrency();

    $order = Order::factory()->create();

    $item = $order->items()->create([
        'product_id' => Product::factory()->create(['currency_id' => $currency->id])->id,
        'product_name' => 'Drawing Set',
        'price' => 150000,
        'quantity' => 1,
    ]);

    $user = User::factory()->admin()->create();
    grantOrderPermissions($user);

    Livewire::actingAs($user)
        ->test(ItemsRelationManager::class, [
            'ownerRecord' => $order,
            'pageClass' => EditOrder::class,
        ])
        ->callAction(
            TestAction::make(EditAction::class)->table($item),
            ['description' => 'Hull Construction Drawings'],
        )
        ->assertHasNoActionErrors();

    expect($item->refresh()->description)->toBe('Hull Construction Drawings');
});
