<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderDiscount extends Model
{
    protected $fillable = [
        'order_id',
        'coupon_id',
        'coupon_snapshot',
        'amount',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'coupon_snapshot' => 'json',
            'amount' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
