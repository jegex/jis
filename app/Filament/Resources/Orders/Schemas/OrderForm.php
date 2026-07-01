<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema(self::getDetailsComponents())
                            ->columns(2),

                        Section::make('Pricing')
                            ->columns(2)
                            ->schema([
                                Select::make('currency_code')
                                    ->live()
                                    ->relationship('currency', 'code')
                                    ->default('IDR')
                                    ->required()
                                    ->label('Currency'),

                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix(fn (Get $get) => $get('currency_code') ?? 'IDR'),

                                TextInput::make('discount')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix(fn (Get $get) => $get('currency_code') ?? 'IDR'),

                                TextInput::make('total')
                                    ->required()
                                    ->numeric()
                                    ->prefix(fn (Get $get) => $get('currency_code') ?? 'IDR'),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                Group::make()
                    ->schema([
                        Section::make([
                            TextEntry::make('created_at')
                                ->label('Order date')
                                ->state(fn (Order $record): ?string => $record->created_at?->diffForHumans()),

                            TextEntry::make('updated_at')
                                ->label('Last modified at')
                                ->state(fn (Order $record): ?string => $record->updated_at?->diffForHumans()),
                        ]),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ]);
    }

    /**
     * @return array<Component>
     */
    public static function getDetailsComponents(): array
    {
        return [
            TextInput::make('guest_email')
                ->email()
                ->maxLength(255)
                ->label('Guest Email'),

            TextInput::make('guest_name')
                ->maxLength(255)
                ->label('Guest Name'),

            Select::make('user_id')
                ->relationship('user', 'email')
                ->searchable()
                ->preload()
                ->label('Registered User'),

            Select::make('status')
                ->options(OrderStatus::class)
                ->required()
                ->live(),

            Select::make('coupon_id')
                ->relationship('coupon', 'code')
                ->searchable()
                ->preload(),

            Textarea::make('notes')
                ->maxLength(1000)
                ->columnSpanFull(),
        ];
    }
}
