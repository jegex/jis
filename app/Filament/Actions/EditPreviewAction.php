<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Services\PreviewService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;

final class EditPreviewAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label(__('Preview'))
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->action(function (Action $action, Component $livewire) {
                $record = $livewire->getRecord();

                if (! method_exists($livewire->form, 'getState')) {
                    return;
                }

                $data = $livewire->form->getState();

                $locale = property_exists($livewire, 'activeLocale')
                    ? $livewire->activeLocale
                    : session()->get('spatie_translatable_active_locale');

                $url = app(PreviewService::class)->provisionalUrl(
                    $record,
                    $data,
                    $locale,
                );

                $livewire->js(
                    '($url) => window.open($url, "_blank", "noopener")',
                    $url,
                );
            });
    }
}
