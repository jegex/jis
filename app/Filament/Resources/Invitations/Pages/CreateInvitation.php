<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invitations\Pages;

use App\Filament\Resources\Invitations\InvitationResource;
use App\Jobs\SendInvitationEmail;
use App\Models\Invitation;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function getFormSchema(): Schema
    {
        return Schema::make()
            ->schema([
                Section::make('Invite User')
                    ->description('Send an invitation to create an account')
                    ->schema([
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
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Invitation
    {
        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser) {
            $existingUser->assignRole($data['role']);

            Notification::make()
                ->title('Role assigned')
                ->body("{$existingUser->email} has been assigned as {$data['role']}")
                ->success()
                ->send();

            return (new Invitation())->forceFill([
                'email' => $data['email'],
                'role' => $data['role'],
                'token' => Str::random(64),
                'invited_by' => auth()->id(),
                'expires_at' => now()->addDays(7),
                'accepted_at' => now(),
            ])->save();
        }

        $invitation = Invitation::create([
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => Str::random(64),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        $user = User::create([
            'email' => $data['email'],
            'name' => Str::before($data['email'], '@'),
            'password' => Str::random(32),
            'is_admin' => true,
            'email_verified_at' => now(),
            'locale' => 'en',
            'admin_locale' => 'en',
            'timezone' => 'UTC',
        ]);

        $user->assignRole($data['role']);

        Notification::make()
            ->title('Invitation sent')
            ->body("Invitation sent to {$data['email']}")
            ->success()
            ->send();

        SendInvitationEmail::dispatch($invitation);

        return $invitation;
    }
}
