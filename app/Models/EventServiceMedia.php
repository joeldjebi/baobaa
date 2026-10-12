<?php

namespace App\Models;

use App\Services\EventServiceImageService;
use Database\Factories\EventServiceMediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventServiceMedia extends Model
{
    /** @use HasFactory<EventServiceMediaFactory> */
    use HasFactory;

    protected $fillable = [
        'event_service_id',
        'disk',
        'path',
        'alt_text',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function eventService(): BelongsTo
    {
        return $this->belongsTo(EventService::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => app(EventServiceImageService::class)->temporaryUrl($this));
    }
}
