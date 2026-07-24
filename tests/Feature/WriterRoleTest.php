<?php

declare(strict_types=1);

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Create permissions if they don't exist
    $this->writerPermissions = [
        'ViewOwn:Post',
        'View:Post',
        'Create:Post',
        'Update:Post',
        'Delete:Post',
        'Publish:Post',
        'ViewAny:Category',
        'View:Category',
        'ViewAny:Tag',
        'View:Tag',
    ];

    foreach ($this->writerPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->writerRole = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    $this->writerRole->givePermissionTo($this->writerPermissions);

    $this->writer = User::factory()->create(['is_admin' => true]);
    $this->writer->assignRole('writer');

    $this->otherUser = User::factory()->create(['is_admin' => true]);
});

it('can access posts page', function (): void {
    $this->actingAs($this->writer)
        ->get('/admin/posts')
        ->assertSuccessful();
});

it('only sees own posts in listing', function (): void {
    $ownPost = Post::factory()->create(['author_id' => $this->writer->id]);
    $otherPost = Post::factory()->create(['author_id' => $this->otherUser->id]);

    $this->actingAs($this->writer);

    $query = PostResource::getEloquentQuery();
    $results = $query->get();

    expect($results->contains('id', $ownPost->id))->toBeTrue();
    expect($results->contains('id', $otherPost->id))->toBeFalse();
});

it('can edit own post', function (): void {
    $ownPost = Post::factory()->create(['author_id' => $this->writer->id]);

    $this->actingAs($this->writer)
        ->get('/admin/posts/'.$ownPost->id.'/edit')
        ->assertSuccessful();
});

it('cannot edit other users post', function (): void {
    $otherPost = Post::factory()->create(['author_id' => $this->otherUser->id]);

    $this->actingAs($this->writer)
        ->get('/admin/posts/'.$otherPost->id.'/edit')
        ->assertNotFound();
});

it('can access categories page', function (): void {
    $this->actingAs($this->writer)
        ->get('/admin/categories')
        ->assertSuccessful();
});

it('can access tags page', function (): void {
    $this->actingAs($this->writer)
        ->get('/admin/tags')
        ->assertSuccessful();
});

it('cannot access orders page', function (): void {
    $this->actingAs($this->writer)
        ->get('/admin/orders')
        ->assertForbidden();
});

it('cannot access users page', function (): void {
    $this->actingAs($this->writer)
        ->get('/admin/customers')
        ->assertForbidden();
});
