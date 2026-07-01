<?php

declare(strict_types=1);

namespace App\Models;

use Biostate\FilamentMenuBuilder\Models\MenuItem as BaseMenuItem;
use NielsNumbers\LaravelLocalizer\Facades\Localizer;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable(['name', 'description'])]
final class MenuItem extends BaseMenuItem
{
    use HasTranslations;

    public static function resolveLookupRouteName(string $name): string
    {
        if (str_starts_with($name, 'translated_') || str_starts_with($name, 'without_locale.')) {
            return $name;
        }

        $prefixed = 'without_locale.'.$name;

        if (app('router')->getRoutes()->hasNamedRoute($prefixed)) {
            return $prefixed;
        }

        return $name;
    }

    public function normalizeRouteParameters(): array
    {
        if ($this->route_parameters->isNotEmpty()) {
            $first = $this->route_parameters->first();

            if (is_array($first) && (isset($first['key']) || isset($first['value']))) {
                return $this->route_parameters->pluck('value', 'key')->toArray();
            }
        }

        return $this->route_parameters->toArray();
    }

    public function resolveRoute(): string
    {
        $routeName = $this->route;

        $params = $this->normalizeRouteParameters();

        if (str_starts_with($routeName, 'translated_') || str_starts_with($routeName, 'without_locale.')) {
            return route($routeName, $params);
        }

        $locale = app()->getLocale();
        $hideDefault = config('localizer.hide_default_locale', true);

        if ($locale === Localizer::defaultLocale() && $hideDefault) {
            $prefixed = 'without_locale.'.$routeName;

            if (app('router')->getRoutes()->hasNamedRoute($prefixed)) {
                return route($prefixed, $params);
            }
        }

        $localeRoute = 'translated_'.$locale.'.'.$routeName;

        if (app('router')->getRoutes()->hasNamedRoute($localeRoute)) {
            return route($localeRoute, $params);
        }

        $fallbackRoute = 'without_locale.'.$routeName;

        if (app('router')->getRoutes()->hasNamedRoute($fallbackRoute)) {
            return route($fallbackRoute, $params);
        }

        return route($routeName, $params);
    }
}
