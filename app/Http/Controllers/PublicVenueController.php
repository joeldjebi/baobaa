<?php

namespace App\Http\Controllers;

use App\Enums\AvailabilityHoldStatus;
use App\Enums\BookingStatus;
use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\Venue;
use App\Models\VenueCategory;
use App\Services\BookingDepositService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PublicVenueController extends Controller
{
    public function __construct(private readonly BookingDepositService $bookingDepositService) {}

    public function index(Request $request): View
    {
        $calendarRange = $this->calendarRangeFromRequest($request);

        $venues = Venue::query()
            ->with(['category', 'media', 'ownerProfile'])
            ->withPublicRating()
            ->published()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->trim()->toString();

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('city'), function ($query) use ($request): void {
                $city = $request->string('city')->trim()->toString();

                $query->where(fn ($query) => $query
                    ->where('city', 'like', "%{$city}%")
                    ->orWhere('district', 'like', "%{$city}%"));
            })
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', fn ($query) => $query->where('slug', $request->string('category')->toString()));
            })
            ->when($request->filled('capacity'), fn ($query) => $query->where('max_capacity', '>=', $request->integer('capacity')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('starting_price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('starting_price', '<=', $request->integer('max_price')))
            ->when($calendarRange !== null, fn (Builder $query) => $this->applyCalendarAvailabilityFilters($query, ...$calendarRange))
            ->orderByDesc('published_at')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        if ($request->ajax()) {
            return view('venues.partials.results', [
                'venues' => $venues,
            ]);
        }

        return view('venues.index', [
            'venues' => $venues,
            'publishedVenuesCount' => Venue::query()->published()->count(),
            'categories' => VenueCategory::query()
                ->where('is_active', true)
                ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug']),
            'cities' => Venue::query()
                ->published()
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
            'searchSuggestions' => Venue::query()
                ->published()
                ->when($calendarRange !== null, fn (Builder $query) => $this->applyCalendarAvailabilityFilters($query, ...$calendarRange))
                ->orderBy('name')
                ->limit(20)
                ->get(['name'])
                ->toBase()
                ->map(fn (Venue $venue): array => ['label' => $venue->name, 'type' => 'Espace'])
                ->merge(
                    VenueCategory::query()
                        ->where('is_active', true)
                        ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
                        ->orderBy('name')
                        ->get(['name'])
                        ->toBase()
                        ->map(fn (VenueCategory $category): array => ['label' => $category->name, 'type' => 'Catégorie'])
                )
                ->values(),
        ]);
    }

    public function blockedDates(Request $request, string $slug): JsonResponse
    {
        $venue = Venue::query()
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $from = $this->parseDate($request->input('from'))?->startOfDay() ?? CarbonImmutable::today();
        $to = $this->parseDate($request->input('to'))?->endOfDay() ?? $from->addMonths(6)->endOfDay();

        if ($to->lessThan($from)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $slots = collect();

        $venue->bookings()
            ->whereIn('status', $this->blockingBookingStatuses())
            ->where('event_date', '>=', $from->toDateString())
            ->where('event_date', '<', $to->addDay()->toDateString())
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now())
                    ->orWhereNot('status', BookingStatus::PendingPayment->value);
            })
            ->get(['event_date', 'starts_at', 'ends_at', 'status', 'expires_at'])
            ->each(function ($booking) use ($slots): void {
                $slots->push([
                    'date' => $booking->event_date->toDateString(),
                    'starts_at' => substr((string) $booking->starts_at, 0, 5),
                    'ends_at' => substr((string) $booking->ends_at, 0, 5),
                    'source' => 'reservation',
                ]);
            });

        $venue->availabilityHolds()
            ->where('status', AvailabilityHoldStatus::Active->value)
            ->where('expires_at', '>', now())
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->get(['starts_at', 'ends_at'])
            ->each(function ($hold) use ($slots): void {
                $slots->push([
                    'date' => $hold->starts_at->toDateString(),
                    'starts_at' => $hold->starts_at->format('H:i'),
                    'ends_at' => $hold->ends_at->format('H:i'),
                    'source' => 'temporaire',
                ]);
            });

        $unavailableDates = $venue->availabilities()
            ->where('status', '!=', 'available')
            ->where('available_date', '>=', $from->toDateString())
            ->where('available_date', '<', $to->addDay()->toDateString())
            ->pluck('available_date')
            ->map(fn ($date): string => CarbonImmutable::parse($date)->toDateString());

        $blockedDates = $slots
            ->pluck('date')
            ->merge($unavailableDates)
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'blocked_dates' => $blockedDates,
            'blocked_slots' => $slots->sortBy(['date', 'starts_at'])->values(),
        ]);
    }

    public function show(string $slug): View
    {
        $venue = Venue::query()
            ->with([
                'category',
                'ownerProfile.depositRules',
                'amenities',
                'media',
                'configurations',
                'addOns',
                'policies',
                'faqs',
                'approvedReviews.client',
            ])
            ->withPublicRating()
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $eligibleBooking = null;
        $existingReview = null;

        if (Auth::check()) {
            $eligibleBooking = Auth::user()->bookings()
                ->where('venue_id', $venue->id)
                ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
                ->latest('event_date')
                ->first();

            $existingReview = $venue->reviews()
                ->where('client_id', Auth::id())
                ->first();
        }

        return view('venues.show', [
            'venue' => $venue,
            'depositAmount' => $this->bookingDepositService->amountFor($venue, (int) $venue->starting_price),
            'reservationAddOns' => $this->reservationAddOnsFor($venue),
            'similarVenues' => $this->similarVenuesFor($venue),
            'approvedReviews' => $venue->approvedReviews
                ->sortByDesc('approved_at')
                ->values(),
            'canReview' => $eligibleBooking && ! $existingReview,
            'existingReview' => $existingReview,
        ]);
    }

    public function reservation(string $slug): View
    {
        $venue = Venue::query()
            ->with([
                'category',
                'ownerProfile.depositRules',
                'media',
            ])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $publicEventServices = EventService::query()
            ->with(['type', 'serviceProviderProfile'])
            ->where('status', VenueStatus::Published)
            ->when($venue->city, fn ($query) => $query->orderByRaw('CASE WHEN city = ? THEN 0 ELSE 1 END', [$venue->city]))
            ->orderBy('starting_price')
            ->limit(24)
            ->get();

        $publicEventProviders = $publicEventServices
            ->filter(fn (EventService $service): bool => $service->serviceProviderProfile !== null)
            ->groupBy(fn (EventService $service) => $service->service_provider_profile_id)
            ->map(fn (Collection $services): array => [
                'provider' => $services->first()->serviceProviderProfile,
                'services' => $services,
            ])
            ->values();

        return view('venues.reservation', [
            'venue' => $venue,
            'depositAmount' => $this->bookingDepositService->amountFor($venue, (int) $venue->starting_price),
            'publicEventProviders' => $publicEventProviders,
            'reservationAddOns' => $this->reservationAddOnsFor($venue),
        ]);
    }

    /**
     * Additional services the client can select for this venue, using the venue options first and the owner modules otherwise.
     *
     * @return array<int, array{id: int, source: string, name: string, description: ?string, amount: int, currency: string, price: string}>
     */
    private function reservationAddOnsFor(Venue $venue): array
    {
        $addOns = $venue->addOns()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($addOn): array => $this->reservationAddOnPayload($addOn, 'venue_add_on'));

        if ($addOns->isEmpty() && $venue->ownerProfile) {
            $addOns = $venue->ownerProfile
                ->moduleTemplates()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn ($module): array => $this->reservationAddOnPayload($module, 'owner_module_template'));
        }

        return $addOns->values()->all();
    }

    /**
     * @return array{id: int, source: string, name: string, description: ?string, amount: int, currency: string, price: string}
     */
    private function reservationAddOnPayload(object $addOn, string $source): array
    {
        return [
            'id' => (int) $addOn->id,
            'source' => $source,
            'name' => $addOn->name,
            'description' => $addOn->description,
            'amount' => (int) $addOn->price,
            'currency' => $addOn->currency,
            'price' => number_format((int) $addOn->price, 0, ',', ' ').' '.$addOn->currency,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Venue>
     */
    private function similarVenuesFor(Venue $venue): \Illuminate\Database\Eloquent\Collection
    {
        return Venue::query()
            ->with(['category', 'media'])
            ->published()
            ->whereKeyNot($venue->id)
            ->where(fn (Builder $query) => $query
                ->where('venue_category_id', $venue->venue_category_id)
                ->orWhere('city', $venue->city))
            ->orderByRaw('CASE WHEN venue_category_id = ? THEN 0 ELSE 1 END', [$venue->venue_category_id])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();
    }

    /**
     * @return array<int, string>
     */
    private function blockingBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
            BookingStatus::PendingOwner->value,
            BookingStatus::PendingPayment->value,
            BookingStatus::Confirmed->value,
            BookingStatus::Disputed->value,
        ];
    }

    /**
     * Exclude venues that are blocked, booked or temporarily locked on the requested dates.
     *
     * A venue without explicit calendar entries is considered available: only dates marked
     * as unavailable by the owner, active bookings and active holds remove it from results.
     */
    private function applyCalendarAvailabilityFilters(Builder $query, CarbonImmutable $rangeStartsAt, CarbonImmutable $rangeEndsAt): void
    {
        $query
            ->whereDoesntHave('availabilities', function (Builder $query) use ($rangeStartsAt, $rangeEndsAt): void {
                $query
                    ->where('status', '!=', 'available')
                    ->where('available_date', '>=', $rangeStartsAt->toDateString())
                    ->where('available_date', '<', $rangeEndsAt->addDay()->toDateString());
            })
            ->whereDoesntHave('bookings', function (Builder $query) use ($rangeStartsAt, $rangeEndsAt): void {
                $query->whereIn('status', $this->blockingBookingStatuses())
                    ->where('event_date', '>=', $rangeStartsAt->toDateString())
                    ->where('event_date', '<', $rangeEndsAt->addDay()->toDateString())
                    ->where(function (Builder $query): void {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now())
                            ->orWhereNot('status', BookingStatus::PendingPayment->value);
                    });
            })
            ->whereDoesntHave('availabilityHolds', function (Builder $query) use ($rangeStartsAt, $rangeEndsAt): void {
                $query->where('status', AvailabilityHoldStatus::Active->value)
                    ->where('expires_at', '>', now())
                    ->where('starts_at', '<', $rangeEndsAt->endOfDay())
                    ->where('ends_at', '>', $rangeStartsAt->startOfDay());
            });
    }

    /**
     * Build the searched date range, ignoring invalid dates and dates in the past.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    private function calendarRangeFromRequest(Request $request): ?array
    {
        $startsAt = $this->parseDate($request->input('start_date'));
        $endsAt = $this->parseDate($request->input('end_date'));

        if ($startsAt === null && $endsAt === null) {
            return null;
        }

        $startsAt ??= $endsAt;
        $endsAt ??= $startsAt;

        if ($endsAt->lessThan($startsAt)) {
            [$startsAt, $endsAt] = [$endsAt, $startsAt];
        }

        $today = CarbonImmutable::today();

        if ($endsAt->lessThan($today)) {
            return null;
        }

        return [$startsAt->max($today)->startOfDay(), $endsAt->endOfDay()];
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value) ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
