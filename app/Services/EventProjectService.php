<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EventProjectItemStatus;
use App\Enums\EventProjectStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use App\Models\EventService;
use App\Models\Payment;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventProjectService
{
    public function ensureVenueBookingItem(Booking $booking): EventProjectItem
    {
        $booking->loadMissing(['venue', 'eventProjectItem.eventProject']);

        if ($booking->eventProjectItem) {
            $item = $booking->eventProjectItem;
            $project = $item->eventProject;
        } else {
            $project = $this->createProjectFromBooking($booking);
            $item = $this->createItemFromBooking($project, $booking);
            $booking->forceFill(['event_project_item_id' => $item->id])->save();
        }

        $item->forceFill($this->itemAttributesFromBooking($booking))->save();
        $this->refreshTotals($project);

        return $item->refresh();
    }

    public function refreshTotals(EventProject $project): void
    {
        $project->loadMissing('items');

        $project->update([
            'estimated_total_amount' => (int) $project->items()->sum('quoted_amount'),
            'confirmed_total_amount' => (int) $project->items()
                ->where('status', EventProjectItemStatus::Confirmed->value)
                ->sum('quoted_amount'),
        ]);
    }

    /**
     * Items quoted by one service provider inside a project: the provider "lane" negotiated as a whole.
     *
     * @return Collection<int, EventProjectItem>
     */
    public function providerLaneItems(EventProject $project, int $serviceProviderProfileId): Collection
    {
        return $project->items()
            ->where('item_type', 'event_service')
            ->where('provider_type', 'service_provider_profile')
            ->where('provider_id', $serviceProviderProfileId)
            ->whereNot('status', EventProjectItemStatus::Cancelled->value)
            ->orderBy('id')
            ->get();
    }

    /**
     * Record the confirmation of one party and move the lane to payment or confirmation when both agreed.
     *
     * @param  'client'|'provider'  $party
     */
    public function confirmProviderLane(EventProject $project, int $serviceProviderProfileId, string $party): void
    {
        DB::transaction(function () use ($project, $serviceProviderProfileId, $party): void {
            $this->providerLaneItems($project, $serviceProviderProfileId)
                ->reject(fn (EventProjectItem $item): bool => $item->paid_at !== null)
                ->each(function (EventProjectItem $item) use ($party): void {
                    $clientConfirmedAt = $party === 'client' ? ($item->client_confirmed_at ?: now()) : $item->client_confirmed_at;
                    $providerConfirmedAt = $party === 'provider' ? ($item->provider_confirmed_at ?: now()) : $item->provider_confirmed_at;

                    $item->forceFill([
                        'client_confirmed_at' => $clientConfirmedAt,
                        'provider_confirmed_at' => $providerConfirmedAt,
                        'status' => match (true) {
                            $clientConfirmedAt && $providerConfirmedAt => (int) $item->deposit_amount > 0
                                ? EventProjectItemStatus::AwaitingPayment
                                : EventProjectItemStatus::Confirmed,
                            $clientConfirmedAt !== null => EventProjectItemStatus::AwaitingProviderConfirmation,
                            default => EventProjectItemStatus::AwaitingClientConfirmation,
                        },
                    ])->save();
                });

            $this->refreshTotals($project);
        });
    }

    /**
     * Amount still due to secure the provider lane.
     */
    public function providerLaneDepositDue(EventProject $project, int $serviceProviderProfileId): int
    {
        return (int) $this->providerLaneItems($project, $serviceProviderProfileId)
            ->where('status', EventProjectItemStatus::AwaitingPayment)
            ->whereNull('paid_at')
            ->sum('deposit_amount');
    }

    /**
     * Register the deposit payment of a provider lane and confirm its items.
     *
     * @param  array<string, mixed>  $providerPayload
     */
    public function recordProviderLaneDeposit(EventProject $project, ServiceProviderProfile $serviceProviderProfile, User $payer, string $paymentMethod, array $providerPayload = []): ?Payment
    {
        return DB::transaction(function () use ($project, $serviceProviderProfile, $payer, $paymentMethod, $providerPayload): ?Payment {
            $items = $this->providerLaneItems($project, $serviceProviderProfile->id)
                ->where('status', EventProjectItemStatus::AwaitingPayment)
                ->whereNull('paid_at');
            $amount = (int) $items->sum('deposit_amount');

            if ($items->isEmpty() || $amount <= 0) {
                return null;
            }

            $commission = app(PartnerConditionsService::class)->serviceProviderCommission($serviceProviderProfile, (int) $items->sum('quoted_amount'));

            $payment = $project->payments()->create([
                'provider_type' => 'service_provider_profile',
                'provider_id' => $serviceProviderProfile->id,
                'payer_id' => $payer->id,
                'reference' => $this->uniquePaymentReference(),
                'provider' => $paymentMethod,
                'payment_method' => $paymentMethod,
                'status' => PaymentStatus::Succeeded,
                'amount' => $amount,
                'currency' => $project->currency,
                'provider_payload' => [
                    'reason' => 'event_service_deposit',
                    'event_project_item_ids' => $items->modelKeys(),
                    'commission' => $commission,
                    ...$providerPayload,
                ],
                'paid_at' => now(),
            ]);

            $items->each(fn (EventProjectItem $item) => $item->forceFill([
                'status' => EventProjectItemStatus::Confirmed,
                'paid_at' => now(),
            ])->save());

            $this->refreshTotals($project);

            return $payment;
        });
    }

    private function uniquePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @param  array<int, int>  $eventServiceIds
     */
    public function syncRequestedAdditions(Booking $booking, array $eventServiceIds, bool $ticketingRequested): void
    {
        $venueItem = $this->ensureVenueBookingItem($booking);
        $project = $venueItem->eventProject;

        $this->attachEventServices($project, $eventServiceIds);

        if ($ticketingRequested) {
            $this->attachTicketing($project, $booking);
        }

        $this->refreshTotals($project);
    }

    /**
     * @param  array<int, int>  $eventServiceQuantitiesById
     */
    public function syncBookingEventServices(Booking $booking, array $eventServiceQuantitiesById): void
    {
        $venueItem = $this->ensureVenueBookingItem($booking);
        $project = $venueItem->eventProject;
        $selectedServiceIds = collect($eventServiceQuantitiesById)
            ->filter(fn (int $quantity): bool => $quantity > 0)
            ->keys()
            ->map(fn (int|string $id): int => (int) $id)
            ->values()
            ->all();

        $project->items()
            ->where('item_type', 'event_service')
            ->when($selectedServiceIds !== [], fn ($query) => $query->whereNotIn('source_id', $selectedServiceIds))
            ->delete();

        $this->attachEventServices($project, $selectedServiceIds, $eventServiceQuantitiesById);
        $this->refreshTotals($project);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>  $eventServiceIds
     */
    public function createStandaloneProject(User $client, array $payload, array $eventServiceIds, bool $ticketingRequested): EventProject
    {
        $project = EventProject::query()->create([
            'client_id' => $client->id,
            'reference' => $this->uniqueProjectReference(),
            'name' => $payload['name'],
            'status' => EventProjectStatus::Active,
            'event_type' => $payload['event_type'] ?? null,
            'event_date' => $payload['event_date'] ?? null,
            'country_code' => strtoupper((string) ($payload['country_code'] ?? 'CI')),
            'city' => $payload['city'] ?? null,
            'district' => $payload['district'] ?? null,
            'currency' => 'XOF',
            'estimated_total_amount' => 0,
            'confirmed_total_amount' => 0,
            'metadata' => [
                'created_from' => 'event_composer',
                'guests_count' => $payload['guests_count'] ?? null,
                'client_notes' => $payload['client_notes'] ?? null,
                'starts_at' => $payload['starts_at'] ?? null,
                'ends_at' => $payload['ends_at'] ?? null,
            ],
        ]);

        $this->attachEventServices($project, $eventServiceIds);

        if ($ticketingRequested) {
            $this->attachTicketing($project);
        }

        $this->refreshTotals($project);

        return $project->refresh();
    }

    /**
     * @param  array<int, int>  $eventServiceIds
     */
    private function attachEventServices(EventProject $project, array $eventServiceIds, array $quantitiesByServiceId = []): void
    {
        if ($eventServiceIds === []) {
            return;
        }

        EventService::query()
            ->with(['serviceProviderProfile', 'type'])
            ->whereIn('id', $eventServiceIds)
            ->publiclyAvailable()
            ->get()
            ->each(function (EventService $service) use ($project, $quantitiesByServiceId): void {
                $quantity = max(1, (int) ($quantitiesByServiceId[$service->id] ?? 1));
                $unitAmount = (int) $service->starting_price;
                $unitDeposit = (int) ($service->deposit_amount ?: 0);

                $project->items()->updateOrCreate([
                    'item_type' => 'event_service',
                    'source_type' => EventService::class,
                    'source_id' => $service->id,
                ], [
                    'provider_type' => 'service_provider_profile',
                    'provider_id' => $service->service_provider_profile_id,
                    'status' => EventProjectItemStatus::Negotiating,
                    'title' => $service->name,
                    'description' => $service->short_description ?: $service->description,
                    'currency' => $service->currency,
                    'quoted_amount' => $unitAmount * $quantity,
                    'deposit_amount' => $unitDeposit * $quantity,
                    'metadata' => [
                        'service_type' => $service->type?->name,
                        'provider_name' => $service->serviceProviderProfile?->business_name,
                        'pricing_unit' => $service->pricing_unit,
                        'quantity' => $quantity,
                        'unit_amount' => $unitAmount,
                        'unit_deposit_amount' => $unitDeposit,
                    ],
                ]);
            });
    }

    private function attachTicketing(EventProject $project, ?Booking $booking = null): void
    {
        $project->items()->updateOrCreate([
            'item_type' => 'ticketing',
            'provider_type' => 'sap',
            'provider_id' => null,
        ], [
            'status' => EventProjectItemStatus::Negotiating,
            'title' => 'Billetterie BAOBAA',
            'description' => 'Demande de mise en place d’une billetterie gérée par le SAP BAOBAA.',
            'currency' => $project->currency,
            'quoted_amount' => 0,
            'deposit_amount' => 0,
            'metadata' => [
                'requested_from' => $booking ? 'venue_booking_form' : 'event_composer',
                'booking_reference' => $booking?->reference,
                'commission_mode' => 'off_platform_negotiation',
            ],
        ]);
    }

    private function createProjectFromBooking(Booking $booking): EventProject
    {
        $venue = $booking->venue;

        return EventProject::query()->create([
            'client_id' => $booking->client_id,
            'reference' => $this->uniqueProjectReference(),
            'name' => $this->projectName($booking),
            'status' => EventProjectStatus::Active,
            'event_type' => $booking->event_type,
            'event_date' => $booking->event_date,
            'country_code' => $venue?->country_code ?: 'CI',
            'city' => $venue?->city,
            'district' => $venue?->district,
            'currency' => $booking->currency,
            'estimated_total_amount' => $booking->total_amount,
            'confirmed_total_amount' => 0,
            'metadata' => [
                'created_from' => 'venue_booking',
                'booking_reference' => $booking->reference,
            ],
        ]);
    }

    private function createItemFromBooking(EventProject $project, Booking $booking): EventProjectItem
    {
        return $project->items()->create($this->itemAttributesFromBooking($booking));
    }

    /**
     * @return array<string, mixed>
     */
    private function itemAttributesFromBooking(Booking $booking): array
    {
        $venue = $booking->venue;

        return [
            'item_type' => 'venue_booking',
            'provider_type' => 'owner_profile',
            'provider_id' => $booking->owner_profile_id,
            'source_type' => Venue::class,
            'source_id' => $booking->venue_id,
            'status' => $this->itemStatusFor($booking),
            'title' => $venue?->name ?: 'Réservation d’espace',
            'description' => $venue?->short_description,
            'currency' => $booking->currency,
            'quoted_amount' => $booking->total_amount,
            'deposit_amount' => $booking->reservation_amount,
            'paid_at' => $booking->payments()
                ->where('status', PaymentStatus::Succeeded)
                ->latest('paid_at')
                ->value('paid_at'),
            'metadata' => [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->reference,
                'event_date' => $booking->event_date?->toDateString(),
                'starts_at' => $booking->starts_at,
                'ends_at' => $booking->ends_at,
                'guests_count' => $booking->guests_count,
                'selected_add_ons' => $booking->selected_add_ons ?: [],
            ],
        ];
    }

    private function itemStatusFor(Booking $booking): EventProjectItemStatus
    {
        return match ($booking->status) {
            BookingStatus::Confirmed, BookingStatus::Completed => EventProjectItemStatus::Confirmed,
            BookingStatus::Cancelled, BookingStatus::Declined => EventProjectItemStatus::Cancelled,
            BookingStatus::PendingPayment => EventProjectItemStatus::AwaitingPayment,
            BookingStatus::PendingOwner => EventProjectItemStatus::AwaitingProviderConfirmation,
            default => EventProjectItemStatus::Negotiating,
        };
    }

    private function projectName(Booking $booking): string
    {
        $eventLabel = $booking->event_type ? Str::headline($booking->event_type) : 'Événement';

        return $eventLabel.' · '.$booking->event_date?->format('d/m/Y');
    }

    private function uniqueProjectReference(): string
    {
        do {
            $reference = 'EVT-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (EventProject::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
