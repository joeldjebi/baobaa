<?php

namespace App\Models;

use Database\Factories\EventProjectMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProjectMessage extends Model
{
    /** @use HasFactory<EventProjectMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'event_project_id',
        'provider_type',
        'provider_id',
        'sender_id',
        'recipient_id',
        'message',
        'proposed_amount',
        'currency',
    ];

    public function eventProject(): BelongsTo
    {
        return $this->belongsTo(EventProject::class);
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
