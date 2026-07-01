<?php

declare(strict_types=1);

namespace App\Providers;

use App\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('payment', function ($app) {
            return new PaymentManager($app);
        });
    }
}
