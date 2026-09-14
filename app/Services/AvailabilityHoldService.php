<?php

namespace App\Services;

use App\Enums\AvailabilityHoldStatus;
use App\Enums\BookingStatus;
use App\Enums\EventProjectItemStatus;
use App\Enums\EventProjectStatus;
use App\Models\AvailabilityHold;
use App\Models\Booking;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use App\Models\EventService;
use App\Models\User;
use App\Models\Venue;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AvailabilityHoldService
{
    private const HOLD_MINUTES = 10;

    /**
     * @template TReturn
     *
     * @param  Closure(AvailabilityHold): TReturn  $callback
     * @return TReturn
     */
    public function reserveVenueSlot(Venue $venue, User $client, CarbonInterface $startsAt, CarbonInterface $endsAt, ?string $sessionId, Closure $callback): mixed
    {
        return $this->withResourceLocks([Venue::class.':'.$venue->id], function () use ($venue, $client, $startsAt, $endsAt, $sessionId, $callback): mixed {
            return DB::transaction(function () use ($venue, $client, $startsAt, $endsAt, $sessionId, $callback): mixed {
                $this->assertVenueSlotAvailable($venue, $startsAt, $endsAt);

                $hold = $this->createHold(Venue::class, $venue->id, $client, $startsAt, $endsAt, $sessionId);
                $result = $callback($hold);

                if ($result instanceof Booking) {
                    $this->convertHold($hold, booking: $result);
                }

                return $result;
            });
        });
    }

    /**
     * @template TReturn
     *
     * @param  array<int, int>  $eventServiceIds
     * @param  Closure(Collection<int, AvailabilityHold>): TReturn  $callback
     * @return TReturn
     */
    public function reserveEventServiceSlots(array $eventServiceIds, User $client, ?CarbonInterface $startsAt, ?CarbonInterface $endsAt, ?string $sessionId, Closure $callback): mixed
    {
        $eventServiceIds = collect($eventServiceIds)->map(fn (int|string $id): int => (int) $id)->unique()->sort()->values()->all();

        if ($eventServiceIds === [] || ! $startsAt || ! $endsAt) {
            return $callback(collect());
        }

        $lockKeys = collect($eventServiceIds)
            ->map(fn (int $serviceId): string => EventService::class.':'.$serviceId)
            ->all();

        return $this->withResourceLocks($lockKeys, function () use ($eventServiceIds, $client, $startsAt, $endsAt, $sessionId, $callback): mixed {
            return DB::transaction(function () use ($eventServiceIds, $client, $startsAt, $endsAt, $sessionId, $callback): mixed {
                foreach ($eventServiceIds as $eventServiceId) {
                    $this->assertEventServiceSlotAvailable($eventServiceId, $startsAt, $endsAt);
                }

                $holds = collect($eventServiceIds)
                    ->map(fn (int $eventServiceId): AvailabilityHold => $this->createHold(EventService::class, $eventServiceId, $client, $startsAt, $endsAt, $sessionId));

                $result = $callback($holds);

                if ($result instanceof EventProject) {
                    $holds->each(fn (AvailabilityHold $hold): bool => $this->convertHold($hold, eventProject: $result));
                }

                if ($result instanceof Booking) {
                    $result->loadMissing('eventProjectItem.eventProject');
                    $eventProject = $result->eventProjectItem?->eventProject;

                    $holds->each(fn (AvailabilityHold $hold): bool => $this->convertHold($hold, booking: $result, eventProject: $eventProject));
                }

                return $result;
            });
        });
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function slotFromDateAndTimes(string $startDate, ?string $endDate, ?string $startsAt, ?string $endsAt): array
    {
        $startTime = $startsAt ?: '00:00';
        $endTime = $endsAt ?: '23:59';
        $startsAtDateTime = CarbonImmutable::parse($startDate.' '.$startTime);
        $endsAtDateTime = CarbonImmutable::parse(($endDate ?: $startDate).' '.$endTime);

        if ($endsAtDateTime->lessThanOrEqualTo($startsAtDateTime)) {
            $endsAtDateTime = $endsAtDateTime->addDay();
        }

        return [$startsAtDateTime, $endsAtDateTime];
    }

    private function assertVenueSlotAvailable(Venue $venue, CarbonInterface $startsAt, CarbonInterface $endsAt): void
    {
        if ($this->hasActiveHold(Venue::class, $venue->id, $startsAt, $endsAt) || $this->hasConflictingBooking($venue, $startsAt, $endsAt)) {
            throw ValidationException::withMessages([
                'start_date' => 'Ce créneau est déjà réservé ou temporairement bloqué par un autre client. Choisissez une autre date ou un autre horaire.',
            ]);
        }
    }

    private function assertEventServiceSlotAvailable(int $eventServiceId, CarbonInterface $startsAt, CarbonInterface $endsAt): void
    {
        if ($this->hasActiveHold(EventService::class, $eventServiceId, $startsAt, $endsAt) || $this->hasConflictingEventServiceItem($eventServiceId, $startsAt, $endsAt)) {
            throw ValidationException::withMessages([
                'event_service_ids' => 'Un service sélectionné est déjà réservé ou temporairement bloqué sur ce créneau. Ajustez la date, l’heure ou votre sélection.',
            ]);
        }
    }

    private function createHold(string $holdableType, int $holdableId, User $client, CarbonInterface $startsAt, CarbonInterface $endsAt, ?string $sessionId): AvailabilityHold
    {
        return AvailabilityHold::query()->create([
            'holdable_type' => $holdableType,
            'holdable_id' => $holdableId,
            'client_id' => $client->id,
            'session_id' => $sessionId,
            'status' => AvailabilityHoldStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
        ]);
    }

    private function convertHold(AvailabilityHold $hold, ?Booking $booking = null, ?EventProject $eventProject = null): bool
    {
        return $hold->forceFill([
            'booking_id' => $booking?->id,
            'event_project_id' => $eventProject?->id,
            'status' => AvailabilityHoldStatus::Converted,
        ])->save();
    }

    private function hasActiveHold(string $holdableType, int $holdableId, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        return AvailabilityHold::query()
            ->where('holdable_type', $holdableType)
            ->where('holdable_id', $holdableId)
            ->where('status', AvailabilityHoldStatus::Active)
            ->where('expires_at', '>', now())
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    private function hasConflictingBooking(Venue $venue, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        return Booking::query()
            ->where('venue_id', $venue->id)
            ->whereIn('status', [
                BookingStatus::Draft->value,
                BookingStatus::PendingOwner->value,
                BookingStatus::PendingPayment->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Disputed->value,
            ])
            ->get(['status', 'event_date', 'starts_at', 'ends_at', 'expires_at'])
            ->contains(function (Booking $booking) use ($startsAt, $endsAt): bool {
                if ($booking->status === BookingStatus::PendingPayment && $booking->expires_at && $booking->expires_at->lessThanOrEqualTo(now())) {
                    return false;
                }

                return $this->bookingOverlaps($booking, $startsAt, $endsAt);
            });
    }

    private function hasConflictingEventServiceItem(int $eventServiceId, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        return EventProjectItem::query()
            ->with('eventProject:id,status,event_date,metadata')
            ->where('source_type', EventService::class)
            ->where('source_id', $eventServiceId)
            ->whereIn('status', [
                EventProjectItemStatus::Negotiating->value,
                EventProjectItemStatus::AwaitingClientConfirmation->value,
                EventProjectItemStatus::AwaitingProviderConfirmation->value,
                EventProjectItemStatus::AwaitingPayment->value,
                EventProjectItemStatus::Confirmed->value,
            ])
            ->whereHas('eventProject', fn ($query) => $query->whereNot('status', EventProjectStatus::Cancelled->value))
            ->get()
            ->contains(function (EventProjectItem $item) use ($startsAt, $endsAt): bool {
                $eventDate = $item->eventProject?->event_date;

                if (! $eventDate || $eventDate->toDateString() < $startsAt->toDateString() || $eventDate->toDateString() > $endsAt->toDateString()) {
                    return false;
                }

                return $this->eventProjectItemOverlaps($item, $startsAt, $endsAt);
            });
    }

    private function bookingOverlaps(Booking $booking, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        [$bookingStartsAt, $bookingEndsAt] = $this->slotFromDateAndTimes(
            $booking->event_date->toDateString(),
            $booking->event_date->toDateString(),
            substr((string) $booking->starts_at, 0, 5),
            substr((string) $booking->ends_at, 0, 5),
        );

        return $this->overlaps($bookingStartsAt, $bookingEndsAt, $startsAt, $endsAt);
    }

    private function eventProjectItemOverlaps(EventProjectItem $item, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        $project = $item->eventProject;

        if (! $project?->event_date) {
            return false;
        }

        [$itemStartsAt, $itemEndsAt] = $this->slotFromDateAndTimes(
            $project->event_date->toDateString(),
            $project->event_date->toDateString(),
            $project->metadata['starts_at'] ?? null,
            $project->metadata['ends_at'] ?? null,
        );

        return $this->overlaps($itemStartsAt, $itemEndsAt, $startsAt, $endsAt);
    }

    private function overlaps(CarbonInterface $firstStartsAt, CarbonInterface $firstEndsAt, CarbonInterface $secondStartsAt, CarbonInterface $secondEndsAt): bool
    {
        return $firstStartsAt->lessThan($secondEndsAt) && $firstEndsAt->greaterThan($secondStartsAt);
    }

    /**
     * @template TReturn
     *
     * @param  array<int, string>  $resourceKeys
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    private function withResourceLocks(array $resourceKeys, Closure $callback): mixed
    {
        $locks = [];

        try {
            foreach (array_unique($resourceKeys) as $resourceKey) {
                $lock = Cache::lock('availability-hold:'.$resourceKey, 10);

                if (! $lock->get()) {
                    throw ValidationException::withMessages([
                        'start_date' => 'Ce créneau est en cours de vérification par un autre client. Réessayez dans quelques secondes.',
                    ]);
                }

                $locks[] = $lock;
            }

            return $callback();
        } finally {
            foreach (array_reverse($locks) as $lock) {
                $lock->release();
            }
        }
    }
}
