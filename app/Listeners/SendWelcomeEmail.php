<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\EmailService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

final class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    public int $tries = 3;

    public function handle(Registered $event): void
    {
        app(EmailService::class)->sendWelcome($event->user);
    }
}
