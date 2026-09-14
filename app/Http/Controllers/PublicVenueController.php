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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicVenueController extends Controller
{
    public function __construct(private readonly BookingDepositService $bookingDepositService) {}

    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->with(['category', 'media', 'ownerProfile'])
            ->where('status', VenueStatus::Published)
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->string('city')->toString()))
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', fn ($query) => $query->where('slug', $request->string('category')->toString()));
            })
            ->when($request->filled('capacity'), fn ($query) => $query->where('max_capacity', '>=', $request->integer('capacity')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('starting_price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('starting_price', '<=', $request->integer('max_price')))
            ->when($request->filled('start_date') || $request->filled('end_date'), fn (Builder $query) => $this->applyCalendarAvailabilityFilters($query, $request))
            ->orderByDesc('published_at')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $viewData = [
            'venues' => $venues,
            'categories' => VenueCategory::query()
                ->where('is_active', true)
                ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug']),
            'cities' => Venue::query()
                ->where('status', VenueStatus::Published)
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
            'searchSuggestions' => Venue::query()
                ->where('status', VenueStatus::Published)
                ->when($request->filled('start_date') || $request->filled('end_date'), fn (Builder $query) => $this->applyCalendarAvailabilityFilters($query, $request))
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
        ];

        if ($request->ajax()) {
            return view('venues.partials.results', [
                'venues' => $venues,
            ]);
        }

        return view('venues.index', $viewData);
    }

    public function blockedDates(Request $request, string $slug): JsonResponse
    {
        $venue = Venue::query()
            ->where('slug', $slug)
            ->where('status', VenueStatus::Published)
            ->firstOrFail();

        $from = filled($request->input('from'))
            ? CarbonImmutable::parse((string) $request->input('from'))->startOfDay()
            : CarbonImmutable::today();
        $to = filled($request->input('to'))
            ? CarbonImmutable::parse((string) $request->input('to'))->endOfDay()
            : $from->addMonths(6)->endOfDay();

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
                'rates',
                'configurations',
                'addOns',
                'policies',
                'faqs',
                'reviews.client',
            ])
            ->where('slug', $slug)
            ->first();

        $eligibleBooking = null;
        $existingReview = null;

        if ($venue && Auth::check()) {
            $eligibleBooking = Auth::user()->bookings()
                ->where('venue_id', $venue->id)
                ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
                ->latest('event_date')
                ->first();

            $existingReview = $venue->reviews()
                ->where('client_id', Auth::id())
                ->first();
        }

        $publicEventServices = EventService::query()
            ->with(['type', 'serviceProviderProfile'])
            ->where('status', VenueStatus::Published)
            ->when($venue?->city, fn ($query) => $query->orderByRaw('CASE WHEN city = ? THEN 0 ELSE 1 END', [$venue->city]))
            ->orderBy('starting_price')
            ->limit(24)
            ->get();

        $publicEventProviders = $publicEventServices
            ->groupBy(fn (EventService $service) => $service->service_provider_profile_id)
            ->map(function ($services) {
                $provider = $services->first()->serviceProviderProfile;

                return [
                    'provider' => $provider,
                    'services' => $services,
                ];
            })
            ->values();

        return view('venues.show', [
            'venue' => $venue,
            'fallback' => $this->fallbackVenue($slug),
            'depositAmount' => $venue ? $this->bookingDepositService->amountFor($venue, (int) $venue->starting_price) : null,
            'suggestedEventServices' => $publicEventServices,
            'publicEventProviders' => $publicEventProviders,
            'similarVenues' => $this->fallbackSimilarVenues($slug),
            'approvedReviews' => $venue?->reviews
                ?->where('status', 'approved')
                ->sortByDesc('approved_at')
                ->values() ?? collect(),
            'canReview' => $venue && $eligibleBooking && ! $existingReview,
            'existingReview' => $existingReview,
        ]);
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

    private function applyCalendarAvailabilityFilters(Builder $query, Request $request): void
    {
        [$rangeStartsAt, $rangeEndsAt] = $this->calendarRangeFromRequest($request);

        $query->whereHas('availabilities', function (Builder $query) use ($rangeStartsAt, $rangeEndsAt): void {
            $query
                ->where('status', 'available')
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
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function calendarRangeFromRequest(Request $request): array
    {
        $startsAt = $request->filled('start_date')
            ? CarbonImmutable::parse((string) $request->input('start_date'))->startOfDay()
            : CarbonImmutable::today();
        $endsAt = $request->filled('end_date')
            ? CarbonImmutable::parse((string) $request->input('end_date'))->endOfDay()
            : $startsAt->endOfDay();

        if ($endsAt->lessThan($startsAt)) {
            return [$endsAt->startOfDay(), $startsAt->endOfDay()];
        }

        return [$startsAt, $endsAt];
    }

    public function reservation(string $slug): View
    {
        $venue = Venue::query()
            ->with([
                'category',
                'ownerProfile.depositRules',
                'amenities',
                'media',
                'rates',
                'configurations',
                'addOns',
                'policies',
                'faqs',
                'reviews.client',
            ])
            ->where('slug', $slug)
            ->where('status', VenueStatus::Published)
            ->firstOrFail();

        $publicEventServices = EventService::query()
            ->with(['type', 'serviceProviderProfile'])
            ->where('status', VenueStatus::Published)
            ->when($venue->city, fn ($query) => $query->orderByRaw('CASE WHEN city = ? THEN 0 ELSE 1 END', [$venue->city]))
            ->orderBy('starting_price')
            ->limit(24)
            ->get();

        $publicEventProviders = $publicEventServices
            ->groupBy(fn (EventService $service) => $service->service_provider_profile_id)
            ->map(function ($services) {
                $provider = $services->first()->serviceProviderProfile;

                return [
                    'provider' => $provider,
                    'services' => $services,
                ];
            })
            ->values();

        $addOns = $venue->addOns()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $reservationAddOns = $addOns->isNotEmpty()
            ? $addOns->map(fn ($addOn) => [
                'id' => $addOn->id,
                'source' => 'venue_add_on',
                'name' => $addOn->name,
                'amount' => (int) $addOn->price,
                'currency' => $addOn->currency,
                'price' => number_format($addOn->price, 0, ',', ' ').' '.$addOn->currency,
            ])
            : $venue->ownerProfile
                ?->moduleTemplates()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn ($module) => [
                    'id' => $module->id,
                    'source' => 'owner_module_template',
                    'name' => $module->name,
                    'amount' => (int) $module->price,
                    'currency' => $module->currency,
                    'price' => number_format($module->price, 0, ',', ' ').' '.$module->currency,
                ]);

        return view('venues.reservation', [
            'venue' => $venue,
            'depositAmount' => $this->bookingDepositService->amountFor($venue, (int) $venue->starting_price),
            'publicEventProviders' => $publicEventProviders,
            'suggestedEventServices' => $publicEventServices,
            'reservationAddOns' => ($reservationAddOns ?? collect())->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackVenue(string $slug): array
    {
        $venues = [
            'auditorium-premium-avec-scene-et-regie' => [
                'title' => 'Auditorium premium avec scene et regie',
                'category' => 'Salle de spectacle',
                'city' => 'Abidjan',
                'district' => 'Plateau',
                'capacity' => '80-450 invites',
                'surface' => '650 m2',
                'price' => '350 000 XOF',
                'reservation' => '100 000 XOF',
                'rating' => '4.9',
                'reviews' => 38,
                'description' => 'Un auditorium central pense pour conferences, lancements de produits, concerts intimistes et ceremonies premium.',
                'images' => [
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            'salle-de-reception-lumineuse-pour-mariage' => [
                'title' => 'Salle de reception lumineuse pour mariage',
                'category' => 'Salle de mariage',
                'city' => 'Abidjan',
                'district' => 'Cocody',
                'capacity' => '120-700 invites',
                'surface' => '1 100 m2',
                'price' => '500 000 XOF',
                'reservation' => '150 000 XOF',
                'rating' => '5.0',
                'reviews' => 52,
                'description' => 'Une grande salle lumineuse avec terrasse, parking et espace traiteur pour receptions, mariages et grands diners.',
                'images' => [
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            'conference-room-equipee-avec-terrasse' => [
                'title' => 'Conference room equipee avec terrasse',
                'category' => 'Salle de conference',
                'city' => 'Dakar',
                'district' => 'Almadies',
                'capacity' => '12-90 invites',
                'surface' => '240 m2',
                'price' => '85 000 XOF',
                'reservation' => '35 000 XOF',
                'rating' => '4.8',
                'reviews' => 24,
                'description' => 'Un espace professionnel lumineux avec terrasse, Wi-Fi haut debit, mobilier modulable et equipements de presentation.',
                'images' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            'jardin-evenementiel-privatisable' => [
                'title' => 'Jardin evenementiel privatisable',
                'category' => 'Jardin evenementiel',
                'city' => 'Cotonou',
                'district' => 'Fidjrosse',
                'capacity' => '60-400 invites',
                'surface' => '1 500 m2',
                'price' => '220 000 XOF',
                'reservation' => '80 000 XOF',
                'rating' => '4.9',
                'reviews' => 31,
                'description' => 'Un jardin privatisable pour receptions, cocktails, ceremonies en plein air et evenements de marque.',
                'images' => [
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=85',
                ],
            ],
        ];

        return $venues[$slug] ?? $venues['auditorium-premium-avec-scene-et-regie'];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fallbackSimilarVenues(string $currentSlug): array
    {
        return collect([
            [
                'title' => 'Auditorium premium avec scene et regie',
                'slug' => 'auditorium-premium-avec-scene-et-regie',
                'city' => 'Abidjan, Plateau',
                'capacity' => '80-450 invites',
                'price' => 'A partir de 350 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Salle de reception lumineuse pour mariage',
                'slug' => 'salle-de-reception-lumineuse-pour-mariage',
                'city' => 'Abidjan, Cocody',
                'capacity' => '120-700 invites',
                'price' => 'A partir de 500 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Conference room equipee avec terrasse',
                'slug' => 'conference-room-equipee-avec-terrasse',
                'city' => 'Dakar, Almadies',
                'capacity' => '12-90 invites',
                'price' => 'A partir de 85 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Jardin evenementiel privatisable',
                'slug' => 'jardin-evenementiel-privatisable',
                'city' => 'Cotonou, Fidjrosse',
                'capacity' => '60-400 invites',
                'price' => 'A partir de 220 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=85',
            ],
        ])
            ->reject(fn (array $venue): bool => $venue['slug'] === $currentSlug)
            ->take(3)
            ->values()
            ->all();
    }
}
