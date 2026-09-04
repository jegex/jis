<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

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

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold')
                    ->limit(40),

                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->money(fn ($record) => $record->currency?->code ?? 'IDR')
                    ->sortable(),

                TextColumn::make('release_date')
                    ->label('Release Date')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->isPreorder() ? 'warning' : 'success')
                    ->formatStateUsing(fn ($record) => $record->isPreorder()
                        ? 'Preorder'
                        : ($record->release_date ? 'Released' : 'Regular')),

                TextColumn::make('discount_price')
                    ->money(fn ($record) => $record->currency?->code ?? 'IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge(),

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

                SelectFilter::make('release_status')
                    ->label('Release Status')
                    ->options([
                        'regular' => 'Regular',
                        'preorder' => 'Preorder',
                        'released' => 'Released',
                    ])
                    ->query(function ($query, $state) {
                        return match ($state['value'] ?? null) {
                            'regular' => $query->whereNull('release_date'),
                            'preorder' => $query->where('release_date', '>', now()),
                            'released' => $query->where('release_date', '<=', now())->whereNotNull('release_date'),
                            default => $query,
                        };
                    }),
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
