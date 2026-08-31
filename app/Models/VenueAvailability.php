<?php

namespace App\Models;

use Database\Factories\VenueAvailabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueAvailability extends Model
{
    /** @use HasFactory<VenueAvailabilityFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'available_date',
        'starts_at',
        'ends_at',
        'status',
        'block_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'available_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
