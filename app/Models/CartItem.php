<?php

namespace App\Models;

use App\Enums\ProductMode;
use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'mode',
        'quantity',
        'rental_starts_on',
        'rental_ends_on',
    ];

    protected function casts(): array
    {
        return [
            'mode' => ProductMode::class,
            'rental_starts_on' => 'date',
            'rental_ends_on' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Number of billed rental days, both dates included.
     */
    public function rentalDays(): int
    {
        if ($this->mode !== ProductMode::Rental || ! $this->rental_starts_on || ! $this->rental_ends_on) {
            return 0;
        }

        return (int) $this->rental_starts_on->diffInDays($this->rental_ends_on) + 1;
    }

    public function lineTotal(): int
    {
        $unitPrice = $this->product?->unitPriceFor($this->mode) ?? 0;

        return $this->mode === ProductMode::Rental
            ? $unitPrice * $this->quantity * max(1, $this->rentalDays())
            : $unitPrice * $this->quantity;
    }

    public function depositTotal(): int
    {
        return $this->mode === ProductMode::Rental ? (int) ($this->product?->deposit_amount ?? 0) * $this->quantity : 0;
    }
}
