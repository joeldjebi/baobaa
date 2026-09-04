<?php

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Http\Controllers\Auth\ClientRegisteredUserController;
use App\Http\Controllers\Auth\PortalAuthenticatedSessionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingMessageController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventComposerController;
use App\Http\Controllers\EventServiceController;
use App\Http\Controllers\OwnerBookingStatusController;
use App\Http\Controllers\OwnerPayoutController;
use App\Http\Controllers\OwnerRegisteredUserController;
use App\Http\Controllers\OwnerSponsorshipController;
use App\Http\Controllers\OwnerVenueDraftController;
use App\Http\Controllers\OwnerVenueMediaController;
use App\Http\Controllers\OwnerVenueStatusController;
use App\Http\Controllers\PortalAccessController;
use App\Http\Controllers\ProformaInvoiceConfirmationController;
use App\Http\Controllers\PublicOwnerProfileController;
use App\Http\Controllers\PublicVenueController;
use App\Http\Controllers\SapDashboardController;
use App\Http\Controllers\SapPortalRequestController;
use App\Http\Controllers\ServiceProviderDashboardController;
use App\Http\Controllers\ServiceProviderRegisteredUserController;
use App\Http\Controllers\TestPaymentController;
use App\Http\Controllers\VenueReviewController;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $categories = VenueCategory::query()
        ->where('is_active', true)
        ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['name', 'slug', 'icon']);

    return view('welcome', [
        'categories' => $categories,
        'featuredVenues' => Venue::query()
            ->with(['category', 'media'])
            ->withCount(['bookings', 'reviews'])
            ->where('status', VenueStatus::Published)
            ->orderByDesc('bookings_count')
            ->orderByDesc('reviews_count')
            ->orderByDesc('published_at')
            ->limit(12)
            ->get(),
        'categoryCarouselVenues' => Venue::query()
            ->with(['category', 'media'])
            ->where('status', VenueStatus::Published)
            ->whereNotNull('venue_category_id')
            ->inRandomOrder()
            ->limit(12)
            ->get(),
        'cityCarouselVenues' => Venue::query()
            ->with(['category', 'media'])
            ->where('status', VenueStatus::Published)
            ->whereNotNull('city')
            ->inRandomOrder()
            ->limit(12)
            ->get(),
        'districtCarouselVenues' => Venue::query()
            ->with(['category', 'media'])
            ->where('status', VenueStatus::Published)
            ->whereNotNull('district')
            ->inRandomOrder()
            ->limit(12)
            ->get(),
        'cityHighlights' => Venue::query()
            ->where('status', VenueStatus::Published)
            ->select('city', DB::raw('count(*) as venues_count'), DB::raw('min(starting_price) as starting_price'))
            ->groupBy('city')
            ->orderByDesc('venues_count')
            ->limit(6)
            ->get(),
        'categoryHighlights' => VenueCategory::query()
            ->where('is_active', true)
            ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
            ->withCount(['venues as published_venues_count' => fn ($query) => $query->where('status', VenueStatus::Published)])
            ->orderByDesc('published_venues_count')
            ->orderBy('sort_order')
            ->limit(6)
            ->get(['id', 'name', 'slug', 'icon']),
        'searchSuggestions' => Venue::query()
            ->where('status', VenueStatus::Published)
            ->orderBy('name')
            ->limit(12)
            ->get(['name'])
            ->map(fn (Venue $venue): array => ['label' => $venue->name, 'type' => 'Espace'])
            ->merge($categories->map(fn (VenueCategory $category): array => ['label' => $category->name, 'type' => 'Catégorie']))
            ->values(),
        'citySuggestions' => Venue::query()
            ->where('status', VenueStatus::Published)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values(),
    ]);
});

Route::redirect('/login', '/client/login')->name('login');

Route::get('/lister-un-espace', function () {
    $user = auth()->user();

    if ($user?->hasPortal(UserRole::Owner)) {
        return redirect()->route('owner.venues.create');
    }

    if ($user) {
        return redirect()->route('portals.owner.request.form');
    }

    return redirect()->route('owner.register');
})->name('venues.list-venue');
Route::get('/espaces', [PublicVenueController::class, 'index'])->name('venues.index');
Route::get('/espaces/{slug}', [PublicVenueController::class, 'show'])->name('venues.show');
Route::get('/composer-mon-evenement', [EventComposerController::class, 'create'])->name('event-composer.create');
Route::post('/espaces/{venue}/reservation', [BookingController::class, 'store'])
    ->middleware(['auth', 'role:client'])
    ->name('bookings.store');
