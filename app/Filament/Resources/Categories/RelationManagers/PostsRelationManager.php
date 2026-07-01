<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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

                IconColumn::make('is_published')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_published'),
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
