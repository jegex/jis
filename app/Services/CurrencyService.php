<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

final class CurrencyService
{
    public function convert(float|int $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromCurrency = $this->getCurrency($from);
        $toCurrency = $this->getCurrency($to);

        $defaultAmount = $amount / $fromCurrency->exchange_rate;

        return round($defaultAmount * $toCurrency->exchange_rate, $toCurrency->decimal_place);
    }

    private function getCurrency(string $code): Currency
    {
        return Cache::remember("currency_{$code}", now()->addHour(), fn () => Currency::where('code', $code)->firstOrFail());
    }
}