Route::post('/espaces/{venue}/avis', [VenueReviewController::class, 'store'])
    ->middleware(['auth', 'role:client'])
    ->name('venues.reviews.store');
Route::get('/partenaires', [PublicOwnerProfileController::class, 'index'])->name('owner-profiles.index');
Route::get('/partenaires/{ownerProfile}', [PublicOwnerProfileController::class, 'show'])->name('owner-profiles.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/client/inscription', [ClientRegisteredUserController::class, 'create'])->name('client.register');
    Route::post('/client/inscription', [ClientRegisteredUserController::class, 'store'])->name('client.register.store');
    Route::get('/proprietaire/inscription', [OwnerRegisteredUserController::class, 'create'])->name('owner.register');
    Route::post('/proprietaire/inscription', [OwnerRegisteredUserController::class, 'store'])->name('owner.register.store');
    Route::get('/prestataire/inscription', [ServiceProviderRegisteredUserController::class, 'create'])->name('service-provider.register');
    Route::post('/prestataire/inscription', [ServiceProviderRegisteredUserController::class, 'store'])->name('service-provider.register.store');

    Route::get('/{portal}/login', [PortalAuthenticatedSessionController::class, 'create'])
        ->whereIn('portal', ['sap', 'proprietaire', 'client', 'prestataire'])
        ->name('portal.login');

    Route::post('/{portal}/login', [PortalAuthenticatedSessionController::class, 'store'])
        ->whereIn('portal', ['sap', 'proprietaire', 'client', 'prestataire'])
        ->name('portal.login.store');
});

Route::post('/logout', [PortalAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::post('/portails/client', [PortalAccessController::class, 'becomeClient'])->name('portals.client.enable');
    Route::post('/portails/proprietaire', [PortalAccessController::class, 'becomeOwner'])->name('portals.owner.enable');
    Route::get('/portails/proprietaire/demande', [PortalAccessController::class, 'ownerApplicationForm'])->name('portals.owner.request.form');
    Route::post('/portails/proprietaire/demande', [PortalAccessController::class, 'requestOwnerAccess'])->name('portals.owner.request');
    Route::post('/portails/prestataire', [PortalAccessController::class, 'becomeServiceProvider'])->name('portals.service-provider.enable');
    Route::get('/portails/prestataire/demande', [PortalAccessController::class, 'serviceProviderApplicationForm'])->name('portals.service-provider.request.form');
    Route::post('/portails/prestataire/demande', [PortalAccessController::class, 'requestServiceProviderAccess'])->name('portals.service-provider.request');
});

