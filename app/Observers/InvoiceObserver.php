<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Invoice;
use App\Services\InvoicePdfGenerator;

final class InvoiceObserver
{
    public function updated(Invoice $invoice): void
    {
        if (! $invoice->wasChanged(['number', 'issued_at'])) {
            return;
        }

        $order = $invoice->order;

        if ($order === null) {
            return;
        }

        app(InvoicePdfGenerator::class)->regenerate($order);
    }

    public function deleted(Invoice $invoice): void
    {
        $invoice->order?->clearMediaCollection(InvoicePdfGenerator::MEDIA_COLLECTION);
    }
}
