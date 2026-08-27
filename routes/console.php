<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:prune-failed --hours=48')->daily();
Schedule::command('queue:prune-batching --hours=48')->daily();

Schedule::command('preorders:release')->hourly();

Schedule::call(function () {
    Order::whereIn('status', [OrderStatus::Pending, OrderStatus::AwaitingPayment, OrderStatus::CreatingPayment])
        ->where('created_at', '<', now()->subHours(2))
        ->update(['status' => OrderStatus::Expired]);
})->everyFifteenMinutes();
