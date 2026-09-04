<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Venue;
use App\Services\BookingDepositService;
use App\Services\BookingWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingDepositService $bookingDepositService,
        private readonly BookingWorkflowService $bookingWorkflowService,
    ) {}

    public function store(Request $request, Venue $venue): RedirectResponse
    {
        abort_unless($venue->status === VenueStatus::Published, 404);
        $allowedPaymentMethods = $this->allowedPaymentMethods($venue);

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'ends_at' => ['required', 'date_format:H:i'],
            'event_type' => ['nullable', 'string', 'max:80'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:'.$venue->max_capacity],
            'payment_method' => ['required', 'string', Rule::in($allowedPaymentMethods)],
            'booking_intent' => ['nullable', Rule::in(['reserve', 'negotiate'])],
            'proposed_amount' => ['nullable', 'integer', 'min:1'],
            'client_notes' => ['nullable', 'string', 'max:2000'],
            'event_service_ids' => ['nullable', 'array', 'max:6'],
            'event_service_ids.*' => [
                'integer',
                Rule::exists('event_services', 'id')->where('status', VenueStatus::Published->value),
            ],
            'ticketing_requested' => ['nullable', 'boolean'],
        ]);

        $venue->loadMissing('ownerProfile.depositRules');

        $wantsNegotiation = ($validated['booking_intent'] ?? 'reserve') === 'negotiate' && filled($validated['proposed_amount'] ?? null);
        $totalAmount = $wantsNegotiation ? (int) $validated['proposed_amount'] : (int) $venue->starting_price;
        $reservationAmount = $this->bookingDepositService->amountFor($venue, $totalAmount);
        $paymentMethod = $validated['payment_method'];
        $eventServiceIds = array_map('intval', $validated['event_service_ids'] ?? []);
        $ticketingRequested = $request->boolean('ticketing_requested');

        $booking = DB::transaction(function () use ($request, $venue, $validated, $totalAmount, $reservationAmount, $paymentMethod, $wantsNegotiation, $eventServiceIds, $ticketingRequested): Booking {
            $booking = Booking::query()->create([
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
                'total_amount' => $totalAmount,
                'reservation_amount' => $reservationAmount,
                'client_notes' => $validated['client_notes'] ?? null,
                'expires_at' => now()->addMinutes(30),
            ]);

            if ($wantsNegotiation || filled($validated['client_notes'] ?? null)) {
                $booking->messages()->create([
                    'sender_id' => $request->user()->id,
                    'recipient_id' => $venue->ownerProfile?->user_id,
                    'message' => $validated['client_notes'] ?: 'Je souhaite négocier cette réservation.',
                    'proposed_amount' => $wantsNegotiation ? $totalAmount : null,
                    'currency' => $venue->currency,
                ]);
            }

            Payment::query()->create([
                'booking_id' => $booking->id,
                'payer_id' => $request->user()->id,
                'reference' => $this->uniquePaymentReference(),
                'provider' => $paymentMethod,
                'payment_method' => $paymentMethod,
                'status' => PaymentStatus::Initiated,
                'amount' => $reservationAmount,
                'currency' => $venue->currency,
                'provider_payload' => [
                    'reason' => 'reservation_deposit',
                    'source' => 'sap_owner_deposit_rule',
                    'price_source' => $wantsNegotiation ? 'client_proposal' : 'venue_public_price',
                ],
            ]);

            $this->bookingWorkflowService->ensureReadyForNegotiation($booking);
            $this->bookingWorkflowService->syncRequestedAdditions($booking, $eventServiceIds, $ticketingRequested);

            return $booking;
        });

        return redirect()
            ->route('client.reservations.show', $booking)
            ->with('booking_status', 'Votre demande est enregistrée. Consultez la proforma, échangez avec le partenaire puis payez l’acompte de réservation.');
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'BAO-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Booking::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function uniquePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @return array<int, string>
     */
    private function allowedPaymentMethods(Venue $venue): array
    {
        $paymentMethods = $venue->payment_methods ?: [];

        return $paymentMethods ?: ['baobaa_checkout'];
    }
}
