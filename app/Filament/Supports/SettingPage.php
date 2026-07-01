<?php

declare(strict_types=1);

namespace App\Filament\Supports;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Arr;
use Throwable;

/**
 * @property-read Schema $form
 */
class SettingPage extends Page
{
    use CanUseDatabaseTransactions;
    use HasUnsavedDataChangesAlert;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static string $settings;

    public static function getSettings(): array
    {
        return Arr::undot(Setting::getAll());
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            foreach ($data as $key => $values) {
                Setting::set($key, $values);
            }

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->rememberData();

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    public function getSavedNotification(): ?Notification
    {
        $title = $this->getSavedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    public function getSavedNotificationTitle(): ?string
    {
        return __('filament-panels::resources/pages/edit-record.notifications.saved.title');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    public function getSaveFormAction(): Action
    {
        $hasFormWrapper = $this->hasFormWrapper();

        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit($hasFormWrapper ? $this->getSubmitFormLivewireMethodName() : null)
            ->action($hasFormWrapper ? null : $this->getSubmitFormLivewireMethodName())
            ->keyBindings(['mod+s'])
            ->visible($this->canEdit());
    }

    public function getSubmitFormAction(): Action
    {
        return $this->getSaveFormAction();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->disabled(! $this->canEdit())
            ->inlineLabel($this->hasInlineLabels())
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        if (! $this->hasFormWrapper()) {
            return Group::make([
                EmbeddedSchema::make('form'),
                $this->getFormActionsContentComponent(),
            ]);
        }

        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName())
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function getFormActionsContentComponent(): Component
    {
        return Actions::make($this->getFormActions())
            ->alignment($this->getFormActionsAlignment())
            ->fullWidth($this->hasFullWidthFormActions())
            ->sticky($this->areFormActionsSticky())
            ->key('form-actions');
    }

    public function hasFormWrapper(): bool
    {
        return true;
    }

    public function getRedirectUrl(): ?string
    {
        return null;
    }

    public function canEdit(): bool
    {
        return true;
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill(self::getSettings());

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function getSubmitFormLivewireMethodName(): string
    {
        return 'save';
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
