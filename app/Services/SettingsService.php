<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

final class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    public function getTranslated(string $key, ?string $locale = null): string
    {
        return Setting::getTranslated($key, $locale);
    }

    public function set(string $key, mixed $value): void
    {
        Setting::set($key, $value);
    }

    public function setMany(array $data): void
    {
        Setting::set($data);
    }

    public function clearCache(): void
    {
        Setting::forgetCache();
    }

    public function getAll(): Collection
    {
        return Setting::all();
    }
}
