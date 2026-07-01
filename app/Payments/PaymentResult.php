<?php

declare(strict_types=1);

namespace App\Payments;

final class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly string $redirectUrl,
        public readonly ?string $snapToken = null,
        public readonly ?string $errorMessage = null,
        public readonly array $rawResponse = [],
    ) {}
}
