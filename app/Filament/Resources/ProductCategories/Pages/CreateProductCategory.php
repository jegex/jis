<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Enums\CategoryType;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = CategoryType::Product->value;

        return $data;
    }
}
