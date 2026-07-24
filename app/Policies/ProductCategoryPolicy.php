<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class ProductCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductCategory', $this->getGuard());
    }

    public function view(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('View:ProductCategory', $this->getGuard());
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductCategory', $this->getGuard());
    }

    public function update(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('Update:ProductCategory', $this->getGuard());
    }

    public function delete(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('Delete:ProductCategory', $this->getGuard());
    }

    public function restore(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('Restore:ProductCategory', $this->getGuard());
    }

    public function forceDelete(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('ForceDelete:ProductCategory', $this->getGuard());
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProductCategory', $this->getGuard());
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductCategory', $this->getGuard());
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductCategory', $this->getGuard());
    }

    public function replicate(AuthUser $authUser, Category $category): bool
    {
        return $authUser->can('Replicate:ProductCategory', $this->getGuard());
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductCategory', $this->getGuard());
    }

    protected function getGuard(): ?string
    {
        return Filament::getCurrentPanel()?->getAuthGuard();
    }
}
