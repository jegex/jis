<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invitations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class InvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rules(['email']),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'writer' => 'Writer',
                        'editor' => 'Editor',
                    ])
                    ->required(),
            ]);
    }
}
