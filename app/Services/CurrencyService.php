<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;

final class CurrencyService
{
    public function convert(float|int $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromCurrency = Currency::where('code', $from)->firstOrFail();
        $toCurrency = Currency::where('code', $to)->firstOrFail();

        $defaultAmount = $amount * $fromCurrency->exchange_rate;

        return round($defaultAmount / $toCurrency->exchange_rate, $toCurrency->decimal_place);
    }
}
