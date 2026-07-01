<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CategoriesTable
{
    public static function configure(Table $table, ?string $countRelation = null): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold'),

                TextColumn::make('slug')
                    ->searchable(),

                IconColumn::make('is_published')
                    ->boolean(),

                TextColumn::make("{$countRelation}_count")
                    ->visible($countRelation !== null)
                    ->counts($countRelation)
                    ->label(ucfirst($countRelation)),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
