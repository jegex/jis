<?php

declare(strict_types=1);

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Filament\Resources\Admins\Schemas\AdminForm;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SensitiveParameter;

final class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...AdminForm::getBaseForm(),
                TextInput::make('password')
                    ->label(__('filament-panels::auth/pages/register.form.password.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->rule(Password::default())
                    ->showAllValidationMessages()
                    ->dehydrateStateUsing(fn (#[SensitiveParameter] $state) => Hash::make($state))
                    ->same('passwordConfirmation')
                    ->validationAttribute(__('filament-panels::auth/pages/register.form.password.validation_attribute')),
                TextInput::make('passwordConfirmation')
                    ->label(__('filament-panels::auth/pages/register.form.password_confirmation.label'))
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->dehydrated(false),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! isset($data['is_admin'])) {
            $data['is_admin'] = true;
        }

        if (! isset($data['locale'])) {
            $data['locale'] = 'en';
        }

        if (! isset($data['admin_locale'])) {
            $data['admin_locale'] = 'en';
        }

        return $data;
    }
}
