<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasLabel;

enum PreorderInterval: string implements HasLabel
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';

    public function getLabel(): string
    {
        return match ($this) {
            self::Day => 'Day(s)',
            self::Week => 'Week(s)',
            self::Month => 'Month(s)',
        };
    }

    public function addTo(CarbonInterface $date, int $duration): CarbonInterface
    {
        return match ($this) {
            self::Day => $date->copy()->addDays($duration),
            self::Week => $date->copy()->addWeeks($duration),
            self::Month => $date->copy()->addMonths($duration),
        };
    }
}
