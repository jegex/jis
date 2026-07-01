<?php

declare(strict_types=1);

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('setting_translated')) {
    function setting_translated(string $key, ?string $locale = null): string
    {
        return Setting::getTranslated($key, $locale);
    }
}

if (! function_exists('locale_text')) {
    function locale_text(mixed $value, ?string $locale = null, string $fallback = 'en'): string
    {
        if (is_array($value)) {
            return $value[$locale ?? app()->getLocale()] ?? $value[$fallback] ?? '';
        }

        return (string) ($value ?? '');
    }
}
