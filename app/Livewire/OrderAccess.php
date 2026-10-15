<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\DownloadService;
use App\Services\OrderAccessService;
use Livewire\Component;

final class OrderAccess extends Component
{
    public ?Order $order = null;

    public string $accessToken = '';

    public function mount(string $token): void
    {
        $this->accessToken = $token;

        $order = app(OrderAccessService::class)->resolve($token);

        if (! $order || $order->status !== OrderStatus::Paid) {
            abort(404);
        }

        $this->order = $order->load('items.product.media', 'invoice', 'currency');
    }

    public function downloadUrl(int $productId): string
    {
        $product = $this->order?->items->firstWhere('product_id', $productId)?->product;

        if (! $this->order instanceof Order || ! $product instanceof Product) {
            return '#';
        }

        $downloadService = app(DownloadService::class);

        if (! $downloadService->canDownload($this->order, $product)) {
            return '#';
        }

        return $downloadService->generateDownloadUrl($this->order, $product, $this->accessToken);
    }

    public function render()
    {
        return view('livewire.order-access')->layout('layouts.app');
    }
}
