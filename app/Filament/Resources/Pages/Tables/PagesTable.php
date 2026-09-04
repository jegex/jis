<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\ContentStatus;
use App\Filament\Actions\PreviewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold')
                    ->limit(50),

                TextColumn::make('slug')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContentStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                PreviewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('unpublish')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->requiresConfirmation()
                        ->visible(fn (): bool => auth()->user()?->can('Publish:Page') ?? false)
                        ->accessSelectedRecords()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($selectedRecords) {
                            $selectedRecords->map(function ($record) {
                                $record->status = ContentStatus::Draft;
                                $record->save();
                            });
                        }),
                    Action::make('publish')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => auth()->user()?->can('Publish:Page') ?? false)
                        ->accessSelectedRecords()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($selectedRecords) {
                            $selectedRecords->map(function ($record) {
                                $record->status = ContentStatus::Publish;
                                $record->save();
                            });
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
