<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EmailTemplateType;
use App\Models\Order;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendOrderEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public EmailTemplateType $type,
    ) {}

    public function uniqueId(): string
    {
        return $this->order->id.'-'.$this->type->value;
    }

    public function handle(EmailService $emailService): void
    {
        match ($this->type) {
            EmailTemplateType::OrderConfirmation => $emailService->sendOrderConfirmation($this->order),
            EmailTemplateType::DownloadLink => $emailService->sendDownloadLink($this->order),
            default => null,
        };
    }
}
