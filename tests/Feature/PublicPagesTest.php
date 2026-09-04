<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;

// ─── English pages (no prefix) ──────────────────────────────

test('english homepage returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/')->assertSuccessful();
});

test('english login page returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/login')->assertSuccessful();
});

test('english register page returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/register')->assertSuccessful();
});

test('english forgot password page returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/forgot-password')->assertSuccessful();
});

test('english blog index returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/blog')->assertSuccessful();
});

test('english products index returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/products')->assertSuccessful();
});

test('english payment success page returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/payment/success')->assertSuccessful();
});

test('english payment pending page returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/payment/pending')->assertSuccessful();
});

test('english payment error page returns 200', function () {
    refreshApplicationWithLocale('en');

    $this->get('/payment/error')->assertSuccessful();
});

test('english payment unfinish page redirects', function () {
    refreshApplicationWithLocale('en');

    $this->get('/payment/unfinish')->assertStatus(302);
});

test('english payment finish page redirects', function () {
    refreshApplicationWithLocale('en');

    $this->get('/payment/finish')->assertStatus(302);
});

test('google auth redirects', function () {
    refreshApplicationWithLocale('en');

    $this->get('/auth/google')->assertStatus(302);
});

test('currency switch redirects', function () {
    refreshApplicationWithLocale('en');
    Currency::factory()->create(['code' => 'USD']);

    $this->get('/currency/USD')->assertStatus(302);
});

test('english blog detail returns 200 with published post', function () {
    refreshApplicationWithLocale('en');
    Post::factory()->create();

    $this->get('/blog/'.Post::first()->slug)->assertSuccessful();
});

test('english product detail returns 200 with published product', function () {
    refreshApplicationWithLocale('en');
    Product::factory()->create();

    $this->get('/products/'.Product::first()->slug)->assertSuccessful();
});

test('english custom page returns 200 with published page', function () {
    refreshApplicationWithLocale('en');
    Page::factory()->create();

    $this->get('/pages/'.Page::first()->slug)->assertSuccessful();
});

test('unauthenticated customer pages redirect to login', function () {
    refreshApplicationWithLocale('en');

    $this->get('/customer/dashboard')->assertStatus(302);
    $this->get('/customer/downloads')->assertStatus(302);
    $this->get('/customer/profile')->assertStatus(302);
});

test('authenticated customer pages return 200', function () {
    refreshApplicationWithLocale('en');
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/customer/dashboard')->assertSuccessful();
    $this->get('/customer/downloads')->assertSuccessful();
    $this->get('/customer/profile')->assertSuccessful();
});

// ─── Indonesian pages (prefixed with /id) ───────────────────

test('indonesian homepage returns 200', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id')->assertSuccessful();
});

test('indonesian blog index returns 200', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/artikel')->assertSuccessful();
});

test('indonesian products index returns 200', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/produk')->assertSuccessful();
});

test('indonesian payment success returns 200', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/pembayaran/sukses')->assertSuccessful();
});

test('indonesian payment pending returns 200', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/pembayaran/menunggu')->assertSuccessful();
});

test('indonesian payment error returns 200', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/pembayaran/gagal')->assertSuccessful();
});

test('indonesian payment unfinish redirects', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/pembayaran/batal')->assertStatus(302);
});

test('indonesian payment finish redirects', function () {
    refreshApplicationWithLocale('id');

    $this->get('/id/pembayaran/selesai')->assertStatus(302);
});

test('indonesian blog detail returns 200 with published post', function () {
    refreshApplicationWithLocale('id');
    Post::factory()->create();

    $this->get('/id/artikel/'.Post::first()->slug)->assertSuccessful();
});

test('indonesian product detail returns 200 with published product', function () {
    refreshApplicationWithLocale('id');
    Product::factory()->create();

    $this->get('/id/produk/'.Product::first()->slug)->assertSuccessful();
});

test('indonesian custom page returns 200 with published page', function () {
    refreshApplicationWithLocale('id');
    Page::factory()->create();

    $this->get('/id/halaman/'.Page::first()->slug)->assertSuccessful();
});

test('indonesian authenticated customer pages return 200', function () {
    refreshApplicationWithLocale('id');
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/id/pelanggan/dashboard')->assertSuccessful();
    $this->get('/id/pelanggan/profil')->assertSuccessful();
    $this->get('/id/pelanggan/unduhan')->assertSuccessful();
});
