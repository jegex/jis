<?php

declare(strict_types=1);

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $allPermissions = [
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

    foreach ($allPermissions as $permName) {
        Permission::findOrCreate($permName, 'web');
    }

    $this->viewOwnRole = Role::findOrCreate('Content Editor', 'web');
    $this->viewOwnRole->givePermissionTo([
        'ViewOwn:Post',
        'View:Dashboard',
        'View:HomepageBuilder', 'View:Settings', 'View:SEO', 'View:SendNewsletter',
    ]);

    $this->viewAnyRole = Role::findOrCreate('Senior Editor', 'web');
    $this->viewAnyRole->givePermissionTo(Permission::all());
});

test('admin with ViewOwn:Post can access posts page', function () {
    $user = User::factory()->admin()->create();
    $user->assignRole($this->viewOwnRole);

    $this->actingAs($user);

    $this->get('/admin/posts')->assertSuccessful();
});

test('admin with ViewOwn:Post only sees own posts in list', function () {
    $user = User::factory()->admin()->create();
    $user->assignRole($this->viewOwnRole);

    $ownPost = Post::factory()->create(['author_id' => $user->id]);
    $otherPost = Post::factory()->create(['author_id' => User::factory()->create()->id]);

    $this->actingAs($user);

    $query = PostResource::getEloquentQuery();
    $results = $query->get();

    $this->assertTrue($results->contains('id', $ownPost->id));
    $this->assertFalse($results->contains('id', $otherPost->id));
});

test('admin with ViewOwn:Post cannot edit own post without Update:Post', function () {
    $user = User::factory()->admin()->create();
    $user->assignRole($this->viewOwnRole);

    $ownPost = Post::factory()->create(['author_id' => $user->id]);

    $this->actingAs($user);

    $this->get('/admin/posts/'.$ownPost->id.'/edit')->assertForbidden();
});

test('admin with ViewOwn:Post cannot edit other users post', function () {
    $user = User::factory()->admin()->create();
    $user->assignRole($this->viewOwnRole);

    $otherPost = Post::factory()->create(['author_id' => User::factory()->create()->id]);

    $this->actingAs($user);

    $this->get('/admin/posts/'.$otherPost->id.'/edit')->assertNotFound();
});

test('admin with ViewOwn:Post cannot access create without Create:Post', function () {
    $user = User::factory()->admin()->create();
    $user->assignRole($this->viewOwnRole);

    $this->actingAs($user);

    $this->get('/admin/posts/create')->assertForbidden();
});

test('admin with ViewAny:Post sees all posts regardless of author', function () {
    $user = User::factory()->admin()->create();
    $user->assignRole($this->viewAnyRole);

    $ownPost = Post::factory()->create(['author_id' => $user->id]);
    $otherPost = Post::factory()->create(['author_id' => User::factory()->create()->id]);

    $this->actingAs($user);

    $query = PostResource::getEloquentQuery();
    $results = $query->get();

    $this->assertTrue($results->contains('id', $ownPost->id));
    $this->assertTrue($results->contains('id', $otherPost->id));
});
