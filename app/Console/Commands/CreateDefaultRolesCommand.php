<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class CreateDefaultRolesCommand extends Command
{
    protected $signature = 'app:create-default-roles {--force : Recreate roles if they already exist}';

    protected $description = 'Create default writer and editor roles with WordPress-aligned permissions';

    public function handle(): int
    {
        $this->info('Syncing permissions from Filament resources...');
        Artisan::call('guardian:sync', [], $this->getOutput());

        $this->newLine();

        $this->createWriterRole();
        $this->createEditorRole();

        $this->newLine();
        $this->info('Default roles created successfully!');

        return self::SUCCESS;
    }

    private function createWriterRole(): void
    {
        $this->info('Creating writer role (WordPress Author)...');

        $writerPermissions = [
            // Posts - can manage own posts
            'ViewOwn:Post',
            'View:Post',
            'Create:Post',
            'Update:Post',
            'Delete:Post',
            'Publish:Post',

            // Categories - view only
            'ViewAny:Category',
            'View:Category',

            // Tags - view only
            'ViewAny:Tag',
            'View:Tag',

            // Dashboard - no permission needed (excluded from guardian:sync)
        ];

        $this->createRole('writer', $writerPermissions);
    }

    private function createEditorRole(): void
    {
        $this->info('Creating editor role (WordPress Editor)...');

        $editorPermissions = [
            // Posts - full CRUD + publish
            'ViewAny:Post',
            'View:Post',
            'Create:Post',
            'Update:Post',
            'Delete:Post',
            'DeleteAny:Post',
            'Restore:Post',
            'ViewOwn:Post',
            'Publish:Post',

            // Pages - full CRUD + publish
            'ViewAny:Page',
            'View:Page',
            'Create:Page',
            'Update:Page',
            'Delete:Page',
            'DeleteAny:Page',
            'Restore:Page',
            'Publish:Page',

            // Categories - full CRUD
            'ViewAny:Category',
            'View:Category',
            'Create:Category',
            'Update:Category',
            'Delete:Category',

            // Tags - full CRUD
            'ViewAny:Tag',
            'View:Tag',
            'Create:Tag',
            'Update:Tag',
            'Delete:Tag',

            // Products - full CRUD + publish
            'ViewAny:Product',
            'View:Product',
            'Create:Product',
            'Update:Product',
            'Delete:Product',
            'Publish:Product',

            // ProductCategories - full CRUD
            'ViewAny:ProductCategory',
            'View:ProductCategory',
            'Create:ProductCategory',
            'Update:ProductCategory',
            'Delete:ProductCategory',

            // Dashboard + widgets (no View:Dashboard - excluded from guardian:sync)
            'View:LatestOrders',
            'View:OrderStatsOverview',
            'View:SalesOverview',
            'View:RevenueChart',
            'View:OrdersByStatusChart',

            // Projects - view only
            'ViewAny:Project',
            'View:Project',
        ];

        $this->createRole('editor', $editorPermissions);
    }

    private function createRole(string $name, array $permissionNames): void
    {
        $existingRole = Role::where('name', $name)->where('guard_name', 'web')->first();

        if ($existingRole && ! $this->option('force')) {
            $this->warn("Role '{$name}' already exists. Use --force to recreate.");

            return;
        }

        if ($existingRole) {
            $existingRole->delete();
            $this->warn("Deleted existing role '{$name}'.");
        }

        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::whereIn('name', $permissionNames)->where('guard_name', 'web')->get();

        if ($permissions->count() !== count($permissionNames)) {
            $foundNames = $permissions->pluck('name')->toArray();
            $missing = array_diff($permissionNames, $foundNames);
            $this->warn('Some permissions not found: '.implode(', ', $missing));
        }

        $role->givePermissionTo($permissions);

        $this->info("Created role '{$name}' with ".$permissions->count().' permissions.');
    }
}
