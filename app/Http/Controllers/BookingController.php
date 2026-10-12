<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\OwnerModuleTemplate;
use App\Models\Payment;
use App\Models\Venue;
use App\Models\VenueAddOn;
use App\Services\AvailabilityHoldService;
use App\Services\BookingDepositService;
use App\Services\BookingWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingDepositService $bookingDepositService,
        private readonly BookingWorkflowService $bookingWorkflowService,
        private readonly AvailabilityHoldService $availabilityHoldService,
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
            'selected_add_on_ids' => ['nullable', 'array', 'max:12'],
            'selected_add_on_ids.*' => [
                'integer',
                Rule::exists('venue_add_ons', 'id')
                    ->where('venue_id', $venue->id)
                    ->where('is_available', true),
            ],
            'selected_module_template_ids' => ['nullable', 'array', 'max:12'],
            'selected_module_template_ids.*' => [
                'integer',
                Rule::exists('owner_module_templates', 'id')
                    ->where('owner_profile_id', $venue->owner_profile_id)
                    ->where('is_active', true),
            ],
            'event_service_ids' => ['nullable', 'array', 'max:6'],
            'event_service_ids.*' => [
                'integer',
                Rule::exists('event_services', 'id')->where('status', VenueStatus::Published->value),
            ],
            'ticketing_requested' => ['nullable', 'boolean'],
            'accept_terms' => ['accepted'],
        ]);

        $venue->loadMissing('ownerProfile.depositRules');
        $selectedAddOns = $this->selectedVenueAddOnsFor($venue, array_map('intval', $validated['selected_add_on_ids'] ?? []))
            ->concat($this->selectedModuleTemplatesFor($venue, array_map('intval', $validated['selected_module_template_ids'] ?? [])));
        $selectedAddOnsTotal = (int) $selectedAddOns->sum('price');

        $wantsNegotiation = ($validated['booking_intent'] ?? 'reserve') === 'negotiate' && filled($validated['proposed_amount'] ?? null);
        $baseAmount = $wantsNegotiation ? (int) $validated['proposed_amount'] : (int) $venue->starting_price;
        $totalAmount = $baseAmount + $selectedAddOnsTotal;
        $reservationAmount = $this->bookingDepositService->amountFor($venue, $totalAmount);
        $paymentMethod = $validated['payment_method'];
        $eventServiceIds = array_map('intval', $validated['event_service_ids'] ?? []);
        $ticketingRequested = $request->boolean('ticketing_requested');
        $selectedAddOnsPayload = $this->selectedAddOnsPayload($selectedAddOns);
        [$slotStartsAt, $slotEndsAt] = $this->availabilityHoldService->slotFromDateAndTimes(
            $validated['start_date'],
            $validated['end_date'] ?? $validated['start_date'],
            $validated['starts_at'],
            $validated['ends_at'],
        );

        $createBooking = function () use ($request, $venue, $validated, $baseAmount, $totalAmount, $reservationAmount, $paymentMethod, $wantsNegotiation, $eventServiceIds, $ticketingRequested, $selectedAddOnsPayload): Booking {
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
                'selected_add_ons' => $selectedAddOnsPayload,
                'expires_at' => now()->addMinutes(10),
            ]);

            if ($wantsNegotiation || filled($validated['client_notes'] ?? null)) {
                $booking->messages()->create([
                    'sender_id' => $request->user()->id,
                    'recipient_id' => $venue->ownerProfile?->user_id,
                    'message' => $validated['client_notes'] ?: 'Je souhaite négocier cette réservation.',
                    'proposed_amount' => $wantsNegotiation ? $baseAmount : null,
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
                    'selected_add_ons' => $selectedAddOnsPayload,
                ],
            ]);

            $this->bookingWorkflowService->ensureReadyForNegotiation($booking);
            $this->bookingWorkflowService->syncRequestedAdditions($booking, $eventServiceIds, $ticketingRequested);

            return $booking;
        };

        $booking = $this->availabilityHoldService->reserveVenueSlot(
            $venue,
            $request->user(),
            $slotStartsAt,
            $slotEndsAt,
            $request->session()->getId(),
            fn (): Booking => $this->availabilityHoldService->reserveEventServiceSlots(
                $eventServiceIds,
                $request->user(),
                $slotStartsAt,
                $slotEndsAt,
                $request->session()->getId(),
                fn (): Booking => $createBooking(),
            ),
        );

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

    /**
     * @param  array<int, int>  $selectedAddOnIds
     */
    private function selectedVenueAddOnsFor(Venue $venue, array $selectedAddOnIds): Collection
    {
        if ($selectedAddOnIds === []) {
            return collect();
        }

        return VenueAddOn::query()
            ->where('venue_id', $venue->id)
            ->where('is_available', true)
            ->whereIn('id', array_unique($selectedAddOnIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, int>  $selectedModuleTemplateIds
     */
    private function selectedModuleTemplatesFor(Venue $venue, array $selectedModuleTemplateIds): Collection
    {
        if ($selectedModuleTemplateIds === []) {
            return collect();
        }

        return OwnerModuleTemplate::query()
            ->where('owner_profile_id', $venue->owner_profile_id)
            ->where('is_active', true)
            ->whereIn('id', array_unique($selectedModuleTemplateIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, VenueAddOn|OwnerModuleTemplate>  $selectedAddOns
     * @return array<int, array{id: int, source_type: string, source_id: int, name: string, price: int, quantity: int, currency: string}>
     */
    private function selectedAddOnsPayload(Collection $selectedAddOns): array
    {
        return $selectedAddOns
            ->map(fn (VenueAddOn|OwnerModuleTemplate $addOn): array => [
                'id' => $addOn->id,
                'source_type' => $addOn instanceof VenueAddOn ? 'venue_add_on' : 'owner_module_template',
                'source_id' => $addOn->id,
                'name' => $addOn->name,
                'price' => (int) $addOn->price,
                'quantity' => 1,
                'currency' => $addOn->currency,
            ])
            ->values()
            ->all();
    }
}
