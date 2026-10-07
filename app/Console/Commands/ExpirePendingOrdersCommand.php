<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

final class ExpirePendingOrdersCommand extends Command
{
    protected $signature = 'orders:expire-pending';

    protected $description = 'Expire unpaid orders older than two hours';

    public function handle(): int
    {
        $count = Order::query()
            ->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::AwaitingPayment,
                OrderStatus::CreatingPayment,
            ])
            ->where('created_at', '<', now()->subHours(2))
            ->update(['status' => OrderStatus::Expired]);

        $this->info("Expired {$count} unpaid order(s).");

        return self::SUCCESS;
    }
}
