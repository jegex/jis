<?php

declare(strict_types=1);

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('stores the quotation and purchase order references on an order', function () {
    ensureIdrCurrency();

    $order = Order::factory()->create();

    $user = User::factory()->admin()->create();

    grantOrderPermissions($user);

    Livewire::actingAs($user)
        ->test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'quotation_number' => 'QT-2026-042',
            'po_number' => 'PO-2026-100',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $order->refresh();

    expect($order->quotation_number)->toBe('QT-2026-042')
        ->and($order->po_number)->toBe('PO-2026-100');
});

it('stores the down payment percentage on an order', function () {
    ensureIdrCurrency();

    $order = Order::factory()->create();

    $user = User::factory()->admin()->create();

    grantOrderPermissions($user);

    Livewire::actingAs($user)
        ->test(EditOrder::class, ['record' => $order->id])
        ->fillForm([
            'down_payment_percent' => 50,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->down_payment_percent)->toBe(50.0);
});
