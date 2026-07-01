<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Enums\CategoryType;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! isset($data['type'])) {
            $data['type'] = CategoryType::Post->value;
        }

        return $data;
    }
}
