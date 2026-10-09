<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use Carbon\CarbonInterface;

final class InvoiceNumberGenerator extends DocumentNumberGenerator
{
    public const DEFAULT_PATTERN = 'INV/{YYYY}/{MM}/{SEQ:M}';

    public const DEFAULT_PADDING = NumberFormat::DEFAULT_PADDING;

    protected function defaultPattern(): string
    {
        return self::DEFAULT_PATTERN;
    }

    protected function defaultPadding(): int
    {
        return self::DEFAULT_PADDING;
    }

    protected function patternSetting(): string
    {
        return 'invoice_number_format';
    }

    protected function paddingSetting(): string
    {
        return 'invoice_number_padding';
    }

    protected function counterKeyPrefix(): string
    {
        return 'invoice:';
    }

    protected function label(): string
    {
        return 'invoice';
    }

    protected function numberExists(string $number): bool
    {
        return Invoice::query()->where('number', $number)->exists();
    }

    protected function highestExistingSequence(string $pattern, CarbonInterface $date): int
    {
        $regex = NumberFormat::matchingRegex($pattern, $date);

        if ($regex === null) {
            return 0;
        }

        $position = mb_strpos($pattern, '{');
        $prefix = $position === false ? $pattern : mb_substr($pattern, 0, $position);

        $highest = 0;

        foreach (Invoice::query()->where('number', 'like', $prefix.'%')->pluck('number') as $number) {
            if (preg_match($regex, (string) $number, $matches) === 1) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest;
    }
}
