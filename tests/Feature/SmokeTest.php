<?php

declare(strict_types=1);

use App\Models\User;

test('homepage is 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/')->assertSuccessful();
});

test('login page is 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/login')->assertSuccessful();
});

test('admin panel redirects to login', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('admin panel is 403 for non-admin', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]));

    $this->get('/admin')->assertForbidden();
});

test('admin panel is 200 for admin', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get('/admin')->assertSuccessful();
});
