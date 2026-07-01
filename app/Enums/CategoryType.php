<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryType: string
{
    case Post = 'post';
    case Product = 'product';
    case Project = 'project';

    public function getLabel(): string
    {
        return match ($this) {
            self::Post => 'Post',
            self::Product => 'Product',
            self::Project => 'Project',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Post => 'info',
            self::Product => 'success',
            self::Project => 'warning',
        };
    }
}
