<?php

namespace App\Models;

use Database\Factories\VenueReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueReview extends Model
{
    /** @use HasFactory<VenueReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'client_id',
        'booking_id',
        'rating',
        'title',
        'comment',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
