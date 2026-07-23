<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Currency;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class OrderStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Order Stats';

    public static function canView(): bool
    {
        return auth()->user()->hasPermissionTo('View:OrderStatsOverview');
    }

    /**
     * @return string|null
     */
    public function getHeading(): ?string
    {
        return $this->heading;
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $currency = Currency::getDefault();
        $decimalPlaces = $currency?->decimal_place ?? 0;
        $symbol = $currency?->symbol ?? 'Rp';
        $divisor = 10 ** $decimalPlaces;
        $format = fn (float $value): string => $symbol.' '.number_format($value, $decimalPlaces, ',', '.');

        $now = now();
        $startThisMonth = (clone $now)->startOfMonth();
        $startLastMonth = (clone $now)->subMonth()->startOfMonth();
        $endLastMonth = (clone $now)->subMonth()->endOfMonth();

        $paidStatus = OrderStatus::Paid;

        $totalRevenueRaw = Order::where('status', $paidStatus)->sum('total');
        $thisMonthRevenueRaw = Order::where('status', $paidStatus)->where('paid_at', '>=', $startThisMonth)->sum('total');
        $lastMonthRevenueRaw = Order::where('status', $paidStatus)->whereBetween('paid_at', [$startLastMonth, $endLastMonth])->sum('total');

        $totalRevenue = $totalRevenueRaw / $divisor;
        $thisMonthRevenue = $thisMonthRevenueRaw / $divisor;
        $lastMonthRevenue = $lastMonthRevenueRaw / $divisor;

        $revenueChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100 : 0);

        $totalOrders = Order::count();
        $thisMonthOrders = Order::where('created_at', '>=', $startThisMonth)->count();

        $pendingCount = Order::whereIn('status', [OrderStatus::Pending, OrderStatus::AwaitingPayment])->count();

        $paidOrdersCount = Order::where('status', $paidStatus)->count();
        $aov = $paidOrdersCount > 0 ? ($totalRevenue / $paidOrdersCount) : 0;

        $sparkline = collect(range(6, 0))->map(fn ($day) => Order::where('status', $paidStatus)
            ->whereDate('paid_at', today()->subDays($day))
            ->sum('total') / $divisor
        )->values()->toArray();

        return [
            Stat::make('Total Revenue', $format($totalRevenue))
                ->description(($revenueChange >= 0 ? '+' : '')."{$revenueChange}% from last month")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($sparkline)
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make('Total Orders', (string) $totalOrders)
                ->description("+{$thisMonthOrders} this month")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Pending Orders', (string) $pendingCount)
                ->description('Awaiting payment confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Average Order Value', $format($aov))
                ->description("From {$paidOrdersCount} paid orders")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
