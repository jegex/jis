<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Order;
use App\Services\DownloadService;
use Livewire\Component;

final class CustomerDownloads extends Component
{
    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->paid()
            ->with('items.product', 'items.product.media', 'invoice')
            ->latest('paid_at')
            ->get();

        return view('livewire.customer-downloads', compact('orders'))
            ->layout('layouts.app');
    }

    public function getDownloadUrl($orderId, $productId)
    {
        $order = property_exists($this, 'orders')
            ? $this->orders->firstWhere('id', $orderId)
            : null;

        if (! $order) {
            $order = Order::where('id', $orderId)
                ->where('user_id', auth()->id())
                ->paid()
                ->first();
        }

        if (! $order) {
            return '#';
        }

        $orderItem = $order->items()->where('product_id', $productId)->first();

        if (! $orderItem?->product) {
            return '#';
        }

        $downloadService = app(DownloadService::class);

        if (! $downloadService->canDownload($order, $orderItem->product)) {
            return '#';
        }

        return $downloadService->generateDownloadUrl($order, $orderItem->product);
    }
}
