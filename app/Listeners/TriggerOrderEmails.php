<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\EmailTemplateType;
use App\Events\PaymentSuccess;
use App\Jobs\SendOrderEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

final class TriggerOrderEmails implements ShouldQueue
{
    public function handle(PaymentSuccess $event): void
    {
        SendOrderEmail::dispatch($event->order, EmailTemplateType::OrderConfirmation);
        SendOrderEmail::dispatch($event->order, EmailTemplateType::DownloadLink);
    }
}
