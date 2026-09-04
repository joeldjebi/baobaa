<?php

namespace App\Models;

use App\Enums\EventProjectItemStatus;
use Database\Factories\EventProjectItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventProjectItem extends Model
{
    /** @use HasFactory<EventProjectItemFactory> */
    use HasFactory;

    protected $fillable = [
        'event_project_id',
        'item_type',
        'provider_type',
        'provider_id',
        'source_type',
        'source_id',
        'status',
        'title',
        'description',
        'currency',
        'quoted_amount',
        'deposit_amount',
        'client_confirmed_at',
        'provider_confirmed_at',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => EventProjectItemStatus::class,
            'client_confirmed_at' => 'datetime',
            'provider_confirmed_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function eventProject(): BelongsTo
    {
        return $this->belongsTo(EventProject::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function proformaInvoice(): HasOne
    {
        return $this->hasOne(ProformaInvoice::class);
    }
}
