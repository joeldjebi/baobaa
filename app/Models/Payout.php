<?php

namespace App\Models;

use Database\Factories\PayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    /** @use HasFactory<PayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_profile_id',
        'booking_id',
        'reference',
        'status',
        'gross_amount',
        'commission_amount',
        'net_amount',
        'currency',
        'provider',
        'provider_reference',
        'scheduled_on',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_on' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
