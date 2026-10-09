<?php

declare(strict_types=1);

use App\Filament\Clusters\Settings\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the default invoice number format when nothing is saved yet', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->assertFormSet([
            'invoice_number_format' => 'INV/{YYYY}/{MM}/{SEQ:M}',
            'invoice_number_padding' => 4,
        ]);
});

it('previews the next invoice numbers in the form', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPermissions($user);

    $prefix = 'INV/'.now()->format('Y').'/'.now()->format('m').'/';

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->assertSee('Preview — next: '.$prefix.'0001, '.$prefix.'0002, '.$prefix.'0003');
});

it('saves the invoice number format settings', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->fillForm([
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'products_per_page' => 9,
            'posts_per_page' => 9,
            'supported_locales' => ['en', 'id'],
            'default_locale' => 'en',
            'invoice_number_format' => 'INV-{YYYY}-{SEQ:Y}',
            'invoice_number_padding' => 5,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('invoice_number_format'))->toBe('INV-{YYYY}-{SEQ:Y}')
        ->and((int) Setting::get('invoice_number_padding'))->toBe(5);
});

it('rejects an invalid invoice number format on save', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->fillForm([
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'products_per_page' => 9,
            'posts_per_page' => 9,
            'supported_locales' => ['en', 'id'],
            'default_locale' => 'en',
            'invoice_number_format' => 'INV-{UNKNOWN}',
            'invoice_number_padding' => 4,
        ])
        ->call('save')
        ->assertHasFormErrors(['invoice_number_format']);

    expect(Setting::has('invoice_number_format'))->toBeFalse();
});
