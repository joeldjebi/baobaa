<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'owner_profile_id',
        'venue_id',
        'venue_rate_id',
        'venue_availability_id',
        'event_project_item_id',
        'reference',
        'status',
        'booking_mode',
        'event_type',
        'event_date',
        'starts_at',
        'ends_at',
        'guests_count',
        'currency',
        'total_amount',
        'reservation_amount',
        'client_notes',
        'expires_at',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'event_date' => 'date',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(VenueRate::class, 'venue_rate_id');
    }

    public function eventProjectItem(): BelongsTo
    {
        return $this->belongsTo(EventProjectItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BookingMessage::class);
    }

    public function proformaInvoice(): HasOne
    {
        return $this->hasOne(ProformaInvoice::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(BookingCommission::class);
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(VenueReview::class);
    }
}
