<?php

declare(strict_types=1);

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Currency Code')
                            ->required()
                            ->maxLength(3)
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('symbol')
                            ->required()
                            ->maxLength(10),

                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate (to default currency)')
                            ->required()
                            ->numeric()
                            ->step(0.0001)
                            ->default(1),

                        TextInput::make('decimal_place')
                            ->label('Decimal Places')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(4)
                            ->default(0)
                            ->helperText('Number of decimal places for this currency'),

                        Toggle::make('is_default')
                            ->default(false),
                    ]),
            ]);
    }
}
