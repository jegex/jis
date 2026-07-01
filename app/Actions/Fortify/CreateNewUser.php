<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Http\Requests\Fortify\CreateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $validated = app(CreateUserRequest::class)->merge($input)->validated();

        return User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'locale' => session('locale', app()->getLocale()),
        ]);
    }
}
