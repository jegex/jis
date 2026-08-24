<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class InvoicePdfGenerator
{
    public const MEDIA_COLLECTION = 'invoices';

    public function __construct(
        private InvoiceNumberGenerator $numberGenerator,
    ) {}

    public function generate(Order $order): Invoice
    {
        $invoice = $order->invoice()->first();

        if ($invoice && $this->hasStoredPdf($order)) {
            return $invoice;
        }

        $invoice ??= Invoice::create([
            'order_id' => $order->id,
            'number' => $this->numberGenerator->next($order->paid_at ?? now()),
            'issued_at' => $order->paid_at ?? now(),
        ]);

        $pdf = $this->render($order);

        $order->addMediaFromString($pdf)
            ->usingFileName($invoice->fileName())
            ->usingName($invoice->fileName())
            ->toMediaCollection(self::MEDIA_COLLECTION, 'local');

        return $invoice;
    }

    public function render(Order $order): string
    {
        $previousLocale = App::getLocale();
        App::setLocale($order->locale ?? config('app.fallback_locale'));

        try {
            return Pdf::loadView('invoice.pdf', $this->buildViewData($order))->output();
        } finally {
            App::setLocale($previousLocale);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(Order $order): array
    {
        $order->loadMissing(['items', 'discounts', 'payments']);

        return [
            'order' => $order,
            'invoice' => $order->invoice,
            'seller' => $this->seller(),
            'logoData' => $this->logoData(),
            'footerNote' => (string) setting('invoice_footer_note', ''),
            'couponCode' => $order->discounts->first()?->coupon_snapshot['code'],
            'payment' => $order->payments
                ->where('status', PaymentStatus::Success)
                ->sortByDesc('paid_at')
                ->first(),
            'customer' => [
                'name' => $order->user?->name ?? $order->guest_name ?? '-',
                'email' => $order->user?->email ?? $order->guest_email ?? '-',
                'address' => $order->user?->address ?? '-',
                'phone' => $order->user?->phone ?? '-',
            ],
            'locale' => $order->locale ?? config('app.fallback_locale'),
        ];
    }

    public function storedPdf(Order $order): ?Media
    {
        /** @var Media|null $media */
        $media = $order->media()
            ->where('collection_name', self::MEDIA_COLLECTION)
            ->orderByDesc('id')
            ->first();

        return $media;
    }

    /**
     * @return array{name: string, address: string, phone: string, email: string, npwp: string}
     */
    private function seller(): array
    {
        return [
            'name' => (string) setting('invoice_company_name', config('app.name')),
            'address' => (string) setting('invoice_company_address', ''),
            'phone' => (string) setting('invoice_company_phone', ''),
            'email' => (string) setting('invoice_company_email', ''),
            'npwp' => (string) setting('invoice_npwp', ''),
        ];
    }

    private function logoData(): ?string
    {
        $value = setting('invoice_logo');

        if (! is_string($value) || $value === '') {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($value)) {
            return null;
        }

        return sprintf(
            'data:%s;base64,%s',
            (string) $disk->mimeType($value),
            base64_encode((string) $disk->get($value)),
        );
    }

    private function hasStoredPdf(Order&HasMedia $order): bool
    {
        return $order->media()
            ->where('collection_name', self::MEDIA_COLLECTION)
            ->exists();
    }
}
