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
}
