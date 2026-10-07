<?php

declare(strict_types=1);

use App\Filament\Clusters\Settings\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function grantSettingsPagePermissions(User $user): void
{
    $permissions = ['View:Settings', 'Update:Settings'];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo($permissions);
}

it('shows the default order number format when nothing is saved yet', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPagePermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->assertFormSet([
            'order_number_format' => 'ORD-{YYYY}{MM}-{SEQ:M}',
            'order_number_padding' => 4,
        ]);
});

it('previews the next order numbers in the form', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPagePermissions($user);

    $suffix = '-0001, ORD-'.now()->format('Ym').'-0002, ORD-'.now()->format('Ym').'-0003';

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->assertSee('Preview — next: ORD-'.now()->format('Ym').$suffix);
});

it('saves the order number format settings', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPagePermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->fillForm([
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'products_per_page' => 9,
            'posts_per_page' => 9,
            'supported_locales' => ['en', 'id'],
            'default_locale' => 'en',
            'order_number_format' => 'SHOP/{YYYY}/{SEQ:Y}',
            'order_number_padding' => 5,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('order_number_format'))->toBe('SHOP/{YYYY}/{SEQ:Y}')
        ->and((int) Setting::get('order_number_padding'))->toBe(5);
});

it('rejects an invalid order number format on save', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPagePermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->fillForm([
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'products_per_page' => 9,
            'posts_per_page' => 9,
            'supported_locales' => ['en', 'id'],
            'default_locale' => 'en',
            'order_number_format' => 'ORD-{UNKNOWN}',
            'order_number_padding' => 4,
        ])
        ->call('save')
        ->assertHasFormErrors(['order_number_format']);

    expect(Setting::has('order_number_format'))->toBeFalse();
});
