<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Currency;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Throwable;

final class RevenueChart extends ChartWidget
{
    public ?string $filter = '30';

    protected static ?int $sort = 3;

    protected ?string $heading = 'Revenue';

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        try {
            return auth()->user()->hasPermissionTo('View:RevenueChart');
        } catch (Throwable) {
            return false;
        }
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Days',
            '30' => '30 Days',
            '90' => '90 Days',
            '365' => 'This Year',
        ];
    }

    protected function getData(): array
    {
        $currency = Currency::getDefault();
        $decimalPlaces = $currency?->decimal_place ?? 0;
        $symbol = $currency?->symbol ?? '$';
        $divisor = 10 ** $decimalPlaces;

        $days = (int) $this->filter;
        $startDate = now()->subDays($days)->startOfDay();

        $raw = Order::where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        $data = collect();
        $period = now()->subDays($days)->copy();

        while ($period->lte(now())) {
            $date = $period->format('Y-m-d');
            $data->push([
                'date' => $period->format('d M'),
                'revenue' => isset($raw[$date]) ? round($raw[$date] / $divisor, $decimalPlaces) : 0,
            ]);
            $period->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => "Revenue ({$symbol})",
                    'data' => $data->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => '#22c55e',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
