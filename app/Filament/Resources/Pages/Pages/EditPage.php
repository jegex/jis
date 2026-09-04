<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Actions\EditPreviewAction;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

final class EditPage extends EditRecord
{
    use Translatable;
    use Translatable;

    protected static string $resource = PageResource::class;

    public function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditPreviewAction::make(),
            Action::make('revisions')
                ->label(__('Revisions'))
                ->icon(Heroicon::OutlinedClock)
                ->color('gray')
                ->url(fn () => PageResource::getUrl('revisions', ['record' => $this->getRecord()->getKey()])),
            DeleteAction::make(),
        ];
    }
}
