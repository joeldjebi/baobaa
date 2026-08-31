<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenueReviewController extends Controller
{
    public function store(Request $request, Venue $venue): RedirectResponse
    {
        $user = Auth::user();

        abort_if(! $user || ! $user->hasPortal(UserRole::Client), 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'comment' => ['required', 'string', 'min:10', 'max:1200'],
        ]);

        $booking = Booking::query()
            ->where('client_id', $user->id)
            ->where('venue_id', $venue->id)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->latest('event_date')
            ->first();

        if (! $booking) {
            return back()
                ->withErrors(['review' => 'Vous devez avoir une réservation confirmée pour commenter cet espace.'])
                ->withInput();
        }

        if ($venue->reviews()->where('client_id', $user->id)->exists()) {
            return back()
                ->withErrors(['review' => 'Vous avez déjà laissé un avis pour cet espace.'])
                ->withInput();
        }

        $venue->reviews()->create([
            'client_id' => $user->id,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'pending',
        ]);

        return back()->with('review_status', 'Merci, votre avis a été envoyé et sera publié après validation.');
    }
}
