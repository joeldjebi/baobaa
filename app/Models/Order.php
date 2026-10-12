<?php

namespace App\Models;

use App\Enums\DepositStatus;
use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\ProductMode;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'reference',
        'client_id',
        'service_provider_profile_id',
        'status',
        'fulfillment_method',
        'delivery_city',
        'delivery_address',
        'delivery_date',
        'items_total',
        'delivery_fee',
        'deposit_total',
        'total_amount',
        'commission_amount',
        'currency',
        'payment_method',
        'deposit_status',
        'deposit_retained_amount',
        'deposit_retention_reason',
        'client_notes',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'fulfillment_method' => FulfillmentMethod::class,
            'deposit_status' => DepositStatus::class,
            'delivery_date' => 'date',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function serviceProviderProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderProfile::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function hasRentals(): bool
    {
        return $this->items->contains(fn (OrderItem $item): bool => $item->mode === ProductMode::Rental);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }
}
