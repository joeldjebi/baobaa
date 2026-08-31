<?php

namespace App\Models;

use Database\Factories\VenueAddOnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueAddOn extends Model
{
    /** @use HasFactory<VenueAddOnFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'description',
        'price',
        'currency',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
