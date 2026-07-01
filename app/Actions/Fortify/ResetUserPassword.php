<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Http\Requests\Fortify\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

final class ResetUserPassword implements ResetsUserPasswords
{
    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        $validated = app(ResetPasswordRequest::class)->merge($input)->validated();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();
    }
}
