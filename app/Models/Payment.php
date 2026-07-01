<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

final class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_transaction_id',
        'gateway_status',
        'snap_token',
        'redirect_url',
        'expires_at',
        'raw_response',
        'currency_code',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'raw_response' => 'json',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'amount' => MoneyCast::class,
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
