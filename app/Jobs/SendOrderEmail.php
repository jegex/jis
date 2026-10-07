<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EmailTemplateType;
use App\Models\Order;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendOrderEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public Order $order,
        public EmailTemplateType $type,
    ) {}

    public function handle(EmailService $emailService): void
    {
        match ($this->type) {
            EmailTemplateType::OrderConfirmation => $emailService->sendOrderConfirmation($this->order),
            EmailTemplateType::DownloadLink => $emailService->sendDownloadLink($this->order),
            default => null,
        };
    }
}
