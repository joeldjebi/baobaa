<?php

namespace App\Models;

use App\Enums\AvailabilityHoldStatus;
use Database\Factories\AvailabilityHoldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AvailabilityHold extends Model
{
    /** @use HasFactory<AvailabilityHoldFactory> */
    use HasFactory;

    protected $fillable = [
        'holdable_type',
        'holdable_id',
        'client_id',
        'booking_id',
        'event_project_id',
        'session_id',
        'status',
        'starts_at',
        'ends_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AvailabilityHoldStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function holdable(): MorphTo
    {
        return $this->morphTo();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function eventProject(): BelongsTo
    {
        return $this->belongsTo(EventProject::class);
    }
}
