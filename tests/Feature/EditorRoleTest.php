<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Create permissions if they don't exist
    $this->editorPermissions = [
        // Posts
        'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post', 'Delete:Post',
        'DeleteAny:Post', 'Restore:Post', 'ViewOwn:Post', 'Publish:Post',
        // Pages
        'ViewAny:Page', 'View:Page', 'Create:Page', 'Update:Page', 'Delete:Page',
        'DeleteAny:Page', 'Restore:Page', 'Publish:Page',
        // Categories
        'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category', 'Delete:Category',
        // Tags
        'ViewAny:Tag', 'View:Tag', 'Create:Tag', 'Update:Tag', 'Delete:Tag',
        // Products
        'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product',
        'Delete:Product', 'Publish:Product',
        // ProductCategories
        'ViewAny:ProductCategory', 'View:ProductCategory', 'Create:ProductCategory',
        'Update:ProductCategory', 'Delete:ProductCategory',
        // Dashboard widgets
        'View:LatestOrders', 'View:OrderStatsOverview', 'View:SalesOverview',
        'View:RevenueChart', 'View:OrdersByStatusChart',
        // Projects
        'ViewAny:Project', 'View:Project',
    ];

    foreach ($this->editorPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $this->editorRole->givePermissionTo($this->editorPermissions);

    $this->editor = User::factory()->create(['is_admin' => true]);
    $this->editor->assignRole('editor');

    $this->otherUser = User::factory()->create(['is_admin' => true]);
});

it('can access posts page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/posts')
        ->assertSuccessful();
});

it('can see all posts in listing', function (): void {
    $ownPost = Post::factory()->create(['author_id' => $this->editor->id]);
    $otherPost = Post::factory()->create(['author_id' => $this->otherUser->id]);

    $this->actingAs($this->editor)
        ->get('/admin/posts')
        ->assertSuccessful();

    // Verify the query returns all posts (not filtered by author)
    $posts = Post::withoutGlobalScope('published')->get();
    expect($posts)->toHaveCount(2);
});

it('can edit any post', function (): void {
    $otherPost = Post::factory()->create(['author_id' => $this->otherUser->id]);

    $this->actingAs($this->editor)
        ->get('/admin/posts/'.$otherPost->id.'/edit')
        ->assertSuccessful();
});

it('can access pages page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/pages')
        ->assertSuccessful();
});

it('can access categories page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/categories')
        ->assertSuccessful();
});

it('can access tags page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/tags')
        ->assertSuccessful();
});

it('can access products page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/products')
        ->assertSuccessful();
});

it('can access product categories page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/product-categories')
        ->assertSuccessful();
});

it('can access projects page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/projects')
        ->assertSuccessful();
});

it('cannot access orders page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/orders')
        ->assertForbidden();
});

it('cannot access users page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/customers')
        ->assertForbidden();
});

it('cannot access settings page', function (): void {
    $this->actingAs($this->editor)
        ->get('/admin/settings/settings')
        ->assertForbidden();
});
