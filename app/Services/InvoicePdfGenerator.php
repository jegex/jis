<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Currency;
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

    public const DUE_DAYS_DEFAULT = 14;

    public const TAX_PERCENT_DEFAULT = 0;

    public function __construct(
        private InvoiceNumberGenerator $numberGenerator,
        private AmountInWords $amountInWords,
    ) {}

    public function generate(Order $order): Invoice
    {
        $invoice = $order->invoice()->first();

        if ($invoice && $this->hasStoredPdf($order)) {
            return $invoice;
        }

        $invoice ??= Invoice::create([
            'order_id' => $order->id,
            'number' => $this->numberGenerator->generate($order->paid_at ?? now()),
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

        $invoice = $order->invoice;
        $issuedAt = $invoice?->issued_at ?? $order->paid_at ?? $order->created_at ?? now();
        $dueDays = (int) (setting('invoice_due_days') ?? self::DUE_DAYS_DEFAULT);
        $locale = $order->locale ?? config('app.fallback_locale');
        $totals = $this->totals($order);

        return [
            'order' => $order,
            'invoice' => $invoice,
            'issuedAt' => $issuedAt,
            'dueDate' => $issuedAt->copy()->addDays($dueDays),
            'quotationNumber' => $order->quotation_number,
            'poNumber' => $order->po_number,
            'seller' => $this->seller(),
            'logoData' => $this->logoData(),
            'footerNote' => (string) setting('invoice_footer_note', ''),
            'couponCode' => $order->discounts->first()?->coupon_snapshot['code'],
            'totals' => $totals,
            'totalInWords' => $this->amountInWords->toWords($totals['total'], $order->currency_code, $locale),
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
            'locale' => $locale,
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
     * @return array{subtotal: float, discount: float, base: float, downPaymentPercent: float|null, downPayment: float, taxPercent: float, tax: float, total: float}
     */
    private function totals(Order $order): array
    {
        $decimalPlaces = (int) (Currency::where('code', $order->currency_code)->value('decimal_place') ?? 0);

        $subtotal = round((float) $order->subtotal, $decimalPlaces);
        $discount = round((float) $order->discount, $decimalPlaces);
        $base = round($subtotal - $discount, $decimalPlaces);

        $downPaymentPercent = $order->down_payment_percent;
        $downPaymentPercent = ($downPaymentPercent === null || $downPaymentPercent <= 0)
            ? null
            : $downPaymentPercent;

        $downPayment = $downPaymentPercent === null
            ? $base
            : round($base * $downPaymentPercent / 100, $decimalPlaces);

        $taxPercent = (float) (setting('invoice_tax_percent') ?? self::TAX_PERCENT_DEFAULT);
        $tax = round($base * $taxPercent / 100, $decimalPlaces);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'base' => $base,
            'downPaymentPercent' => $downPaymentPercent,
            'downPayment' => $downPayment,
            'taxPercent' => $taxPercent,
            'tax' => $tax,
            'total' => round($downPayment + $tax, $decimalPlaces),
        ];
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
