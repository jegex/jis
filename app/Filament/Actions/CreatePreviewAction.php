<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

final class CreatePreviewAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label(__('Preview'))
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->databaseTransaction()
            ->action(function (Action $action, Component $livewire) {
                $livewire->validate();

                $record = $livewire->createDraftRecordForPreview();

                $livewire->js(
                    '($url) => window.open($url, "_blank", "noopener")',
                    $livewire->previewDraftUrl($record),
                );

                $livewire->redirect($livewire->previewDraftEditUrl($record));
            });
    }
}
