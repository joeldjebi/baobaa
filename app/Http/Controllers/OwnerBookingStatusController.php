<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\OwnerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerBookingStatusController extends Controller
{
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $booking->owner_profile_id === (int) $ownerProfile->id, 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['confirm', 'decline', 'cancel', 'complete'])],
        ]);

        $nextStatus = match ($validated['action']) {
            'confirm' => BookingStatus::Confirmed,
            'decline' => BookingStatus::Declined,
            'cancel' => BookingStatus::Cancelled,
            'complete' => BookingStatus::Completed,
        };

        $booking->update([
            'status' => $nextStatus,
            'confirmed_at' => $nextStatus === BookingStatus::Confirmed ? now() : $booking->confirmed_at,
            'cancelled_at' => $nextStatus === BookingStatus::Cancelled ? now() : $booking->cancelled_at,
        ]);

        return back()->with('booking_status', match ($validated['action']) {
            'confirm' => 'Réservation confirmée.',
            'decline' => 'Réservation refusée.',
            'cancel' => 'Réservation annulée.',
            'complete' => 'Réservation marquée comme terminée.',
        });
    }

    private function ownerProfile(Request $request): OwnerProfile
    {
        return $request->user()->ownerProfile()->firstOrFail();
    }
}
