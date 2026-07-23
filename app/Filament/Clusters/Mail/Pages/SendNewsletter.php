<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Pages;

use App\Enums\EmailTemplateType;
use App\Filament\Clusters\Mail\MailCluster;
use App\Jobs\SendNewsletterEmail;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class SendNewsletter extends Page
{
    public ?EmailTemplate $selectedTemplate = null;

    public array $preview = [];

    public array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected string $view = 'filament.pages.send-newsletter';

    protected static ?string $cluster = MailCluster::class;

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $permission = 'View:'.class_basename(self::class);

        return $user->hasPermissionTo($permission);
    }

    public static function getNavigationLabel(): string
    {
        return 'Send Newsletter';
    }

    public function mount(): void
    {
        $this->form->fill([
            'send_to_all' => true,
            'preview_locale' => 'en',
            'user_ids' => [],
        ]);
    }

    public function updated($propertyName, $value): void
    {
        if ($propertyName === 'data.template_id') {
            $this->generatePreview();
        }

        if ($propertyName === 'data.preview_locale') {
            $this->generatePreview();
        }

        if ($propertyName === 'data.send_to_all' && $value) {
            $this->data['user_ids'] = [];
        }
    }

    public function generatePreview(): void
    {
        $this->selectedTemplate = EmailTemplate::find($this->data['template_id'] ?? null);

        if (! $this->selectedTemplate) {
            $this->preview = [];

            return;
        }

        $emailService = app(EmailService::class);

        $variables = [
            'customer_name' => 'John Doe',
            'site_title' => config('app.name'),
            'newsletter_content' => 'This is a sample newsletter content.',
        ];

        $locale = $this->data['preview_locale'] ?? 'en';

        $this->preview = [
            'subject' => $emailService->parseTemplate(
                $this->selectedTemplate->getTranslation('subject', $locale),
                $variables,
            ),
            'body' => $emailService->parseTemplate(
                $this->selectedTemplate->getTranslation('body', $locale),
                $variables,
            ),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Select::make('template_id')
                    ->label('Email Template')
                    ->options(fn () => EmailTemplate::where('type', EmailTemplateType::Newsletter)
                        ->where('is_active', true)
                        ->get()
                        ->mapWithKeys(fn ($template) => [
                            $template->id => $template->getTranslation('subject', app()->getLocale()),
                        ]))
                    ->required()
                    ->live(),

                Toggle::make('send_to_all')
                    ->label('Send to all users')
                    ->default(true)
                    ->live(),

                Select::make('user_ids')
                    ->label('Select users')
                    ->options(User::pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->hidden(fn (Get $get): bool => $get('send_to_all')),

                Select::make('preview_locale')
                    ->label('Preview Language')
                    ->options([
                        'en' => 'English',
                        'id' => 'Indonesian',
                    ])
                    ->default('en')
                    ->live(),
            ]);
    }

    public function send(): void
    {
        $templateId = $this->data['template_id'] ?? null;
        $template = $templateId ? EmailTemplate::find($templateId) : null;

        if (! $template) {
            Notification::make()
                ->title('Please select a template')
                ->danger()
                ->send();

            return;
        }

        $sendToAll = $this->data['send_to_all'] ?? true;
        $users = $sendToAll
            ? User::all()
            : User::whereIn('id', $this->data['user_ids'] ?? [])->get();

        if ($users->isEmpty()) {
            Notification::make()
                ->title('No users selected')
                ->warning()
                ->send();

            return;
        }

        $count = 0;

        foreach ($users as $user) {
            SendNewsletterEmail::dispatch($user, $template);
            $count++;
        }

        Notification::make()
            ->title("Newsletter queued for {$count} user(s)")
            ->success()
            ->send();

        $this->selectedTemplate = null;
        $this->preview = [];

        $this->form->fill([
            'send_to_all' => true,
            'preview_locale' => 'en',
            'user_ids' => [],
        ]);
    }

    public function getTitle(): string
    {
        return 'Send Newsletter';
    }
}
