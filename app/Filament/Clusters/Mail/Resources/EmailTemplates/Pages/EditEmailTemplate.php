<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Mail\Resources\EmailTemplates\Pages;

use App\Filament\Clusters\Mail\Resources\EmailTemplates\EmailTemplateResource;
use App\Services\EmailService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

final class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),

            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->modalHeading('Email Template Preview')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(function () {
                    $record = $this->record;

                    return view('filament.email-preview', [
                        'record' => $record,
                    ]);
                }),

            Action::make('sendTest')
                ->label('Send Test')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->label('Recipient Email'),
                    Select::make('locale')
                        ->options([
                            'en' => 'English',
                            'id' => 'Indonesian',
                        ])
                        ->required()
                        ->default('en'),
                ])
                ->action(function (array $data): void {
                    $record = $this->record;
                    $locale = $data['locale'];
                    $recipient = $data['email'];

                    $variables = [
                        'customer_name' => 'John Doe',
                        'email' => $recipient,
                        'order_id' => '12345',
                        'product_name' => 'Sample Product',
                        'total' => 'Rp 100.000',
                        'download_url' => 'https://example.com/download/sample',
                        'site_title' => config('app.name'),
                        'newsletter_content' => 'This is a test newsletter content.',
                    ];

                    $subject = app(EmailService::class)->parseTemplate(
                        $record->getTranslation('subject', $locale),
                        $variables,
                    );
                    $body = app(EmailService::class)->parseTemplate(
                        $record->getTranslation('body', $locale),
                        $variables,
                    );

                    Mail::send('email.layout', [
                        'subject' => "[Test] {$subject}",
                        'body' => $body,
                    ], function ($message) use ($recipient, $subject) {
                        $message->to($recipient)
                            ->subject("[Test] {$subject}");
                    });

                    Notification::make()
                        ->title('Test email sent successfully!')
                        ->success()
                        ->send();
                }),
        ];
    }
}
