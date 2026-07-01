<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class GoogleLoginController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $socialAccount = SocialAccount::where('provider', 'google')
            ->where('provider_id', (string) $googleUser->id)
            ->first();

        if ($socialAccount) {
            Auth::login($socialAccount->user);

            return redirect()->intended(config('fortify.home'));
        }

        $user = User::where('email', $googleUser->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make(Str::password(32)),
                'email_verified_at' => now(),
                'locale' => session('locale', app()->getLocale()),
            ]);
        }

        $user->socialAccounts()->create([
            'provider' => 'google',
            'provider_id' => (string) $googleUser->id,
            'avatar' => $googleUser->avatar,
        ]);

        Auth::login($user);

        return redirect()->intended(config('fortify.home'));
    }
}
