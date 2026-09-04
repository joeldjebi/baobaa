<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\OwnerModuleTemplate;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\PortalAccessRequest;
use App\Models\SponsorshipCampaign;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueAvailability;
use App\Models\VenueCategory;
use App\Models\VenueReview;
use App\Services\BookingWorkflowService;
use App\Services\PartnerLogoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly BookingWorkflowService $bookingWorkflowService) {}

    public function sap(): View
    {
        return view('dashboards.sap', [
            'pendingAccessRequestsCount' => PortalAccessRequest::query()->where('status', 'pending')->count(),
            'pendingSponsorshipsCount' => SponsorshipCampaign::query()->where('status', 'pending')->count(),
            'ownersCount' => OwnerProfile::query()->count(),
            'publishedVenuesCount' => Venue::query()->where('status', VenueStatus::Published)->count(),
        ]);
    }

    public function owner(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'recentBookings' => $this->ownerBookingsQuery($ownerProfile)->latest()->limit(6)->get(),
            'topVenues' => $ownerProfile->venues()
                ->withCount('bookings')
                ->orderByDesc('bookings_count')
                ->limit(4)
                ->get(),
            'monthlyRevenue' => $this->monthlyRevenue($ownerProfile),
        ]);
    }

    public function client(Request $request): View
    {
        $client = $request->user();

        return view('dashboards.client', [
            ...$this->clientMetricsFor($client),
            'client' => $client,
            'upcomingBookings' => Booking::query()
                ->with(['venue.ownerProfile', 'payments'])
                ->where('client_id', $client->id)
                ->whereDate('event_date', '>=', now()->toDateString())
                ->latest('event_date')
                ->limit(5)
                ->get(),
            'recentPayments' => Payment::query()
                ->with('booking.venue')
                ->where('payer_id', $client->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function clientReservations(Request $request): View
    {
        $client = $request->user();

        return view('dashboards.client-reservations', [
            ...$this->clientMetricsFor($client),
            'client' => $client,
            'bookings' => Booking::query()
                ->with(['venue.ownerProfile', 'payments'])
                ->where('client_id', $client->id)
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('reference', 'like', $search)
                            ->orWhereHas('venue', fn (Builder $query) => $query->where('name', 'like', $search));
                    });
                })
                ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('event_date', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('event_date', '<=', $request->date('date_to')))
                ->latest('event_date')
                ->paginate(10, ['*'], 'bookings_page')
                ->withQueryString(),
        ]);
    }

    public function clientBookingShow(Request $request, Booking $booking): View
    {
        $client = $request->user();

        abort_unless((int) $booking->client_id === (int) $client->id, 404);

        $booking = $this->bookingWorkflowService->ensureReadyForNegotiation($booking);

        return view('dashboards.client-booking-show', [
            ...$this->clientMetricsFor($client),
            'client' => $client,
            'booking' => $booking->load([
                'venue.media',
                'venue.category',
                'venue.ownerProfile.user',
                'payments',
                'messages.sender',
                'proformaInvoice.items',
            ]),
        ]);
    }

    public function ownerVenues(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner.venues', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'venues' => $ownerProfile->venues()
                ->with(['category'])
                ->withCount(['bookings', 'media'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), fn (Builder $query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))
                ->latest()
                ->paginate(8, ['*'], 'venues_page')
                ->withQueryString(),
        ]);
    }

    public function ownerVenueCreate(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);
        $venue = null;

        $routeVenue = $request->route('venue');
        $venueId = $routeVenue instanceof Venue
            ? $routeVenue->id
            : (int) ($routeVenue ?: $request->integer('venue'));

        if ($venueId) {
            $venue = $ownerProfile->venues()
                ->with(['addOns', 'configurations', 'media', 'policies', 'faqs'])
                ->whereKey($venueId)
                ->firstOrFail();
        }

        return view('dashboards.owner.venue-create', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'categories' => VenueCategory::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'moduleTemplates' => $ownerProfile->moduleTemplates()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'currentVenue' => $venue,
            'currentStep' => $request->string('step')->toString() ?: 'base',
        ]);
    }

    public function ownerBookings(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner.bookings', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'bookings' => $this->ownerBookingsQuery($ownerProfile)
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $query->where(function (Builder $query) use ($request): void {
                        $search = '%'.$request->string('q')->toString().'%';
                        $query->where('reference', 'like', $search)
                            ->orWhereHas('venue', fn (Builder $query) => $query->where('name', 'like', $search))
                            ->orWhereHas('client', fn (Builder $query) => $query->where('name', 'like', $search));
                    });
                })
                ->latest()
                ->paginate(8, ['*'], 'bookings_page')
                ->withQueryString(),
        ]);
    }

    public function ownerBookingShow(Request $request, Booking $booking): View
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $booking->owner_profile_id === (int) $ownerProfile->id, 403);

        $booking = $this->bookingWorkflowService->ensureReadyForNegotiation($booking);

        return view('dashboards.owner.booking-show', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'booking' => $booking->load([
                'venue.media',
                'venue.category',
                'client',
                'payments',
                'commission',
                'payout',
                'messages.sender',
                'proformaInvoice.items',
            ]),
        ]);
    }

    public function ownerPayments(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner.payments', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'payments' => Payment::query()
                ->with(['booking.venue', 'payer'])
                ->whereHas('booking', fn (Builder $query) => $query->where('owner_profile_id', $ownerProfile->id))
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->latest()
                ->paginate(8, ['*'], 'payments_page')
                ->withQueryString(),
            'payoutEligibleBookings' => $this->ownerBookingsQuery($ownerProfile)
                ->with('payments')
                ->where('status', BookingStatus::Completed)
                ->whereDate('event_date', '<=', now()->subHours(48)->toDateString())
                ->whereHas('payments', fn (Builder $query) => $query->where('status', PaymentStatus::Succeeded))
                ->whereDoesntHave('payout')
                ->latest()
                ->limit(5)
                ->get(),
            'baobaaHistory' => $ownerProfile->subscriptions()
                ->with('subscriptionPlan')
                ->latest()
                ->limit(6)
                ->get(),
            'commissionHistory' => $ownerProfile->payouts()
                ->where('commission_amount', '>', 0)
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    public function ownerCalendar(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner.calendar', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'availabilities' => VenueAvailability::query()
                ->with('venue')
                ->whereHas('venue', fn (Builder $query) => $query->where('owner_profile_id', $ownerProfile->id))
                ->when($request->filled('venue_id'), fn (Builder $query) => $query->where('venue_id', $request->integer('venue_id')))
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('available_date', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('available_date', '<=', $request->date('date_to')))
                ->whereDate('available_date', '>=', now()->toDateString())
                ->orderBy('available_date')
                ->paginate(10, ['*'], 'calendar_page')
                ->withQueryString(),
            'calendarVenues' => $ownerProfile->venues()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function ownerAddOns(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);
        $moduleStatsQuery = $ownerProfile->moduleTemplates();

        return view('dashboards.owner.addons', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'modulesActiveCount' => (clone $moduleStatsQuery)->where('is_active', true)->count(),
            'modulesInactiveCount' => (clone $moduleStatsQuery)->where('is_active', false)->count(),
            'modulesAveragePrice' => (int) (clone $moduleStatsQuery)->avg('price'),
            'moduleTemplates' => $ownerProfile->moduleTemplates()
                ->when($request->filled('status'), fn (Builder $query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
                ->when($request->filled('q'), fn (Builder $query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))
                ->latest()
                ->paginate(10, ['*'], 'addons_page')
                ->withQueryString(),
        ]);
    }

    public function storeOwnerAddOn(Request $request): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
        ]);

        $ownerProfile->moduleTemplates()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => 'XOF',
            'is_active' => true,
            'sort_order' => $ownerProfile->moduleTemplates()->count() + 1,
        ]);

        return back()->with('addon_status', 'Module ajouté à votre bibliothèque.');
    }

    public function updateOwnerModule(Request $request, OwnerModuleTemplate $module): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $module->owner_profile_id === (int) $ownerProfile->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
        ]);

        $module->update($validated);

        return back()->with('addon_status', 'Module mis à jour.');
    }

    public function toggleOwnerModule(Request $request, OwnerModuleTemplate $module): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $module->owner_profile_id === (int) $ownerProfile->id, 403);

        $module->update(['is_active' => ! $module->is_active]);

        return back()->with('addon_status', $module->is_active ? 'Module activé.' : 'Module désactivé.');
    }

    public function deleteOwnerModule(Request $request, OwnerModuleTemplate $module): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);

        abort_unless((int) $module->owner_profile_id === (int) $ownerProfile->id, 403);

        $module->delete();

        return back()->with('addon_status', 'Module supprimé de votre bibliothèque.');
    }

    public function ownerReviews(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner.reviews', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'reviews' => VenueReview::query()
                ->with(['venue', 'client'])
                ->whereHas('venue', fn (Builder $query) => $query->where('owner_profile_id', $ownerProfile->id))
                ->latest()
                ->paginate(8, ['*'], 'reviews_page')
                ->withQueryString(),
        ]);
    }

    public function ownerSettings(Request $request): View
    {
        $ownerProfile = $this->ownerProfile($request);

        return view('dashboards.owner.settings', [
            ...$this->ownerMetrics($ownerProfile),
            'ownerProfile' => $ownerProfile,
            'plans' => SubscriptionPlan::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function updateOwnerSettings(Request $request): RedirectResponse
    {
        $ownerProfile = $this->ownerProfile($request);
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'whatsapp_phone' => ['nullable', 'string', 'max:32'],
            'payout_provider' => ['required', 'string', 'max:80'],
            'payout_account_reference' => ['required', 'string', 'max:255'],
            'billing_preference' => ['required', 'in:commission,subscription,hybrid'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $ownerProfile->update(Arr::except($validated, ['logo']));

        if ($request->hasFile('logo')) {
            app(PartnerLogoService::class)->store(
                $ownerProfile,
                $request->file('logo'),
                $ownerProfile->business_name,
            );
        }

        return back()->with('settings_status', 'Paramètres enregistrés.');
    }

    private function ownerProfile(Request $request): OwnerProfile
    {
        return $request->user()->ownerProfile()->firstOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'owner_type' => 'company',
            'business_name' => $request->user()->name,
            'verification_status' => 'pending',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'billing_preference' => 'commission',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function ownerMetrics(OwnerProfile $ownerProfile): array
    {
        $venueIds = $ownerProfile->venues()->pluck('id');
        $bookingQuery = Booking::query()->where('owner_profile_id', $ownerProfile->id);
        $paidPaymentQuery = Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereHas('booking', fn (Builder $query) => $query->where('owner_profile_id', $ownerProfile->id));

        return [
            'activeVenuesCount' => Venue::query()->whereIn('id', $venueIds)->where('status', VenueStatus::Published)->count(),
            'pendingBookingsCount' => (clone $bookingQuery)->whereIn('status', [BookingStatus::PendingOwner, BookingStatus::PendingPayment])->count(),
            'confirmedBookingsCount' => (clone $bookingQuery)->where('status', BookingStatus::Confirmed)->count(),
            'grossRevenue' => (int) (clone $paidPaymentQuery)->sum('amount'),
            'averageRating' => (float) Venue::query()->whereIn('id', $venueIds)->avg('average_rating'),
            'reviewsCount' => VenueReview::query()->whereIn('venue_id', $venueIds)->count(),
            'activeSubscription' => $ownerProfile->subscriptions()
                ->with('subscriptionPlan')
                ->whereIn('status', ['active', 'trialing'])
                ->latest('ends_on')
                ->first(),
            'activeDepositRule' => $ownerProfile->depositRules()
                ->where('is_active', true)
                ->where(function (Builder $query): void {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function (Builder $query): void {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->latest('starts_at')
                ->latest('id')
                ->first(),
            'billingPreferenceLabel' => match ($ownerProfile->billing_preference ?? 'commission') {
                'subscription' => 'Abonnement',
                'hybrid' => 'Abonnement + commission',
                default => 'Commission',
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ownerMetricsFor(OwnerProfile $ownerProfile): array
    {
        return $this->ownerMetrics($ownerProfile);
    }

    private function ownerBookingsQuery(OwnerProfile $ownerProfile): Builder
    {
        return Booking::query()
            ->with(['venue', 'client'])
            ->where('owner_profile_id', $ownerProfile->id);
    }

    /**
     * @return array<string, int>
     */
    public function clientMetricsFor(User $client): array
    {
        return [
            'upcomingBookingsCount' => Booking::query()
                ->where('client_id', $client->id)
                ->whereDate('event_date', '>=', now()->toDateString())
                ->whereIn('status', [BookingStatus::PendingPayment, BookingStatus::PendingOwner, BookingStatus::Confirmed])
                ->count(),
            'confirmedPaymentsAmount' => (int) Payment::query()
                ->where('payer_id', $client->id)
                ->where('status', PaymentStatus::Succeeded)
                ->sum('amount'),
            'reservedVenuesCount' => Booking::query()
                ->where('client_id', $client->id)
                ->distinct('venue_id')
                ->count('venue_id'),
            'pendingPaymentsCount' => Payment::query()
                ->where('payer_id', $client->id)
                ->whereIn('status', [PaymentStatus::Initiated, PaymentStatus::Pending])
                ->count(),
        ];
    }

    /**
     * @return array<int, array{label: string, amount: int}>
     */
    private function monthlyRevenue(OwnerProfile $ownerProfile): array
    {
        return collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($ownerProfile): array {
                $date = now()->subMonths($monthsAgo);
                $amount = Payment::query()
                    ->where('status', PaymentStatus::Succeeded)
                    ->whereYear('paid_at', $date->year)
                    ->whereMonth('paid_at', $date->month)
                    ->whereHas('booking', fn (Builder $query) => $query->where('owner_profile_id', $ownerProfile->id))
                    ->sum('amount');

                return [
                    'label' => ucfirst($date->translatedFormat('M')),
                    'amount' => (int) $amount,
                ];
            })
            ->values()
            ->all();
    }
}
