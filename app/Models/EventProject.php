<?php

namespace App\Models;

use App\Enums\EventProjectStatus;
use Database\Factories\EventProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventProject extends Model
{
    /** @use HasFactory<EventProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'reference',
        'name',
        'status',
        'event_type',
        'event_date',
        'country_code',
        'city',
        'district',
        'currency',
        'estimated_total_amount',
        'confirmed_total_amount',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => EventProjectStatus::class,
            'event_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EventProjectItem::class);
    }
}
