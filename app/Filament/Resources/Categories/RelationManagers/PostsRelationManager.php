<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $relatedResource = PostResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold')
                    ->limit(50),

                TextColumn::make('author.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContentStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
