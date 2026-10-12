<?php

namespace App\Models;

use App\Enums\ProductMode;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'mode',
        'quantity',
        'unit_price',
        'rental_days',
        'rental_starts_on',
        'rental_ends_on',
        'line_total',
        'deposit_amount',
    ];

    protected function casts(): array
    {
        return [
            'mode' => ProductMode::class,
            'rental_starts_on' => 'date',
            'rental_ends_on' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
