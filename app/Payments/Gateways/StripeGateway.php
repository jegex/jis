<?php

declare(strict_types=1);

namespace App\Payments\Gateways;

use App\Models\Order;
use App\Payments\PaymentGateway;
use App\Payments\PaymentNotification;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatusDto;
use RuntimeException;

final class StripeGateway implements PaymentGateway
{
    private array $config = [];

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function charge(Order $order, array $params = []): PaymentResult
    {
        throw new RuntimeException('Stripe gateway not yet implemented.');
    }

    public function callback(array $data): PaymentNotification
    {
        throw new RuntimeException('Stripe gateway not yet implemented.');
    }

    public function checkStatus(string $transactionId): PaymentStatusDto
    {
        throw new RuntimeException('Stripe gateway not yet implemented.');
    }
}
