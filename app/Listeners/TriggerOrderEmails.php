<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\EmailTemplateType;
use App\Events\PaymentSuccess;
use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendOrderEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

final class TriggerOrderEmails implements ShouldQueue
{
    public function handle(PaymentSuccess $event): void
    {
        $lock = Cache::lock('emails-chain:'.$event->order->getKey(), 600);

        if (! $lock->get()) {
            return;
        }

        Bus::chain([
            new GenerateInvoicePdf($event->order),
            new SendOrderEmail($event->order, EmailTemplateType::OrderConfirmation),
            new SendOrderEmail($event->order, EmailTemplateType::DownloadLink),
        ])->dispatch();
    }
}
