<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Currency;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class SalesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    protected function getStats(): array
    {
        $currency = Currency::getDefault();
        $decimalPlaces = $currency?->decimal_place ?? 0;
        $symbol = $currency?->symbol ?? 'Rp';
        $divisor = 10 ** $decimalPlaces;
        $format = fn (float $value): string => $symbol.' '.number_format($value, $decimalPlaces, ',', '.');

        $paidStatus = OrderStatus::Paid;

        $ordersToday = Order::whereDate('created_at', today())->count();
        $orders7d = Order::where('created_at', '>=', now()->subDays(7))->count();
        $orders30d = Order::where('created_at', '>=', now()->subDays(30))->count();

        $salesTodayRaw = Order::where('status', $paidStatus)->whereDate('paid_at', today())->sum('total');
        $sales7dRaw = Order::where('status', $paidStatus)->where('paid_at', '>=', now()->subDays(7))->sum('total');
        $sales30dRaw = Order::where('status', $paidStatus)->where('paid_at', '>=', now()->subDays(30))->sum('total');

        return [
            Stat::make('Orders Today', (string) $ordersToday)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Orders (7 Days)', (string) $orders7d)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Orders (30 Days)', (string) $orders30d)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Sales Today', $format($salesTodayRaw / $divisor))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Sales (7 Days)', $format($sales7dRaw / $divisor))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Sales (30 Days)', $format($sales30dRaw / $divisor))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
