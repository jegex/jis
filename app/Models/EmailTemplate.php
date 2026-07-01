<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailTemplateType;
use Database\Factories\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

final class EmailTemplate extends Model
{
    /** @use HasFactory<EmailTemplateFactory> */
    use HasFactory, HasTranslations;

    public array $translatable = ['subject', 'body'];

    protected $fillable = [
        'type',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => EmailTemplateType::class,
            'variables' => 'json',
            'is_active' => 'boolean',
        ];
    }
}
