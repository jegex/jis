<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasTranslatableRouteKey
{
    public function getRouteKey(): mixed
    {
        $key = parent::getRouteKey();

        return $key ?? $this->getTranslation($this->getRouteKeyName(), config('app.fallback_locale'));
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $result = parent::resolveRouteBinding($value, $field);

        if ($result !== null) {
            return $result;
        }

        $fallbackLocale = config('app.fallback_locale');

        if (app()->getLocale() !== $fallbackLocale) {
            $field = $field ?? $this->getRouteKeyName();

            return $this->newQuery()->where("{$field}->{$fallbackLocale}", $value)->first();
        }

        return null;
    }
}
