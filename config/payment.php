<?php

declare(strict_types=1);

use App\Payments\Gateways\MidtransGateway;
use App\Payments\Gateways\StripeGateway;
use App\Payments\Gateways\XenditGateway;

return [
    'default' => env('PAYMENT_GATEWAY', 'midtrans'),

    'default_currency' => env('DEFAULT_CURRENCY', 'IDR'),

    'types' => [
        'midtrans' => [
            'driver' => MidtransGateway::class,
            'currencies' => ['IDR'],
            'target_currency' => 'IDR',
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
            'enabled_payments' => ['credit_card'],
        ],
        'xendit' => [
            'driver' => XenditGateway::class,
            'currencies' => ['IDR', 'PHP'],
            'target_currency' => 'IDR',
        ],
        'stripe' => [
            'driver' => StripeGateway::class,
            'currencies' => ['USD', 'EUR', 'GBP', 'SGD', 'MYR'],
            'target_currency' => 'USD',
        ],
    ],
];
