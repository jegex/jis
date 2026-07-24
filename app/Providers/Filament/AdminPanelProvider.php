<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Resources\MenuItems\MenuItemResource;
use App\Filament\Widgets\LatestOrders;
use App\Filament\Widgets\OrdersByStatusChart;
use App\Filament\Widgets\OrderStatsOverview;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\SalesOverview;
use App\Http\Livewire\MenuItemForm;
use App\Http\Middleware\ForceAdminLocale;
use App\Models\Menu;
use App\Models\MenuItem;
use Backstage\Mails\Mails;
use Backstage\Mails\MailsPlugin;
use Biostate\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Livewire\Livewire;
use Waguilar\FilamentGuardian\FilamentGuardianPlugin;
use Webard\FilamentTranslatable\FilamentTranslatablePlugin;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->profile(isSimple: false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandLogo(fn () => view('components.logo'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->navigationGroups([
                'Shop',
                'Content',
                'Menu',
                'Settings',
                'Admin',
            ])
            ->widgets([
                OrderStatsOverview::class,
                SalesOverview::class,
                RevenueChart::class,
                OrdersByStatusChart::class,
                LatestOrders::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                ForceAdminLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                SpatieTranslatablePlugin::make()
                    ->persist()
                    ->useFallbackLocale()
                    ->defaultLocales(config('localizer.supported_locales')),
                FilamentGuardianPlugin::make()
                    ->navigationGroup('Settings')
                    ->resourceSectionColumns(2)
                    ->resourceCheckboxColumns(3)
                    ->permissionCheckboxColumns(3),
                FilamentMenuBuilderPlugin::make()
                    ->usingMenuModel(Menu::class)
                    ->usingMenuItemModel(MenuItem::class)
                    ->usingMenuItemResource(MenuItemResource::class),
                FilamentTranslatablePlugin::make()
                    ->locales(config('localizer.supported_locales')),
                MailsPlugin::make()
                    ->canManageMails(function () {
                        $user = Auth::user();

                        if ($user->hasAnyPermission([
                            'ViewAny:Mail',
                            'View:Mail',
                        ])) {
                            return true;
                        }

                        return false;
                    }),
            ])
            ->routes(fn () => Mails::routes());
    }

    public function boot()
    {
        TranslatableTabs::configureUsing(function (TranslatableTabs $component) {
            $component
                ->localesLabels(config('localizer.locale_with_label'))
                ->locales(config('localizer.supported_locales'));
        });

        Livewire::component('menu-item-form', MenuItemForm::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): string => Blade::render('<x-filament::icon-button
                tag="a"
                target="_blank"
                href="'.url('/').'"
                icon="heroicon-o-globe-alt"
                tooltip="Visit website"
                color="gray"
            />'),
        );
    }
}
