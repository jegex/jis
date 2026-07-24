<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Invitation;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendInvitationEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public Invitation $invitation,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->invitation->id;
    }

    public function handle(EmailService $emailService): void
    {
        $emailService->sendInvitationEmail($this->invitation);
    }
}
