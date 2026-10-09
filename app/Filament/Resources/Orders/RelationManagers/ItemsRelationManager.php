<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(2000)
                    ->helperText('Printed on the invoice. Falls back to the product name when left blank.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('description')
                    ->limit(60)
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('price')
                    ->numeric(),
                TextColumn::make('quantity')
                    ->numeric(),
            ])
            ->headerActions([])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
