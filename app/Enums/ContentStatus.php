<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContentStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Future = 'future';
    case Publish = 'publish';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Future => 'info',
            self::Publish => 'success',
            default => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Future => 'Scheduled',
            self::Publish => 'Published',
        };
    }
}
