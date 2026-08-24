<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\InvoicePdfGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class BackfillInvoicesCommand extends Command
{
    protected $signature = 'invoices:backfill {--dry-run : Only show orders that would get an invoice}';

    protected $description = 'Generate invoices for paid orders that do not have one yet';

    public function handle(InvoicePdfGenerator $generator): int
    {
        $query = Order::query()
            ->where('status', OrderStatus::Paid)
            ->whereDoesntHave('invoice');

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('No paid orders without an invoice. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} paid order(s) without an invoice.");

        if ($this->option('dry-run')) {
            (clone $query)
                ->orderBy('paid_at')
                ->get(['id', 'order_number', 'paid_at'])
                ->each(fn (Order $order) => $this->line("  - {$order->order_number} ({$order->paid_at?->format('Y-m-d')})"));

            return self::SUCCESS;
        }

        $generated = 0;
        $failed = 0;

        $query
            ->with('items')
            ->orderBy('paid_at')
            ->chunkById(50, function ($orders) use ($generator, &$generated, &$failed): void {
                foreach ($orders as $order) {
                    try {
                        DB::transaction(fn () => $generator->generate($order));

                        $generated++;
                        $this->info("  [OK] {$order->order_number}");
                    } catch (Throwable $exception) {
                        $failed++;
                        report($exception);
                        $this->error("  [FAIL] {$order->order_number}: {$exception->getMessage()}");
                    }
                }
            });

        $this->newLine();
        $this->info("Done. Generated: {$generated}, Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
