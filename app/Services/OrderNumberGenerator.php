<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderNumberCounter;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class OrderNumberGenerator
{
    public function generate(?CarbonInterface $date = null): string
    {
        $date ??= now();
        [$pattern, $padding] = $this->resolvedFormat();

        return DB::transaction(function () use ($pattern, $padding, $date) {
            $counter = $this->lockCounter(OrderNumberFormat::counterKey($pattern, $date));

            do {
                $counter->value++;
                $number = OrderNumberFormat::render($pattern, $padding, $date, $counter->value);
            } while (Order::query()->where('order_number', $number)->exists());

            $counter->save();

            return $number;
        });
    }

    /**
     * @return list<string>
     */
    public function preview(int $count = 3, ?string $pattern = null, ?int $padding = null, ?CarbonInterface $date = null): array
    {
        $date ??= now();
        $pattern ??= $this->pattern();
        $padding ??= $this->padding();

        if (OrderNumberFormat::validate($pattern, $padding) !== null) {
            return [];
        }

        $last = (int) OrderNumberCounter::query()
            ->where('key', OrderNumberFormat::counterKey($pattern, $date))
            ->value('value');

        return array_map(
            fn (int $sequence): string => OrderNumberFormat::render($pattern, $padding, $date, $last + $sequence),
            range(1, $count),
        );
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function resolvedFormat(): array
    {
        $pattern = $this->pattern();
        $padding = $this->padding();

        if (OrderNumberFormat::validate($pattern, $padding) === null) {
            return [$pattern, $padding];
        }

        report(new RuntimeException("Invalid order number format [{$pattern}] with padding [{$padding}]; falling back to defaults."));

        return [OrderNumberFormat::DEFAULT_PATTERN, OrderNumberFormat::DEFAULT_PADDING];
    }

    private function pattern(): string
    {
        $pattern = setting('order_number_format');

        return is_string($pattern) && $pattern !== '' ? $pattern : OrderNumberFormat::DEFAULT_PATTERN;
    }

    private function padding(): int
    {
        $padding = setting('order_number_padding');

        return is_numeric($padding) ? (int) $padding : OrderNumberFormat::DEFAULT_PADDING;
    }

    private function lockCounter(string $key): OrderNumberCounter
    {
        try {
            OrderNumberCounter::query()->firstOrCreate(['key' => $key], ['value' => 0]);
        } catch (UniqueConstraintViolationException) {
            // Another request created the row first; the lock below picks it up.
        }

        return OrderNumberCounter::query()
            ->where('key', $key)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
