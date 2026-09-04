<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Actions\CreatePreviewAction;
use App\Filament\Resources\Pages\Concerns\CanPreviewDraft;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

final class CreateProduct extends CreateRecord
{
    use CanPreviewDraft;
    use Translatable;
    use Translatable;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreatePreviewAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
