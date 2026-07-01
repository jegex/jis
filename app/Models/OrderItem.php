<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;

final class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'quantity' => 'integer',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
