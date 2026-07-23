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
            ->where('status', 'paid')
            ->with('items.product', 'items.product.media')
            ->latest('paid_at')
            ->get();

        return view('livewire.customer-downloads', compact('orders'))
            ->layout('layouts.app');
    }

    public function getDownloadUrl($orderId, $productId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $order) {
            return '#';
        }

        $orderItem = $order->items()->where('product_id', $productId)->first();

        if (! $orderItem?->product) {
            return '#';
        }

        return app(DownloadService::class)->generateDownloadUrl($order, $orderItem->product);
    }
}
