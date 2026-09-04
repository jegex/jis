<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Actions\CreatePreviewAction;
use App\Filament\Resources\Pages\Concerns\CanPreviewDraft;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

final class CreatePost extends CreateRecord
{
    use CanPreviewDraft;
    use Translatable;
    use Translatable;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreatePreviewAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
