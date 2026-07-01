<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Success => 'success',
            self::Failed => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }
}
