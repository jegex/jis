<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Payment', $this->getGuard());
    }

    public function view(AuthUser $authUser, Payment $payment): bool
    {
        return $authUser->can('View:Payment', $this->getGuard());
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Payment', $this->getGuard());
    }

    public function update(AuthUser $authUser, Payment $payment): bool
    {
        return $authUser->can('Update:Payment', $this->getGuard());
    }

    public function delete(AuthUser $authUser, Payment $payment): bool
    {
        return $authUser->can('Delete:Payment', $this->getGuard());
    }

    public function restore(AuthUser $authUser, Payment $payment): bool
    {
        return $authUser->can('Restore:Payment', $this->getGuard());
    }

    public function forceDelete(AuthUser $authUser, Payment $payment): bool
    {
        return $authUser->can('ForceDelete:Payment', $this->getGuard());
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Payment', $this->getGuard());
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Payment', $this->getGuard());
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Payment', $this->getGuard());
    }

    public function replicate(AuthUser $authUser, Payment $payment): bool
    {
        return $authUser->can('Replicate:Payment', $this->getGuard());
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Payment', $this->getGuard());
    }

    protected function getGuard(): ?string
    {
        return Filament::getCurrentPanel()?->getAuthGuard();
    }
}
