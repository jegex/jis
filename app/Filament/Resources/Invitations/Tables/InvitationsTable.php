<?php

declare(strict_types=1);

namespace App\Filament\Resources\Invitations\Tables;

use App\Models\Invitation;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class InvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'writer' => 'info',
                        'editor' => 'success',
                    }),
                TextColumn::make('inviter.name')
                    ->label('Invited By'),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->color(fn (Invitation $record): string => $record->isExpired() ? 'danger' : 'success'
                    ),
                TextColumn::make('accepted_at')
                    ->label('Status')
                    ->state(fn (Invitation $record): string => $record->isAccepted() ? 'Accepted' :
                        ($record->isExpired() ? 'Expired' : 'Pending')
                    )
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Accepted' => 'success',
                        'Expired' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'writer' => 'Writer',
                        'editor' => 'Editor',
                    ]),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Invitation $record): void {
                        app(\App\Services\EmailService::class)->sendInvitationEmail($record);
                        Notification::make()
                            ->title('Invitation resent')
                            ->body("Invitation resent to {$record->email}")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                ActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
