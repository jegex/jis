<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── Default locale (English, no prefix) ──────────────────

it('returns 200 for English homepage', function () {
    refreshApplicationWithLocale('en');

    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns 200 for English products page', function () {
    refreshApplicationWithLocale('en');

    $response = $this->get('/products');

    $response->assertStatus(200);
});

it('returns 200 for English blog page', function () {
    refreshApplicationWithLocale('en');

    $response = $this->get('/blog');

    $response->assertStatus(200);
});

it('returns 200 for English product detail page', function () {
    refreshApplicationWithLocale('en');

    $product = Product::factory()->create();

    $response = $this->get('/products/'.$product->slug);

    $response->assertStatus(200);
});

it('returns 200 for English blog detail page', function () {
    refreshApplicationWithLocale('en');

    $post = Post::factory()->create();

    $response = $this->get('/blog/'.$post->slug);

    $response->assertStatus(200);
});

// ─── Indonesian localized pages ────────────────────────────

it('returns 200 for Indonesian homepage', function () {
    refreshApplicationWithLocale('id');

    $response = $this->get('/id');

    $response->assertStatus(200);
});

it('returns 200 for Indonesian products page', function () {
    refreshApplicationWithLocale('id');

    $response = $this->get('/id/produk');

    $response->assertStatus(200);
});

it('returns 200 for Indonesian blog page', function () {
    refreshApplicationWithLocale('id');

    $response = $this->get('/id/artikel');

    $response->assertStatus(200);
});

it('returns 200 for Indonesian product detail page', function () {
    refreshApplicationWithLocale('id');

    $product = Product::factory()->create();

    $response = $this->get('/id/produk/'.$product->slug);

    $response->assertStatus(200);
});

it('returns 200 for Indonesian blog detail page', function () {
    refreshApplicationWithLocale('id');

    $post = Post::factory()->create();

    $response = $this->get('/id/artikel/'.$post->slug);

    $response->assertStatus(200);
});

// ─── Route generation ──────────────────────────────────────

it('generates correct English route (without prefix)', function () {
    refreshApplicationWithLocale('en');

    $url = route('products.index');

    expect($url)->toEndWith('/products');
});

it('generates correct Indonesian route (with prefix)', function () {
    refreshApplicationWithLocale('id');

    $url = route('products.index');

    expect($url)->toEndWith('/id/produk');
});

// ─── Non-localized routes ──────────────────────────────────

// ─── Homepage blocks ────────────────────────────────────────

it('renders homepage blocks from the database setting', function () {
    refreshApplicationWithLocale('en');

    Setting::set('homepage_blocks', [
        ['type' => 'hero', 'data' => [
            'title' => ['en' => 'Welcome Hero'],
            'badge_enabled' => false,
        ]],
        ['type' => 'stats', 'data' => ['items' => [
            ['value' => 99, 'suffix' => '%', 'label' => ['en' => 'Uptime']],
        ]]],
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Welcome Hero');
    $response->assertSee('data-counter="99"', false);
    $response->assertSee('data-suffix="%"', false);
    $response->assertSee('Uptime');
});

it('does not localize API callback routes', function () {
    $response = $this->post('/api/payment/callback', []);

    expect($response->getStatusCode())->not->toBe(404);
});

it('does not localize Google auth routes', function () {
    $response = $this->get('/auth/google');

    $response->assertStatus(302);
});
