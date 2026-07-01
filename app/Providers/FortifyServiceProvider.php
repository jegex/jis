<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

final class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance(LoginResponseContract::class, new class implements LoginResponseContract
        {
            public function toResponse($request)
            {
                if ($request->user()->is_admin) {
                    return redirect('/admin');
                }

                return $request->wantsJson()
                    ? response()->json(['two_factor' => false])
                    : redirect()->intended(route('customer.dashboard'));
            }
        });

        $this->app->instance(RegisterResponseContract::class, new class implements RegisterResponseContract
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? new JsonResponse('', 201)
                    : redirect()->intended(route('customer.dashboard'));
            }
        });

        $this->app->instance(TwoFactorLoginResponseContract::class, new class implements TwoFactorLoginResponseContract
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? new JsonResponse('', 204)
                    : redirect()->intended(route('customer.dashboard'));
            }
        });

        $this->app->instance(PasswordResetResponseContract::class, new class implements PasswordResetResponseContract
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? new JsonResponse(['message' => __('passwords.reset')], 200)
                    : redirect(route('customer.dashboard'))->with('status', __('passwords.reset'));
            }
        });

        $this->app->instance(VerifyEmailResponseContract::class, new class implements VerifyEmailResponseContract
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? new JsonResponse('', 204)
                    : redirect()->intended(route('customer.dashboard').'?verified=1');
            }
        });

        $this->app->instance(PasswordConfirmedResponseContract::class, new class implements PasswordConfirmedResponseContract
        {
            public function toResponse($request)
            {
                return $request->wantsJson()
                    ? new JsonResponse('', 201)
                    : redirect()->intended(route('customer.dashboard'));
            }
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['token' => $request->token]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });
    }
}
