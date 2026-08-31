<?php

namespace App\Models;

use Database\Factories\BookingCommissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCommission extends Model
{
    /** @use HasFactory<BookingCommissionFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'commission_rule_id',
        'commission_type',
        'percentage_rate',
        'fixed_amount',
        'base_amount',
        'commission_amount',
        'currency',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'percentage_rate' => 'decimal:2',
            'snapshot' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }
}
