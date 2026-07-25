<?php

declare(strict_types=1);

use App\Livewire\SetPassword;
use App\Models\Invitation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('renders the set password page with valid token', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Role::findOrCreate('writer', 'web');

    $invitation = Invitation::create([
        'email' => 'newuser@example.com',
        'role' => 'writer',
        'token' => Str::random(64),
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    User::create([
        'email' => 'newuser@example.com',
        'name' => 'newuser',
        'password' => Str::random(32),
        'is_admin' => true,
        'email_verified_at' => now(),
        'locale' => 'en',
        'admin_locale' => 'en',
        'timezone' => 'UTC',
    ]);

    Livewire::test(SetPassword::class, ['token' => $invitation->token])
        ->assertStatus(200);
});

it('returns 404 with invalid token', function () {
    Livewire::test(SetPassword::class, ['token' => 'invalid-token'])
        ->assertStatus(404);
});

it('returns 404 with expired token', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Role::findOrCreate('writer', 'web');

    $invitation = Invitation::create([
        'email' => 'newuser@example.com',
        'role' => 'writer',
        'token' => Str::random(64),
        'invited_by' => $admin->id,
        'expires_at' => now()->subDay(),
    ]);

    User::create([
        'email' => 'newuser@example.com',
        'name' => 'newuser',
        'password' => Str::random(32),
        'is_admin' => true,
        'email_verified_at' => now(),
        'locale' => 'en',
        'admin_locale' => 'en',
        'timezone' => 'UTC',
    ]);

    Livewire::test(SetPassword::class, ['token' => $invitation->token])
        ->assertStatus(404);
});

it('sets password successfully and redirects to admin dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Role::findOrCreate('writer', 'web');

    $invitation = Invitation::create([
        'email' => 'newuser@example.com',
        'role' => 'writer',
        'token' => Str::random(64),
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    $user = User::create([
        'email' => 'newuser@example.com',
        'name' => 'newuser',
        'password' => Str::random(32),
        'is_admin' => true,
        'email_verified_at' => now(),
        'locale' => 'en',
        'admin_locale' => 'en',
        'timezone' => 'UTC',
    ]);

    Livewire::test(SetPassword::class, ['token' => $invitation->token])
        ->set('password', 'password123')
        ->set('passwordConfirmation', 'password123')
        ->call('submit')
        ->assertRedirect(route('filament.admin.pages.dashboard'));

    $user->refresh();
    expect(Hash::check('password123', $user->password))->toBeTrue();
    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});

it('validates password minimum length', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Role::findOrCreate('writer', 'web');

    $invitation = Invitation::create([
        'email' => 'newuser@example.com',
        'role' => 'writer',
        'token' => Str::random(64),
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    User::create([
        'email' => 'newuser@example.com',
        'name' => 'newuser',
        'password' => Str::random(32),
        'is_admin' => true,
        'email_verified_at' => now(),
        'locale' => 'en',
        'admin_locale' => 'en',
        'timezone' => 'UTC',
    ]);

    Livewire::test(SetPassword::class, ['token' => $invitation->token])
        ->set('password', 'short')
        ->set('passwordConfirmation', 'short')
        ->call('submit')
        ->assertHasErrors(['password']);
});

it('validates password confirmation match', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Role::findOrCreate('writer', 'web');

    $invitation = Invitation::create([
        'email' => 'newuser@example.com',
        'role' => 'writer',
        'token' => Str::random(64),
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    User::create([
        'email' => 'newuser@example.com',
        'name' => 'newuser',
        'password' => Str::random(32),
        'is_admin' => true,
        'email_verified_at' => now(),
        'locale' => 'en',
        'admin_locale' => 'en',
        'timezone' => 'UTC',
    ]);

    Livewire::test(SetPassword::class, ['token' => $invitation->token])
        ->set('password', 'password123')
        ->set('passwordConfirmation', 'different')
        ->call('submit')
        ->assertHasErrors(['password']);
});
