<?php

namespace App\Models;

use Database\Factories\VenueRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueRate extends Model
{
    /** @use HasFactory<VenueRateFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'rate_type',
        'price',
        'currency',
        'min_hours',
        'max_hours',
        'min_guests',
        'max_guests',
        'is_default',
        'is_active',
        'conditions',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
