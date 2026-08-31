<?php

namespace App\Models;

use App\Services\VenueImageService;
use Database\Factories\VenueMediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueMedia extends Model
{
    /** @use HasFactory<VenueMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'type',
        'disk',
        'path',
        'alt_text',
        'is_primary',
        'sort_order',
        'moderation_status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    protected function signedUrl(): Attribute
    {
        return Attribute::get(fn (): string => app(VenueImageService::class)->temporaryUrl($this));
    }
}
