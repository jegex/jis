<?php

declare(strict_types=1);

namespace App\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class Setting extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function set(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::updateOrCreate(
                    ['key' => $k],
                    ['value' => $v instanceof BackedEnum ? $v->value : $v],
                );
            }

            self::forgetCache();

            return new self;
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value instanceof BackedEnum ? $value->value : $value],
        );

        self::forgetCache();

        return $setting;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::getAll()->get($key, $default);
    }

    public static function getTranslated(string $key, ?string $locale = null): string
    {
        $value = static::get($key, []);

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $locale ??= app()->getLocale();

            return $value[$locale] ?? $value[array_key_first($value)] ?? '';
        }

        return '';
    }

    public static function getOnly(array $keys = []): Collection
    {
        return static::whereIn('key', $keys)->pluck('value', 'key');
    }

    public static function getAll(bool $fetch = false): Collection
    {
        if ($fetch) {
            return static::pluck('value', 'key');
        }

        return Cache::rememberForever('settings', function () {
            return static::pluck('value', 'key');
        });
    }

    public static function has(string $key): bool
    {
        return static::getAll()->has($key);
    }

    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
        static::forgetCache();
    }

    public static function forgetCache(): void
    {
        Cache::forget('settings');
    }
}
