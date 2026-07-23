<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'size', 'unit', 'date', 'type', 'metadata', 'is_published'])]
final class Project extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
