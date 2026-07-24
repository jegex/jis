<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Throwable;

final class OrdersByStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Orders by Status';

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        try {
            return auth()->user()->hasPermissionTo('View:OrdersByStatusChart');
        } catch (Throwable) {
            return false;
        }
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    protected function getData(): array
    {
        $statuses = OrderStatus::cases();

        $data = collect($statuses)->map(fn (OrderStatus $status) => Order::where('status', $status)->count())->toArray();

        $labels = collect($statuses)->map(fn (OrderStatus $status) => $status->getLabel())->toArray();

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#f59e0b', '#9ca3af', '#f59e0b', '#22c55e', '#ef4444', '#9ca3af', '#06b6d4'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
