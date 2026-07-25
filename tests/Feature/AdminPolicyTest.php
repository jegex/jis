<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Waguilar\FilamentGuardian\Facades\Guardian;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    $adminPermissions = [
        'ViewAny:Admin', 'View:Admin', 'Create:Admin', 'Update:Admin', 'Delete:Admin',
        'DeleteAny:Admin', 'Restore:Admin', 'ForceDelete:Admin',
        'RestoreAny:Admin', 'ForceDeleteAny:Admin', 'Replicate:Admin', 'Reorder:Admin',
    ];

    foreach ($adminPermissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->superAdminRole = Role::firstOrCreate(['name' => Guardian::getSuperAdminRoleName(), 'guard_name' => 'web']);
    $this->superAdminRole->givePermissionTo($adminPermissions);

    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole(Guardian::getSuperAdminRoleName());

    $this->target = User::factory()->admin()->create();
});

it('soft deletes a user', function (): void {
    $this->target->delete();

    expect(User::find($this->target->id))->toBeNull();
    expect(User::withoutGlobalScopes([SoftDeletingScope::class])->find($this->target->id))->not->toBeNull();
});

it('trashed users are excluded from default query', function (): void {
    $this->target->delete();

    $this->actingAs($this->admin)
        ->get('/admin/admins')
        ->assertSuccessful();
});

it('super_admin can access admin list page', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/admins')
        ->assertSuccessful();
});

it('super_admin can access admin create page', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/admins/create')
        ->assertSuccessful();
});

it('super_admin can edit admin', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/admins/'.$this->target->id.'/edit')
        ->assertSuccessful();
});

it('editor with ViewAny:Admin permission can access admin list page', function (): void {
    $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $editorRole->givePermissionTo(['ViewAny:Admin', 'View:Admin']);

    $editor = User::factory()->admin()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get('/admin/admins')
        ->assertSuccessful();
});

it('editor without ViewAny:Admin permission cannot access admin list page', function (): void {
    $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $editor = User::factory()->admin()->create();
    $editor->assignRole('editor');

    $this->actingAs($editor)
        ->get('/admin/admins')
        ->assertForbidden();
});

it('writer with ViewAny:Admin permission can access admin list page', function (): void {
    $writerRole = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    $writerRole->givePermissionTo(['ViewAny:Admin', 'View:Admin']);

    $writer = User::factory()->admin()->create();
    $writer->assignRole('writer');

    $this->actingAs($writer)
        ->get('/admin/admins')
        ->assertSuccessful();
});

it('writer without ViewAny:Admin permission cannot access admin list page', function (): void {
    $writerRole = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);

    $writer = User::factory()->admin()->create();
    $writer->assignRole('writer');

    $this->actingAs($writer)
        ->get('/admin/admins')
        ->assertForbidden();
});
