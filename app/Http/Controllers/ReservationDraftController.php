<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Keeps what a visitor entered on the reservation page while they sign in or create their client account,
 * so the form is filled again when they come back (see PublicVenueController::reservation).
 */
class ReservationDraftController extends Controller
{
    public const SessionKey = 'reservation_drafts';

    public function store(Request $request, Venue $venue): RedirectResponse
    {
        abort_unless($venue->status === VenueStatus::Published, 404);

        $request->session()->put(self::SessionKey.'.'.$venue->id, $request->only([
            'event_start',
            'event_end',
            'event_type',
            'guests_count',
            'payment_method',
            'client_notes',
            'selected_add_on_ids',
            'selected_module_template_ids',
            'event_service_ids',
            'ticketing_requested',
        ]));

        $reservationUrl = route('venues.reservation', $venue->slug);
        $request->session()->put('url.intended', $reservationUrl);

        return $request->input('next') === 'register'
            ? redirect()->route('client.register')
            : redirect()->route('portal.login', ['portal' => 'client', 'redirect' => $reservationUrl]);
    }
}
