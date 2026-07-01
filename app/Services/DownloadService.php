<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\URL;

final class DownloadService
{
    public function generateDownloadUrl(Order $order, Product $product, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $route = $locale === 'en'
            ? 'without_locale.payment.download'
            : "translated_{$locale}.payment.download";

        return URL::temporarySignedRoute(
            $route,
            now()->addHours(24),
            [
                'order' => $order->order_number,
                'product' => $product->id,
            ]
        );
    }

    public function canDownload(Order $order, Product $product): bool
    {
        if ($order->status->value !== 'paid') {
            return false;
        }

        return $order->items()->where('product_id', $product->id)->exists();
    }
}
