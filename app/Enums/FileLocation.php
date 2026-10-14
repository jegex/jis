<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FileLocation: string implements HasColor, HasLabel
{
    case Local = 'local';
    case External = 'external';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::External => 'info',
            default => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Local => 'Local File',
            self::External => 'External URL',
        };
    }
}
