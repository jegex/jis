<?php

declare(strict_types=1);

namespace App\Payments;

final class PaymentNotification
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $transactionStatus,
        public readonly string $orderId,
        public readonly array $rawData = [],
    ) {}
}
