<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

final class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Invitation');
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $user->can('View:Invitation');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Invitation');
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $user->can('Update:Invitation');
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->can('Delete:Invitation');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Invitation');
    }

    public function restore(User $user, Invitation $invitation): bool
    {
        return $user->can('Restore:Invitation');
    }

    public function forceDelete(User $user, Invitation $invitation): bool
    {
        return $user->can('ForceDelete:Invitation');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Invitation');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Invitation');
    }

    public function replicate(User $user, Invitation $invitation): bool
    {
        return $user->can('Replicate:Invitation');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Invitation');
    }
}
