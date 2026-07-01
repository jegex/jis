<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Http\Requests\Fortify\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

final class UpdateUserPassword implements UpdatesUserPasswords
{
    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $validated = app(UpdatePasswordRequest::class)->merge($input)->validated();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();
    }
}
