<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\EmailService;
use Illuminate\Auth\Events\Registered;

final class SendWelcomeEmail
{
    public function handle(Registered $event): void
    {
        app(EmailService::class)->sendWelcome($event->user);
    }
}
