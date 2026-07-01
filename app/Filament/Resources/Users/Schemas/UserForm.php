<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use DateTimeZone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn ($record) => $record === null)
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                    ]),

                Section::make('Preferences')
                    ->columns(2)
                    ->schema([
                        Select::make('locale')
                            ->options([
                                'en' => 'English',
                                'id' => 'Bahasa Indonesia',
                            ])
                            ->default('en'),

                        Select::make('admin_locale')
                            ->label('Admin Panel Language')
                            ->options([
                                'en' => 'English',
                                'id' => 'Bahasa Indonesia',
                            ])
                            ->default('en')
                            ->native(false),

                        Select::make('timezone')
                            ->options(DateTimeZone::listIdentifiers())
                            ->searchable()
                            ->default('Asia/Jakarta'),
                    ]),
            ]);
    }
}
