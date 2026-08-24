<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $variables
     * @param  array{name: string, contents: string}|null  $invoice
     */
    public function __construct(
        public string $recipient,
        public string $subjectTemplate,
        public string $bodyTemplate,
        public ?array $invoice = null,
        public array $variables = [],
    ) {}

    public function build(): static
    {
        $subjectLine = strtr($this->subjectTemplate, $this->toReplacements($this->variables));
        $bodyHtml = strtr($this->bodyTemplate, $this->toReplacements($this->variables));

        $this->to($this->recipient)
            ->subject($subjectLine)
            ->view('email.layout', [
                'subject' => $subjectLine,
                'body' => $bodyHtml,
            ]);

        if ($this->invoice !== null) {
            $this->attachData($this->invoice['contents'], $this->invoice['name'], [
                'mime' => 'application/pdf',
            ]);
        }

        return $this;
    }

    /**
     * @param  array<string, string>  $variables
     * @return array<string, string>
     */
    private function toReplacements(array $variables): array
    {
        return collect($variables)
            ->mapWithKeys(fn ($value, $key) => ['{'.$key.'}' => (string) $value])
            ->all();
    }
}
