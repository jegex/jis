<?php

declare(strict_types=1);

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $value = Setting::get($key, $default);

        $fileKeys = ['logo_dark', 'logo_light', 'favicon'];

        if (in_array($key, $fileKeys) && is_string($value) && $value !== ''
            && !str_starts_with($value, 'media/')
            && !str_starts_with($value, 'http')
        ) {
            return 'media/' . $value;
        }

        return $value;
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
