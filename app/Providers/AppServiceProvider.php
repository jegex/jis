<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\PriceHelper;
use App\Models\Language;
use App\Services\CouponService;
use App\Services\CurrencyService;
use App\Services\OrderService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use NielsNumbers\LaravelLocalizer\Localizer;
use NielsNumbers\LaravelLocalizer\Middleware\SetLocale;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('order.service', function ($app) {
            return new OrderService($app->make(CouponService::class));
        });

        $this->app->singleton(CurrencyService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('order-access', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('order-download', function (Request $request) {
            $orderNumber = $request->route('order');

            return [
                Limit::perHour(30)->by('download-order:'.($orderNumber ?? $request->ip())),
                Limit::perMinute(10)->by('download-ip:'.$request->ip()),
            ];
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        $persistLocale = setting('persist_locale', []);

        $languages = Cache::remember('languages_with_label', now()->addHour(), fn () => Language::whereIn('code', setting('supported_locales', ['en', 'id']))
            ->get()
            ->mapWithKeys(function (Language $language) {
                return [$language->code => $language->name];
            })
            ->toArray());

        config([
            'localizer.locale_with_label' => $languages,
            'localizer.default_locale' => setting('default_locale', config('app.fallback_locale')),
            'localizer.supported_locales' => setting('supported_locales', ['en', 'id']),
            'app.fallback_locale' => setting('default_locale', config('app.fallback_locale')),
            'localizer.hide_default_locale' => setting('hide_default_locale', true),
            'localizer.redirect_enabled' => setting('redirect_enabled', true),
            'localizer.persist_locale.session' => $persistLocale['session'] ?? true,
            'localizer.persist_locale.cookie' => $persistLocale['cookie'] ?? true,

            'seo.favicon' => setting('favicon'),
        ]);

        app(Localizer::class)->setActiveDefaultLocale(setting('default_locale', config('app.fallback_locale')));

        app(PersistentMiddleware::class)
            ->addPersistentMiddleware(
                SetLocale::class,
            );

        Str::macro('price', function (int|float $amount, ?string $currencyCode = null) {
            return PriceHelper::format($amount, $currencyCode);
        });

        Blade::directive('head', function () {
            return '<?php echo app("view")->make("components.head", [
                "seoData" => $seoData ?? null,
                "model" => $model ?? null,
            ])->render(); ?>';
        });
    }
}
