<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendEmailVerification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $userId,
        public string $verificationUrl,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->userId;
    }

    public function handle(EmailService $emailService): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $emailService->sendEmailVerificationLink($user, $this->verificationUrl);
    }
}
