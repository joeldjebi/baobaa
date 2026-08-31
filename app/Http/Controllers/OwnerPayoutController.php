<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\OwnerProfile;
use App\Models\Payout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OwnerPayoutController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $booking->owner_profile_id === (int) $ownerProfile->id, 403);

        $hasSucceededPayment = $booking->payments()
            ->where('status', PaymentStatus::Succeeded)
            ->exists();
        $eligible = $booking->status === BookingStatus::Completed
            && $booking->event_date?->lte(now()->subHours(48))
            && $hasSucceededPayment;

        if (! $eligible) {
            return back()->withErrors([
                'payout' => 'La demande de reversement sera disponible 48h après la fin de la réservation, lorsque le paiement est confirmé.',
            ]);
        }

        if (Payout::query()->where('booking_id', $booking->id)->exists()) {
            return back()->withErrors([
                'payout' => 'Une demande de reversement existe déjà pour cette réservation.',
            ]);
        }

        $grossAmount = (int) $booking->payments()
            ->where('status', PaymentStatus::Succeeded)
            ->sum('amount');
        $commissionAmount = (int) round($grossAmount * 0.1);

        Payout::query()->create([
            'owner_profile_id' => $ownerProfile->id,
            'booking_id' => $booking->id,
            'reference' => 'REV-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
            'status' => 'pending',
            'gross_amount' => $grossAmount,
            'commission_amount' => $commissionAmount,
            'net_amount' => max(0, $grossAmount - $commissionAmount),
            'currency' => $booking->currency,
            'provider' => $ownerProfile->payout_provider,
            'provider_reference' => $ownerProfile->payout_account_reference,
            'scheduled_on' => now()->addDay()->toDateString(),
        ]);

        return back()->with('payout_status', 'Votre demande de reversement est enregistrée.');
    }

    private function ownerProfile(Request $request): OwnerProfile
    {
        return $request->user()->ownerProfile()->firstOrFail();
    }
}
