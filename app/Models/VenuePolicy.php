<?php

namespace App\Models;

use Database\Factories\VenuePolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenuePolicy extends Model
{
    /** @use HasFactory<VenuePolicyFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'policy_type',
        'title',
        'summary',
        'content',
        'is_highlighted',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_highlighted' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
