<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

final class SetPassword extends Component
{
    public string $token = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?Invitation $invitation = null;

    public function mount(string $token): void
    {
        $this->token = $token;

        $invitation = Invitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            abort(404);
        }

        $this->invitation = $invitation;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'password' => 'Password',
            'passwordConfirmation' => 'Confirm Password',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $invitation = $this->invitation;

        if (! $invitation || ! $invitation->isValid()) {
            abort(404);
        }

        $user = User::where('email', $invitation->email)->firstOrFail();

        $user->update([
            'password' => Hash::make($this->password),
            'is_admin' => true,
        ]);

        $invitation->update([
            'accepted_at' => now(),
        ]);

        auth()->login($user);

        $this->redirect(route('filament.admin.pages.dashboard'));
    }

    public function render()
    {
        return view('livewire.set-password')
            ->layout('layouts.guest');
    }
}
