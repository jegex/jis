<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\URL;

final class DownloadService
{
    public function generateDownloadUrl(Order $order, Product $product): string
    {
        $externalUrl = $product->externalDownloadUrl();

        if ($externalUrl !== null) {
            return $externalUrl;
        }

        if (config('checkout.allow_guest')) {
            return route('payment.download', [
                'order' => $order->order_number,
                'product' => $product->id,
            ]);
        }

        return URL::temporarySignedRoute(
            'payment.download',
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
