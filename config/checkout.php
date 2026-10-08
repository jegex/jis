<?php

declare(strict_types=1);

return [
    'allow_guest' => env('CHECKOUT_ALLOW_GUEST', false),
    'max_quantity' => env('CHECKOUT_MAX_QUANTITY', 99),
];
