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
            $product = $order->items->first()?->product;

            if (! $product) {
                continue;
            }

            $emailService->sendPreorderRelease($order);

            $order->update(['preorder_released_at' => now()]);

            $released++;

            $this->line("Released: {$order->order_number} ({$product->title})");
        }

        $this->info("Done. Released {$released} preorder(s).");

        return Command::SUCCESS;
    }
}
