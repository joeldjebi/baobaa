<?php

namespace App\Models;

use Database\Factories\EventProjectItemMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProjectItemMessage extends Model
{
    /** @use HasFactory<EventProjectItemMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'event_project_item_id',
        'sender_id',
        'recipient_id',
        'message',
        'proposed_amount',
        'currency',
    ];

    public function eventProjectItem(): BelongsTo
    {
        return $this->belongsTo(EventProjectItem::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
