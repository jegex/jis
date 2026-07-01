<?php

declare(strict_types=1);

namespace App\Models;

use Closure;
use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'decimal_place',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:4',
            'decimal_place' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public static function getDefault(): Closure|self
    {
        return once(function () {
            return self::query()->firstWhere('is_default', true);
        });
    }

    protected static function booted(): void
    {
        self::saving(function (Currency $currency) {
            if ($currency->is_default) {
                static::query()
                    ->where('id', '!=', $currency->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
