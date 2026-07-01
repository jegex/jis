<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;

// ─── Shared route list ──────────────────────────────────────

$adminListUrls = [
    '/admin',
    '/admin/categories',
    '/admin/coupons',
    '/admin/currencies',
    '/admin/orders',
    '/admin/pages',
    '/admin/posts',
    '/admin/product-categories',
    '/admin/products',
    '/admin/projects',
    '/admin/tags',
    '/admin/users',
    '/admin/settings/general-settings',
    '/admin/settings/s-e-o-templates',
    '/admin/settings/system-settings',
    '/admin/settings/homepage-builder',
    '/admin/mail/email-templates',
    '/admin/mail/send-newsletter',
    '/admin/menus',
    '/admin/menu-items',
    '/admin/mails',
];

$adminCreateUrls = [
    '/admin/categories/create',
    '/admin/coupons/create',
    '/admin/currencies/create',
    '/admin/orders/create',
    '/admin/pages/create',
    '/admin/posts/create',
    '/admin/product-categories/create',
    '/admin/products/create',
    '/admin/tags/create',
    '/admin/users/create',
    '/admin/menu-items/create',
    '/admin/menus/create',
    '/admin/mail/email-templates/create',
];

// ─── Non-admin user → 403 ──────────────────────────────────

test('non-admin admin dashboard returns 403', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]));
    $this->get('/admin')->assertForbidden();
});

test('non-admin list pages return 403', function (string $url) {
    $this->actingAs(User::factory()->create(['is_admin' => false]));
    $this->get($url)->assertForbidden();
})->with($adminListUrls);

test('non-admin create pages return 403', function (string $url) {
    $this->actingAs(User::factory()->create(['is_admin' => false]));
    $this->get($url)->assertForbidden();
})->with($adminCreateUrls);

// ─── Admin user → 200 ───────────────────────────────────────

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->category = Category::factory()->forPosts()->create();
    $this->post = Post::factory()->create();
    $this->product = Product::factory()->create();
    $this->page = Page::factory()->create();
    $this->coupon = Coupon::factory()->create();
    $this->order = Order::factory()->create();
    $this->tag = Tag::factory()->create();

    Project::create([
        'name' => 'Test Project',
        'type' => 'construction',
        'size' => 100,
        'unit' => 'm',
        'date' => now(),
    ]);
});

test('admin dashboard returns 200', function () {
    $this->get('/admin')->assertSuccessful();
});

test('admin list pages return 200', function (string $url) {
    $this->get($url)->assertSuccessful();
})->with($adminListUrls);

test('admin create pages return 200', function (string $url) {
    $this->get($url)->assertSuccessful();
})->with($adminCreateUrls);

test('admin edit category page returns 200', function () {
    $this->get('/admin/categories/'.$this->category->id.'/edit')->assertSuccessful();
});

test('admin edit coupon page returns 200', function () {
    $this->get('/admin/coupons/'.$this->coupon->id.'/edit')->assertSuccessful();
});

test('admin edit currency page returns 200', function () {
    $this->get('/admin/currencies/'.Currency::first()->id.'/edit')->assertSuccessful();
});

test('admin edit order page returns 200', function () {
    $this->get('/admin/orders/'.$this->order->id.'/edit')->assertSuccessful();
});

test('admin edit page record returns 200', function () {
    $this->get('/admin/pages/'.$this->page->id.'/edit')->assertSuccessful();
});

test('admin edit post page returns 200', function () {
    $this->get('/admin/posts/'.$this->post->id.'/edit')->assertSuccessful();
});

test('admin edit product page returns 200', function () {
    $this->get('/admin/products/'.$this->product->id.'/edit')->assertSuccessful();
});

test('admin edit tag page returns 200', function () {
    $this->get('/admin/tags/'.$this->tag->id.'/edit')->assertSuccessful();
});
