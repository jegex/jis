<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Currency;

final class PriceHelper
{
    public static function format(int|float $amount, ?string $currencyCode = null): string
    {
        $currency = $currencyCode
            ? Currency::where('code', $currencyCode)->first()
            : Currency::getDefault();

        $currency ??= Currency::getDefault();

        $decimalPlaces = $currency?->decimal_place ?? 0;
        $symbol = $currency?->symbol ?? 'Rp';

        if ($decimalPlaces > 0) {
            $formatted = number_format((float) $amount, $decimalPlaces, '.', ',');
        } else {
            $formatted = number_format((float) $amount, 0, ',', '.');
        }

        return $symbol.' '.$formatted;
    }
}
