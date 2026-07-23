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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Waguilar\FilamentGuardian\Facades\Guardian;

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
    '/admin/admins',
    '/admin/settings/settings',
    '/admin/settings/seo',
    '/admin/homepage-builder',
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
    '/admin/admins/create',
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
    $superAdminRole = Role::findOrCreate(Guardian::getSuperAdminRoleName(), 'web');

    // Create permissions needed for page access
    $pagePermissions = [
        'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User', 'DeleteAny:User',
        'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category', 'Delete:Category', 'DeleteAny:Category',
        'ViewAny:ProductCategory', 'View:ProductCategory', 'Create:ProductCategory', 'Update:ProductCategory', 'Delete:ProductCategory', 'DeleteAny:ProductCategory',
        'ViewAny:Coupon', 'View:Coupon', 'Create:Coupon', 'Update:Coupon', 'Delete:Coupon', 'DeleteAny:Coupon',
        'ViewAny:Currency', 'View:Currency', 'Create:Currency', 'Update:Currency', 'Delete:Currency', 'DeleteAny:Currency',
        'ViewAny:Order', 'View:Order', 'Create:Order', 'Update:Order', 'Delete:Order', 'DeleteAny:Order',
        'ViewAny:Page', 'View:Page', 'Create:Page', 'Update:Page', 'Delete:Page', 'DeleteAny:Page',
        'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post', 'Delete:Post', 'DeleteAny:Post',
        'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product', 'Delete:Product', 'DeleteAny:Product',
        'ViewAny:Project', 'View:Project', 'Create:Project', 'Update:Project', 'Delete:Project', 'DeleteAny:Project',
        'ViewAny:Tag', 'View:Tag', 'Create:Tag', 'Update:Tag', 'Delete:Tag', 'DeleteAny:Tag',
        'ViewAny:EmailTemplate', 'View:EmailTemplate', 'Create:EmailTemplate', 'Update:EmailTemplate', 'Delete:EmailTemplate',
        'ViewAny:Mail', 'View:Mail', 'Create:Mail', 'Update:Mail', 'Delete:Mail', 'DeleteAny:Mail',
        'ViewAny:MenuItem', 'View:MenuItem', 'Create:MenuItem', 'Update:MenuItem', 'Delete:MenuItem', 'DeleteAny:MenuItem',
        'ViewAny:Menu', 'View:Menu', 'Create:Menu', 'Update:Menu', 'Delete:Menu', 'DeleteAny:Menu',
        'View:HomepageBuilder', 'View:Settings', 'View:SEO', 'View:SendNewsletter',
        'Publish:Post', 'Publish:Page',
        'View:Dashboard',
        'ViewOwn:Post',
    ];

    foreach ($pagePermissions as $permName) {
        Permission::findOrCreate($permName, 'web');
    }

    $superAdminRole->givePermissionTo(Permission::all());

    $user = User::factory()->admin()->create();
    $user->assignRole($superAdminRole);
    $this->actingAs($user);

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
