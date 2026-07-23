<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $hidden = ['guest_email', 'guest_name'];

    protected $fillable = [
        'order_number',
        'user_id',
        'guest_email',
        'guest_name',
        'currency_code',
        'payment_currency_code',
        'subtotal',
        'discount',
        'total',
        'exchange_rate',
        'coupon_id',
        'status',
        'paid_at',
        'notes',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => MoneyCast::class,
            'discount' => MoneyCast::class,
            'total' => MoneyCast::class,
            'paid_at' => 'datetime',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(OrderDiscount::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    #[Scope]
    protected function paid(Builder $query): void
    {
        $query->whereNotNull('paid_at');
    }
}
