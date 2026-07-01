<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Currency;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $decimalPlaces = $this->resolveDecimalPlaces($model, $attributes);

        return round((float) $value / (10 ** $decimalPlaces), $decimalPlaces);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $decimalPlaces = $this->resolveDecimalPlaces($model, $attributes);

        return (int) round((float) $value * (10 ** $decimalPlaces));
    }

    private function resolveDecimalPlaces(Model $model, array $attributes): int
    {
        $currencyCode = $model->currency_code ?? $attributes['currency_code'] ?? Currency::getDefault()?->code ?? 'IDR';

        return Currency::where('code', $currencyCode)->value('decimal_place') ?? 0;
    }
}
