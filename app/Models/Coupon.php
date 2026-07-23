<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CouponAppliesTo;
use App\Enums\CouponType;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $hidden = ['code'];

    protected $fillable = [
        'code',
        'type',
        'value',
        'applies_to',
        'product_id',
        'max_uses',
        'used_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'applies_to' => CouponAppliesTo::class,
            'value' => MoneyCast::class,
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('published');
    }

    public function orders(): HasMany|self
    {
        return $this->hasMany(Order::class);
    }

    public function isValid(): bool
    {
        if ($this->max_uses !== null && $this->max_uses !== 0 && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