Route::middleware(['auth', 'role:sap'])->group(function (): void {
    Route::get('/sap/dashboard', [SapDashboardController::class, 'overview'])->name('sap.dashboard');
    Route::get('/sap/partenaires', [SapDashboardController::class, 'owners'])->name('sap.owners');
    Route::get('/sap/clients', [SapDashboardController::class, 'clients'])->name('sap.clients');
    Route::post('/sap/users/{user}/statut', [SapDashboardController::class, 'updateUserStatus'])->name('sap.users.status');
    Route::get('/sap/espaces', [SapDashboardController::class, 'venues'])->name('sap.venues');
    Route::post('/sap/espaces/{venue}/statut', [SapDashboardController::class, 'updateVenueStatus'])->name('sap.venues.status');
    Route::get('/sap/reservations', [SapDashboardController::class, 'bookings'])->name('sap.bookings');
    Route::post('/sap/reservations/{booking}/statut', [SapDashboardController::class, 'updateBookingStatus'])->name('sap.bookings.status');
    Route::get('/sap/paiements', [SapDashboardController::class, 'payments'])->name('sap.payments');
    Route::post('/sap/paiements/{payment}/statut', [SapDashboardController::class, 'updatePaymentStatus'])->name('sap.payments.status');
    Route::get('/sap/forfaits-abonnement', [SapDashboardController::class, 'subscriptionPlans'])->name('sap.subscription-plans');
    Route::post('/sap/forfaits-abonnement', [SapDashboardController::class, 'storeSubscriptionPlan'])->name('sap.subscription-plans.store');
    Route::post('/sap/forfaits-abonnement/{subscriptionPlan}/statut', [SapDashboardController::class, 'toggleSubscriptionPlan'])->name('sap.subscription-plans.toggle');
    Route::get('/sap/commissions', [SapDashboardController::class, 'commissions'])->name('sap.commissions');
    Route::post('/sap/commissions', [SapDashboardController::class, 'storeCommission'])->name('sap.commissions.store');
    Route::post('/sap/commissions/{commissionRule}/statut', [SapDashboardController::class, 'toggleCommission'])->name('sap.commissions.toggle');
    Route::get('/sap/acomptes', [SapDashboardController::class, 'depositRules'])->name('sap.deposit-rules');
    Route::post('/sap/acomptes', [SapDashboardController::class, 'storeDepositRule'])->name('sap.deposit-rules.store');
    Route::post('/sap/acomptes/{ownerDepositRule}/statut', [SapDashboardController::class, 'toggleDepositRule'])->name('sap.deposit-rules.toggle');
    Route::get('/sap/types-services', [SapDashboardController::class, 'serviceTypes'])->name('sap.service-types');
    Route::post('/sap/types-services', [SapDashboardController::class, 'storeServiceType'])->name('sap.service-types.store');
    Route::post('/sap/types-services/{eventServiceType}/statut', [SapDashboardController::class, 'toggleServiceType'])->name('sap.service-types.toggle');
    Route::get('/sap/forfaits-sponsoring', [SapDashboardController::class, 'sponsorshipPlans'])->name('sap.sponsorship-plans');
    Route::post('/sap/forfaits-sponsoring', [SapDashboardController::class, 'storeSponsorshipPlan'])->name('sap.sponsorship-plans.store');
    Route::post('/sap/forfaits-sponsoring/{sponsorshipPlan}/statut', [SapDashboardController::class, 'toggleSponsorshipPlan'])->name('sap.sponsorship-plans.toggle');
    Route::get('/sap/demandes-portails', [SapPortalRequestController::class, 'index'])->name('sap.portal-requests');
    Route::post('/sap/demandes-portails/{portalAccessRequest}/decision', [SapPortalRequestController::class, 'decide'])->name('sap.portal-requests.decide');
    Route::post('/sap/sponsorings/{sponsorshipCampaign}/decision', [SapPortalRequestController::class, 'decideSponsorship'])->name('sap.sponsorships.decide');
});

Route::middleware(['auth', 'role:service_provider'])->group(function (): void {
    Route::get('/prestataire/dashboard', [ServiceProviderDashboardController::class, 'overview'])->name('service-provider.dashboard');
    Route::get('/prestataire/services', [ServiceProviderDashboardController::class, 'services'])->name('service-provider.services');
    Route::get('/prestataire/services/nouveau', [ServiceProviderDashboardController::class, 'serviceForm'])->name('service-provider.services.create');
    Route::get('/prestataire/services/{eventService}/modifier', [ServiceProviderDashboardController::class, 'serviceForm'])->name('service-provider.services.edit');
    Route::post('/prestataire/services', [EventServiceController::class, 'store'])->name('service-provider.services.store');
    Route::patch('/prestataire/services/{eventService}', [EventServiceController::class, 'update'])->name('service-provider.services.update');
    Route::post('/prestataire/services/{eventService}/statut', [EventServiceController::class, 'toggle'])->name('service-provider.services.toggle');
    Route::delete('/prestataire/services/{eventService}', [EventServiceController::class, 'destroy'])->name('service-provider.services.destroy');
    Route::get('/prestataire/parametres', [ServiceProviderDashboardController::class, 'settings'])->name('service-provider.settings');
    Route::patch('/prestataire/parametres', [EventServiceController::class, 'updateSettings'])->name('service-provider.settings.update');
});

