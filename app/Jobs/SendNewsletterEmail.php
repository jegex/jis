<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendNewsletterEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public EmailTemplate $template,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $emailService->sendNewsletter($this->user, $this->template);
    }
}
