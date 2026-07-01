<?php

declare(strict_types=1);

namespace App\Payments;

use App\Models\Order;

interface PaymentGateway
{
    public function setConfig(array $config): static;

    public function charge(Order $order, array $params = []): PaymentResult;

    public function callback(array $data): PaymentNotification;

    public function checkStatus(string $transactionId): PaymentStatusDto;
}
