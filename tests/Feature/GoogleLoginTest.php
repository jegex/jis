<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('new user is created with verified email via google login', function () {
    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]));

    $this->get('/auth/google/callback');

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    $user = User::where('email', 'john@example.com')->first();

    expect($user->email_verified_at)->not->toBeNull();
});

test('existing unverified user gets verified when logging in via google', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing User',
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-456',
        'name' => 'Existing User',
        'email' => 'existing@example.com',
    ]));

    $this->get('/auth/google/callback');

    $user->refresh();

    expect($user->email_verified_at)->not->toBeNull();
});

test('existing verified user can login via google', function () {
    $user = User::factory()->create([
        'email' => 'verified@example.com',
        'name' => 'Verified User',
    ]);

    $originalVerifiedAt = $user->email_verified_at;

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-789',
        'name' => 'Verified User',
        'email' => 'verified@example.com',
    ]));

    $this->get('/auth/google/callback');

    $user->refresh();

    expect($user->email_verified_at->format('Y-m-d H:i:s'))->toBe($originalVerifiedAt->format('Y-m-d H:i:s'));
});

test('already linked social account logs in without changes', function () {
    $user = User::factory()->create([
        'email' => 'linked@example.com',
    ]);

    $user->socialAccounts()->create([
        'provider' => 'google',
        'provider_id' => 'google-existing',
    ]);

    $originalVerifiedAt = $user->email_verified_at;

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-existing',
        'name' => 'Linked User',
        'email' => 'linked@example.com',
    ]));

    $this->get('/auth/google/callback');

    $user->refresh();

    expect($user->email_verified_at->format('Y-m-d H:i:s'))->toBe($originalVerifiedAt->format('Y-m-d H:i:s'));
});
