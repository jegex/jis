<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

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

final class PostsTable
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

                TextColumn::make('author.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContentStatus::class),

                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
                        ->visible(fn (): bool => auth()->user()?->can('Publish:Post') ?? false)
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
                        ->visible(fn (): bool => auth()->user()?->can('Publish:Post') ?? false)
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
