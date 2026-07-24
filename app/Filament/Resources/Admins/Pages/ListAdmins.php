<?php

declare(strict_types=1);

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Jobs\SendInvitationEmail;
use App\Models\Invitation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

final class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inviteUser')
                ->label('Invite User')
                ->icon('heroicon-o-paper-airplane')
                ->modalHeading('Invite a New Admin')
                ->modalDescription('Send an email invitation to create an admin account')
                ->form([
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required(),

                    Select::make('role')
                        ->label('Role')
                        ->options([
                            'writer' => 'Writer',
                            'editor' => 'Editor',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $existingUser = User::where('email', $data['email'])->first();

                    if ($existingUser) {
                        $existingUser->assignRole($data['role']);

                        Notification::make()
                            ->title('Role assigned')
                            ->body("{$existingUser->email} has been assigned as {$data['role']}")
                            ->success()
                            ->send();

                        return;
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
                }),
            CreateAction::make(),
        ];
    }
}
