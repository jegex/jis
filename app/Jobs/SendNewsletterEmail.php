<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendNewsletterEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public User $user,
        public EmailTemplate $template,
    ) {}

    public function uniqueId(): string
    {
        return $this->user->id.'-'.$this->template->id;
    }

    public function handle(EmailService $emailService): void
    {
        $emailService->sendNewsletter($this->user, $this->template);
    }
}
