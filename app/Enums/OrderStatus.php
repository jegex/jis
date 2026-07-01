<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case CreatingPayment = 'creating_payment';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending, self::AwaitingPayment => 'warning',
            self::Paid => 'success',
            self::Failed => 'danger',
            self::Refunded => 'info',
            default => 'gray'
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return str($this->value)->headline()->title()->toString();
    }
}
