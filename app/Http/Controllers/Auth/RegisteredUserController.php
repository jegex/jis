<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Http\Controllers\RegisteredUserController as FortifyRegisteredUserController;

final class RegisteredUserController extends FortifyRegisteredUserController
{
    public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
    {
        $request->validate([
            'g-recaptcha-response' => [
                Rule::when(config('services.recaptcha.enabled'), 'required'),
                new Recaptcha('register'),
            ],
        ], [
            'g-recaptcha-response.required' => Recaptcha::MESSAGE,
        ]);

        return parent::store($request, $creator);
    }
}
