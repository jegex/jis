<?php

declare(strict_types=1);

namespace App\Payments;

final class PaymentStatusDto
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $status,
        public readonly ?string $message = null,
    ) {}
}
