<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Services\PreviewService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

final class PreviewAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label(__('Preview'))
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->openUrlInNewTab()
            ->url(function (Model $record) {
                return app(PreviewService::class)->url(
                    $record,
                    session()->get('spatie_translatable_active_locale'),
                );
            });
    }
}
