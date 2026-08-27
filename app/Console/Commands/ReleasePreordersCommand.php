<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class ReleasePreordersCommand extends Command
{
    protected $signature = 'preorders:release';

    protected $description = 'Release preorder products and notify customers';

    public function handle(EmailService $emailService): int
    {
        $orders = Order::query()
            ->where('status', 'paid')
            ->whereNull('preorder_released_at')
            ->whereHas('items.product', function ($query) {
                $query->whereNotNull('release_date')
                    ->where('release_date', '<=', Carbon::now());
            })
            ->with('items.product')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No preorders to release.');

            return Command::SUCCESS;
        }

        $released = 0;

        foreach ($orders as $order) {
            $releasedItems = $order->items->filter(function ($item) {
                $product = $item->product;

                return $product && $product->release_date !== null && $product->release_date->isPast();
            });

            if ($releasedItems->isEmpty()) {
                continue;
            }

            $emailService->sendPreorderRelease($order);

            $order->update(['preorder_released_at' => now()]);

            $released++;

            $productNames = $releasedItems->pluck('product_name')->implode(', ');
            $this->line("Released: {$order->order_number} ({$productNames})");
        }

        $this->info("Done. Released {$released} preorder(s).");

        return Command::SUCCESS;
    }
}
