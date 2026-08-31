<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request, Venue $venue): RedirectResponse
    {
        abort_unless($venue->status === VenueStatus::Published, 404);

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'ends_at' => ['required', 'date_format:H:i'],
            'event_type' => ['nullable', 'string', 'max:80'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:'.$venue->max_capacity],
        ]);

        $reservationAmount = (int) ($venue->reservation_amount ?: $venue->starting_price);

        Booking::query()->create([
            'client_id' => $request->user()->id,
            'owner_profile_id' => $venue->owner_profile_id,
            'venue_id' => $venue->id,
            'reference' => $this->uniqueReference(),
            'status' => BookingStatus::PendingPayment,
            'booking_mode' => $venue->booking_mode,
            'event_type' => $validated['event_type'] ?? null,
            'event_date' => $validated['start_date'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'guests_count' => $validated['guests_count'],
            'currency' => $venue->currency,
            'total_amount' => (int) $venue->starting_price,
            'reservation_amount' => $reservationAmount,
            'expires_at' => now()->addMinutes(30),
        ]);

        return back()->with('booking_status', 'Votre réservation est enregistrée. Vous pouvez maintenant passer au paiement sécurisé.');
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'BAO-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Booking::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