Route::middleware(['auth', 'role:owner'])->group(function (): void {
    Route::get('/proprietaire/dashboard', [DashboardController::class, 'owner'])->name('owner.dashboard');
    Route::get('/proprietaire/espaces', [DashboardController::class, 'ownerVenues'])->name('owner.venues');
    Route::get('/proprietaire/espaces/nouveau', [DashboardController::class, 'ownerVenueCreate'])->name('owner.venues.create');
    Route::get('/proprietaire/espaces/{venue}/modifier', [DashboardController::class, 'ownerVenueCreate'])->name('owner.venues.edit');
    Route::post('/proprietaire/espaces/brouillon', [OwnerVenueDraftController::class, 'store'])->name('owner.venues.draft.store');
    Route::delete('/proprietaire/espaces/{venue}/medias/{venueMedia}', [OwnerVenueMediaController::class, 'destroy'])->name('owner.venues.media.destroy');
    Route::post('/proprietaire/espaces/{venue}/statut', [OwnerVenueStatusController::class, 'update'])->name('owner.venues.status');
    Route::get('/proprietaire/reservations', [DashboardController::class, 'ownerBookings'])->name('owner.bookings');
    Route::get('/proprietaire/reservations/{booking}', [DashboardController::class, 'ownerBookingShow'])->name('owner.bookings.show');
    Route::post('/proprietaire/reservations/{booking}/statut', [OwnerBookingStatusController::class, 'update'])->name('owner.bookings.status');
    Route::post('/proprietaire/reservations/{booking}/messages', [BookingMessageController::class, 'store'])->name('owner.bookings.messages.store');
    Route::post('/proprietaire/reservations/{booking}/proforma/confirmer', [ProformaInvoiceConfirmationController::class, 'store'])->name('owner.bookings.proforma.confirm');
    Route::get('/proprietaire/paiements', [DashboardController::class, 'ownerPayments'])->name('owner.payments');
    Route::get('/proprietaire/sponsorings', [OwnerSponsorshipController::class, 'index'])->name('owner.sponsorships');
    Route::post('/proprietaire/sponsorings', [OwnerSponsorshipController::class, 'store'])->name('owner.sponsorships.store');
    Route::post('/proprietaire/sponsorings/{sponsorshipCampaign}/statut', [OwnerSponsorshipController::class, 'updateStatus'])->name('owner.sponsorships.status');
    Route::post('/proprietaire/reservations/{booking}/reversement', [OwnerPayoutController::class, 'store'])->name('owner.payouts.store');
    Route::get('/proprietaire/disponibilites', [DashboardController::class, 'ownerCalendar'])->name('owner.calendar');
    Route::get('/proprietaire/modules', [DashboardController::class, 'ownerAddOns'])->name('owner.addons');
    Route::post('/proprietaire/modules', [DashboardController::class, 'storeOwnerAddOn'])->name('owner.addons.store');
    Route::patch('/proprietaire/modules/{module}', [DashboardController::class, 'updateOwnerModule'])->name('owner.addons.update');
    Route::post('/proprietaire/modules/{module}/statut', [DashboardController::class, 'toggleOwnerModule'])->name('owner.addons.toggle');
    Route::delete('/proprietaire/modules/{module}', [DashboardController::class, 'deleteOwnerModule'])->name('owner.addons.delete');
    Route::get('/proprietaire/avis', [DashboardController::class, 'ownerReviews'])->name('owner.reviews');
    Route::get('/proprietaire/parametres', [DashboardController::class, 'ownerSettings'])->name('owner.settings');
    Route::post('/proprietaire/parametres', [DashboardController::class, 'updateOwnerSettings'])->name('owner.settings.update');
});

Route::middleware(['auth', 'role:client'])->group(function (): void {
    Route::get('/client/dashboard', [DashboardController::class, 'client'])->name('client.dashboard');
    Route::get('/client/evenements', [DashboardController::class, 'clientProjects'])->name('client.projects');
    Route::post('/client/evenements/composer', [EventComposerController::class, 'store'])->name('event-composer.store');
    Route::get('/client/reservations', [DashboardController::class, 'clientReservations'])->name('client.reservations');
    Route::get('/client/reservations/{booking}', [DashboardController::class, 'clientBookingShow'])->name('client.reservations.show');
    Route::post('/client/reservations/{booking}/messages', [BookingMessageController::class, 'store'])->name('client.reservations.messages.store');
    Route::post('/client/reservations/{booking}/proforma/confirmer', [ProformaInvoiceConfirmationController::class, 'store'])->name('client.reservations.proforma.confirm');
    Route::post('/client/reservations/{booking}/paiement-test', [TestPaymentController::class, 'store'])->name('client.reservations.test-payment');
    Route::get('/client/paiements', [ClientProfileController::class, 'payments'])->name('client.payments');
    Route::get('/client/profil', [ClientProfileController::class, 'edit'])->name('client.profile');
    Route::patch('/client/profil', [ClientProfileController::class, 'update'])->name('client.profile.update');
    Route::put('/client/mot-de-passe', [ClientProfileController::class, 'password'])->name('client.password.update');
});
