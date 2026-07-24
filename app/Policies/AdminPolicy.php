<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class AdminPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Admin', $this->getGuard());
    }

    public function view(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('View:Admin', $this->getGuard());
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Admin', $this->getGuard());
    }

    public function update(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Update:Admin', $this->getGuard());
    }

    public function delete(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Delete:Admin', $this->getGuard());
    }

    public function restore(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Restore:Admin', $this->getGuard());
    }

    public function forceDelete(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('ForceDelete:Admin', $this->getGuard());
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Admin', $this->getGuard());
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Admin', $this->getGuard());
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Admin', $this->getGuard());
    }

    public function replicate(AuthUser $authUser, User $user): bool
    {
        return $authUser->can('Replicate:Admin', $this->getGuard());
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Admin', $this->getGuard());
    }

    protected function getGuard(): ?string
    {
        return Filament::getCurrentPanel()?->getAuthGuard();
    }
}
