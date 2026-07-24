<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Waguilar\FilamentGuardian\Facades\Guardian;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Create permissions
    $permissions = [
        'Create:Invitation',
        'ViewAny:Invitation',
        'View:Invitation',
        'Update:Invitation',
        'Delete:Invitation',
        'DeleteAny:Invitation',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Create roles and assign permissions
    $this->superAdminRole = Role::firstOrCreate(['name' => Guardian::getSuperAdminRoleName(), 'guard_name' => 'web']);
    $this->superAdminRole->givePermissionTo($permissions);

    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole(Guardian::getSuperAdminRoleName());
});

afterEach(function (): void {
    Mockery::close();
});

it('admin can access invitations list page', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/invitations')
        ->assertSuccessful();
});

it('admin can access invitations create page', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/invitations/create')
        ->assertSuccessful();
});

it('non-admin cannot access invitations list page', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/invitations')
        ->assertForbidden();
});

it('non-admin cannot access invitations create page', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/invitations/create')
        ->assertForbidden();
});

it('writer cannot access invitations page', function (): void {
    $writerPermissions = [
        'ViewOwn:Post',
    ];

    foreach ($writerPermissions as $permName) {
        Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
    }

    $writerRole = Role::firstOrCreate(['name' => 'writer', 'guard_name' => 'web']);
    $writerRole->givePermissionTo($writerPermissions);

    $writer = User::factory()->admin()->create();
    $writer->assignRole('writer');

    $this->actingAs($writer)
        ->get('/admin/invitations')
        ->assertForbidden();
});

it('invitation model can be created', function (): void {
    $invitation = Invitation::create([
        'email' => 'test@example.com',
        'role' => 'writer',
        'token' => 'test-token-123',
        'invited_by' => $this->admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation->email)->toBe('test@example.com');
    expect($invitation->role)->toBe('writer');
    expect($invitation->isValid())->toBeTrue();
});

it('invitation is valid when not expired and not accepted', function (): void {
    $invitation = Invitation::factory()->create([
        'expires_at' => now()->addDays(7),
        'accepted_at' => null,
    ]);

    expect($invitation->isExpired())->toBeFalse();
    expect($invitation->isAccepted())->toBeFalse();
    expect($invitation->isValid())->toBeTrue();
});

it('invitation is not valid when expired', function (): void {
    $invitation = Invitation::factory()->create([
        'expires_at' => now()->subDay(),
        'accepted_at' => null,
    ]);

    expect($invitation->isExpired())->toBeTrue();
    expect($invitation->isValid())->toBeFalse();
});

it('invitation is not valid when accepted', function (): void {
    $invitation = Invitation::factory()->create([
        'expires_at' => now()->addDays(7),
        'accepted_at' => now(),
    ]);

    expect($invitation->isAccepted())->toBeTrue();
    expect($invitation->isValid())->toBeFalse();
});

it('invitation has correct relationship to inviter', function (): void {
    $invitation = Invitation::factory()->create([
        'invited_by' => $this->admin->id,
    ]);

    expect($invitation->inviter->id)->toBe($this->admin->id);
    expect($invitation->inviter->name)->toBe($this->admin->name);
});

it('invitation can be created for writer role', function (): void {
    $invitation = Invitation::factory()->create([
        'role' => 'writer',
    ]);

    expect($invitation->role)->toBe('writer');
});

it('invitation can be created for editor role', function (): void {
    $invitation = Invitation::factory()->create([
        'role' => 'editor',
    ]);

    expect($invitation->role)->toBe('editor');
});
