<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;

final class OrderNumberGenerator extends DocumentNumberGenerator
{
    public const DEFAULT_PATTERN = 'ORD-{YYYY}{MM}-{SEQ:M}';

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
        return 'order_number_format';
    }

    protected function paddingSetting(): string
    {
        return 'order_number_padding';
    }

    protected function counterKeyPrefix(): string
    {
        return '';
    }

    protected function label(): string
    {
        return 'order';
    }

    protected function numberExists(string $number): bool
    {
        return Order::query()->where('order_number', $number)->exists();
    }

    protected function highestExistingSequence(string $pattern, CarbonInterface $date): int
    {
        return 0;
    }
}
