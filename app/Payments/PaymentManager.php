<?php

declare(strict_types=1);

namespace App\Payments;

use Illuminate\Support\Manager;
use Illuminate\Support\Str;

final class PaymentManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('payment.default', 'midtrans');
    }

    public function buildProvider(string $provider): PaymentGateway
    {
        return $this->container->make($provider);
    }

    protected function createDriver($driver): PaymentGateway
    {
        $originalDriver = $driver;

        $type = $this->config->get("payment.types.{$driver}");

        $driver = $type['driver'] ?? $originalDriver;

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        }

        $method = 'create'.Str::studly($driver).'Driver';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if ($type) {
            return $this->buildProvider($driver)->setConfig($type);
        }

        return parent::createDriver($originalDriver);
    }
}
