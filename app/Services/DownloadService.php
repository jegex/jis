<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;

final class DownloadService
{
    public function __construct(
        private OrderAccessService $orderAccessService,
    ) {}

    public function generateDownloadUrl(Order $order, Product $product, ?string $accessToken = null): string
    {
        $parameters = [
            'order' => $order->order_number,
            'product' => $product->id,
        ];

        if ($accessToken !== null) {
            $parameters['token'] = $accessToken;
        }

        return route('payment.download', $parameters);
    }

    /**
     * Issue a fresh Order Access Token and return its plaintext.
     *
     * Only the hash is persisted, so a previously issued plaintext cannot be
     * recovered; every new emailed link therefore rotates the token and
     * invalidates previously sent links (per ADR 0002).
     */
    public function issueAccessToken(Order $order): string
    {
        return $this->orderAccessService->issue($order);
    }

    public function canDownload(Order $order, Product $product): bool
    {
        if ($order->status->value !== 'paid') {
            return false;
        }

        return $order->items()->where('product_id', $product->id)->exists();
    }
}
