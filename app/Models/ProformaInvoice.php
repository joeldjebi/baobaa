<?php

namespace App\Models;

use App\Enums\ProformaInvoiceStatus;
use Database\Factories\ProformaInvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProformaInvoice extends Model
{
    /** @use HasFactory<ProformaInvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'reference',
        'status',
        'currency',
        'subtotal_amount',
        'deposit_amount',
        'service_fee_amount',
        'total_amount',
        'client_confirmed_at',
        'owner_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProformaInvoiceStatus::class,
            'client_confirmed_at' => 'datetime',
            'owner_confirmed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProformaInvoiceItem::class)->orderBy('sort_order');
    }
}
