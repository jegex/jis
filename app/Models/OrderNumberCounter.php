<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class OrderNumberCounter extends Model
{
    /** @use HasFactory<\Database\Factories\OrderNumberCounterFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    protected $attributes = [
        'value' => 0,
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }
}
