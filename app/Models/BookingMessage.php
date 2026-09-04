<?php

namespace App\Models;

use Database\Factories\BookingMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingMessage extends Model
{
    /** @use HasFactory<BookingMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'sender_id',
        'recipient_id',
        'message',
        'proposed_amount',
        'currency',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
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
