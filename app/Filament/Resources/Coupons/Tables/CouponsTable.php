<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\CouponType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold'),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === CouponType::Percentage ? "{$state}%" : number_format($state, 0))
                    ->sortable(),

                TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(fn ($state, $record) => "{$state}/{$record->max_uses}")
                    ->placeholder('Unlimited'),

                TextColumn::make('max_uses')
                    ->label('Max Uses')
                    ->formatStateUsing(fn ($state) => $state === 0 || $state === null ? 'Unlimited' : $state),

                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(CouponType::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
