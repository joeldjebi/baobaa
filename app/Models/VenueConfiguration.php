<?php

namespace App\Models;

use Database\Factories\VenueConfigurationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueConfiguration extends Model
{
    /** @use HasFactory<VenueConfigurationFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'capacity',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
