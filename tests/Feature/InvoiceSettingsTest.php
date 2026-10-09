<?php

declare(strict_types=1);

use App\Filament\Clusters\Settings\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the default invoice due days when nothing is saved yet', function () {
    $user = User::factory()->admin()->create();
    grantSettingsPermissions($user);

    Livewire::actingAs($user)
        ->test(Settings::class)
        ->assertFormSet([
            'invoice_due_days' => 14,
        ]);
});

it('saves the invoice due days setting', function () {
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
            'invoice_number_format' => 'INV/{YYYY}/{MM}/{SEQ:M}',
            'invoice_number_padding' => 4,
            'invoice_due_days' => 21,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect((int) Setting::get('invoice_due_days'))->toBe(21);
});
