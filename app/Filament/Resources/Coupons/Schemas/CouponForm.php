<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponAppliesTo;
use App\Enums\CouponType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->live(onBlur: true),

                        Select::make('type')
                            ->live()
                            ->options(CouponType::class)
                            ->required(),

                        TextInput::make('value')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix(fn (Get $get) => $get('type') === CouponType::Percentage ? '%' : config('payment.default_currency', 'IDR')),

                        TextInput::make('max_uses')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Set to 0 for unlimited uses'),

                        DateTimePicker::make('expires_at'),
                    ]),
                Section::make('Conditions')
                    ->schema([
                        Select::make('applies_to')
                            ->options(CouponAppliesTo::class),

                        Select::make('product_id')
                            ->relationship('product', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
