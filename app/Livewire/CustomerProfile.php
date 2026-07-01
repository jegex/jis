<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class CustomerProfile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $message = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(UpdateUserProfileInformation $updater): void
    {
        $this->reset('message');

        try {
            $updater->update(Auth::user(), [
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $this->message = __('Profile updated successfully.');
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        }
    }

    public function updatePassword(UpdateUserPassword $updater): void
    {
        $this->reset('message');

        try {
            $updater->update(Auth::user(), [
                'current_password' => $this->current_password,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);

            $this->reset('current_password', 'password', 'password_confirmation');
            $this->message = __('Password updated successfully.');
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        }
    }

    public function render()
    {
        return view('livewire.customer-profile')
            ->layout('layouts.app');
    }
}
