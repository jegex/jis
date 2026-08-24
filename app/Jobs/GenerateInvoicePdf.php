<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\InvoicePdfGenerator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class GenerateInvoicePdf implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public Order $order,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->order->id;
    }

    public function handle(InvoicePdfGenerator $generator): void
    {
        $order = $this->order->refresh();

        if ($order === null || $order->status !== OrderStatus::Paid) {
            return;
        }

        $generator->generate($order);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Invoice PDF generation failed permanently', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'error' => $exception?->getMessage(),
        ]);

        $adminEmail = setting('contact_email') ?: config('mail.from.address');

        if (! $adminEmail) {
            return;
        }

        Mail::raw(
            __('Failed to generate invoice PDF for order :number after :tries attempts.', [
                'number' => $this->order->order_number,
                'tries' => $this->tries,
            ]),
            function ($message) use ($adminEmail): void {
                $message->to($adminEmail)
                    ->subject(__('Invoice generation failed - Order :number', [
                        'number' => $this->order->order_number,
                    ]));
            },
        );
    }
}
