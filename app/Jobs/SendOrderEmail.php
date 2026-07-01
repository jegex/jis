<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendOrderEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $type,
    ) {}

    public function handle(EmailService $emailService): void
    {
        if ($this->type === 'confirmation') {
            $emailService->sendOrderConfirmation($this->order);
        } elseif ($this->type === 'download') {
            $emailService->sendDownloadLink($this->order);
        }
    }
}
