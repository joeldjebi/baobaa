<?php

use App\Enums\AvailabilityHoldStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\AvailabilityHold;
use App\Models\Booking;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\OwnerProfile;
use App\Models\PortalAccessRequest;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use App\Models\VenueReview;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public home targets final clients without exposing internal portals', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Réservez le lieu parfait')
        ->assertSee(route('venues.index'))
        ->assertDontSee('Portail SAP')
        ->assertDontSee('Proprietaire');
});

test('web responses include browser security headers', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=(self)')
        ->assertHeader('Content-Security-Policy');
});

test('public home only displays categories with published spaces', function () {
    $publishedCategory = VenueCategory::factory()->create([
        'name' => 'Salles publiées',
        'slug' => 'salles-publiees',
        'icon' => 'building',
    ]);

    VenueCategory::factory()->create([
        'name' => 'Catégorie vide',
        'slug' => 'categorie-vide',
        'icon' => 'sparkles',
    ]);

    Venue::factory()->create([
        'venue_category_id' => $publishedCategory->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Salles publiées')
        ->assertSee(route('venues.index', ['category' => 'salles-publiees']))
        ->assertDontSee('Catégorie vide');
});

test('public owner call to action redirects directly to owner registration', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('owner.register'))
        ->assertDontSee(route('portal.login', ['portal' => 'proprietaire']));

    $this->get(route('venues.list-venue'))
        ->assertRedirect(route('owner.register'));
});

test('public menu adapts when a client is connected', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'name' => 'Client Connecté',
    ]);

    $this->actingAs($client)
        ->get(route('venues.index'))
        ->assertOk()
        ->assertSeeText('Mes réservations')
        ->assertSee(route('client.reservations'))
        ->assertSeeText('Client Connecté')
        ->assertDontSeeText('Publier votre lieu')
        ->assertDontSeeText('Valoriser mon espace');
});

test('client can register himself', function () {
    $this->get(route('client.register'))
        ->assertOk()
        ->assertSeeText('Créer mon compte');

    $this->post(route('client.register.store'), [
        'name' => 'Nouveau Client',
        'email' => 'nouveau.client@baobaa.local',
        'phone' => '+2250102030405',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertRedirect(route('client.dashboard'));

    $user = User::query()->where('email', 'nouveau.client@baobaa.local')->first();

    expect($user)
        ->not->toBeNull()
        ->and($user->hasPortal(UserRole::Client))->toBeTrue();
});

test('owner can register himself but waits for SAP approval', function () {
    $this->get(route('owner.register'))
        ->assertOk()
        ->assertSeeText('Demande d’accès PEE');

    $this->post(route('owner.register.store'), [
        'name' => 'Responsable Partenaire',
        'email' => 'nouveau.pee@baobaa.local',
        'phone' => '+2250707070707',
        'password' => 'password',
        'password_confirmation' => 'password',
        'applicant_type' => 'company',
        'business_name' => 'Blue Event House',
        'legal_name' => 'Blue Event House SARL',
        'tax_identifier' => 'RCCM-CI-TEST',
        'country_code' => 'CI',
        'city' => 'Abidjan',
        'motivation' => 'Nous voulons publier nos espaces sur BAOBAA.',
    ])
        ->assertRedirect(route('client.dashboard'));

    $user = User::query()->where('email', 'nouveau.pee@baobaa.local')->firstOrFail();

    expect($user->hasPortal(UserRole::Client))->toBeTrue()
        ->and($user->hasPortal(UserRole::Owner))->toBeFalse()
        ->and(PortalAccessRequest::query()->where('user_id', $user->id)->where('status', 'pending')->exists())->toBeTrue();
});

test('public venues index lists published spaces and supports filters', function () {
    $conferenceCategory = VenueCategory::factory()->create([
        'name' => 'Salles de conférence',
        'slug' => 'salles-de-conference',
    ]);

    $gardenCategory = VenueCategory::factory()->create([
        'name' => 'Jardins événementiels',
        'slug' => 'jardins-evenementiels',
    ]);

    Venue::factory()->create([
        'venue_category_id' => $conferenceCategory->id,
        'name' => 'Salle conférence Riviera',
        'slug' => 'salle-conference-riviera',
        'status' => VenueStatus::Published,
        'city' => 'Abidjan',
        'district' => 'Riviera',
        'max_capacity' => 180,
        'starting_price' => 250000,
        'published_at' => now(),
    ])->availabilities()->create([
        'available_date' => now()->addDays(7)->toDateString(),
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'available',
    ]);

    Venue::factory()->create([
        'venue_category_id' => $gardenCategory->id,
        'name' => 'Jardin privé Dakar',
        'slug' => 'jardin-prive-dakar',
        'status' => VenueStatus::Published,
        'city' => 'Dakar',
        'district' => 'Almadies',
        'max_capacity' => 420,
        'starting_price' => 700000,
        'published_at' => now(),
    ])->availabilities()->create([
        'available_date' => now()->addDays(14)->toDateString(),
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'available',
    ]);

    Venue::factory()->create([
        'name' => 'Espace encore en brouillon',
        'status' => VenueStatus::Draft,
    ]);

    $this->get(route('venues.index'))
        ->assertOk()
        ->assertSeeText('Trouvez l’espace')
        ->assertSeeText('Salle conférence Riviera')
        ->assertSeeText('Jardin privé Dakar')
        ->assertDontSee('Espace encore en brouillon');

    $this->get(route('venues.index', [
        'city' => 'Abidjan',
        'category' => 'salles-de-conference',
        'capacity' => 120,
        'min_price' => 200000,
        'max_price' => 300000,
        'start_date' => now()->addDays(6)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ]))
        ->assertOk()
        ->assertSeeText('Salle conférence Riviera')
        ->assertDontSee(route('venues.show', 'jardin-prive-dakar'));

    $this->get(route('venues.index', ['q' => 'conférence']))
        ->assertOk()
        ->assertSeeText('Salle conférence Riviera')
        ->assertDontSee(route('venues.show', 'jardin-prive-dakar'));
});

test('public venues search excludes booked and temporarily blocked dates', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);
    $blockedDate = now()->addDays(18)->toDateString();
    $availableVenue = Venue::factory()->create([
        'name' => 'Salle libre Plateau',
        'slug' => 'salle-libre-plateau',
        'status' => VenueStatus::Published,
        'city' => 'Abidjan',
        'published_at' => now(),
    ]);
    $bookedVenue = Venue::factory()->create([
        'name' => 'Salle déjà réservée',
        'slug' => 'salle-deja-reservee',
        'status' => VenueStatus::Published,
        'city' => 'Abidjan',
        'published_at' => now(),
    ]);
    $heldVenue = Venue::factory()->create([
        'name' => 'Jardin temporairement bloqué',
        'slug' => 'jardin-temporairement-bloque',
        'status' => VenueStatus::Published,
        'city' => 'Abidjan',
        'published_at' => now(),
    ]);

    collect([$availableVenue, $bookedVenue, $heldVenue])->each(fn (Venue $venue) => $venue->availabilities()->create([
        'available_date' => $blockedDate,
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'available',
    ]));

    Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $bookedVenue->owner_profile_id,
        'venue_id' => $bookedVenue->id,
        'status' => BookingStatus::Confirmed,
        'event_date' => $blockedDate,
        'starts_at' => '09:00:00',
        'ends_at' => '18:00:00',
    ]);

    AvailabilityHold::factory()->create([
        'holdable_type' => Venue::class,
        'holdable_id' => $heldVenue->id,
        'client_id' => $client->id,
        'status' => AvailabilityHoldStatus::Active,
        'starts_at' => now()->addDays(18)->setTime(10, 0),
        'ends_at' => now()->addDays(18)->setTime(16, 0),
        'expires_at' => now()->addMinutes(10),
    ]);

    $this->get(route('venues.index', [
        'start_date' => $blockedDate,
        'end_date' => $blockedDate,
    ]))
        ->assertOk()
        ->assertSeeText('Salle libre Plateau')
        ->assertDontSeeText('Salle déjà réservée')
        ->assertDontSeeText('Jardin temporairement bloqué');

    $this->get(route('venues.blocked-dates', $bookedVenue->slug))
        ->assertOk()
        ->assertJsonFragment(['blocked_dates' => [$blockedDate]])
        ->assertJsonFragment([
            'date' => $blockedDate,
            'starts_at' => '09:00',
            'ends_at' => '18:00',
        ]);
});

test('public venues index returns only results partial for ajax filtering', function () {
    Venue::factory()->create([
        'name' => 'Espace AJAX premium',
        'slug' => 'espace-ajax-premium',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('venues.index', ['q' => 'AJAX']), [
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertSee('data-venues-results')
        ->assertSeeText('Espace AJAX premium')
        ->assertDontSee('Catalogue BAOBAA');
});

test('public venue detail page is available', function () {
    $this->get(route('venues.show', ['slug' => 'auditorium-premium-avec-scene-et-regie']))
        ->assertOk()
        ->assertSee('Auditorium premium avec scene et regie')
        ->assertSeeText('Me connecter pour démarrer');
});

test('client can review a venue only after a confirmed booking', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $venue = Venue::factory()->create([
        'name' => 'Salle test avis',
        'slug' => 'salle-test-avis',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->actingAs($client)
        ->post(route('venues.reviews.store', $venue), [
            'rating' => 5,
            'title' => 'Très bonne expérience',
            'comment' => 'La réservation était claire et le lieu correspondait aux attentes.',
        ])
        ->assertSessionHasErrors('review');

    Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $venue->owner_profile_id,
        'venue_id' => $venue->id,
        'status' => BookingStatus::Confirmed,
    ]);

    $this->actingAs($client)
        ->post(route('venues.reviews.store', $venue), [
            'rating' => 5,
            'title' => 'Très bonne expérience',
            'comment' => 'La réservation était claire et le lieu correspondait aux attentes.',
        ])
        ->assertRedirect()
        ->assertSessionHas('review_status');

    $this->assertDatabaseHas('venue_reviews', [
        'venue_id' => $venue->id,
        'client_id' => $client->id,
        'rating' => 5,
        'status' => 'pending',
    ]);
});

test('venue detail displays approved reviews', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'name' => 'Client Avis',
    ]);

    $venue = Venue::factory()->create([
        'name' => 'Salle avec avis',
        'slug' => 'salle-avec-avis',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $venue->owner_profile_id,
        'venue_id' => $venue->id,
        'status' => BookingStatus::Completed,
    ]);

    VenueReview::factory()->create([
        'venue_id' => $venue->id,
        'client_id' => $client->id,
        'booking_id' => $booking->id,
        'rating' => 5,
        'title' => 'Lieu parfait',
        'comment' => 'Très bonne organisation et espace vraiment adapté.',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('Avis clients')
        ->assertSeeText('Lieu parfait')
        ->assertSeeText('Très bonne organisation');
});

test('similar venues on detail page link to their own detail pages', function () {
    $this->get(route('venues.show', ['slug' => 'auditorium-premium-avec-scene-et-regie']))
        ->assertOk()
        ->assertSee(route('venues.show', 'conference-room-equipee-avec-terrasse'))
        ->assertSee('Conference room equipee avec terrasse');

    $this->get(route('venues.show', ['slug' => 'conference-room-equipee-avec-terrasse']))
        ->assertOk()
        ->assertSee('Conference room equipee avec terrasse');
});

test('public owner profiles page returns partner data without an empty state', function () {
    $this->get(route('owner-profiles.index'))
        ->assertOk()
        ->assertSee('Partenaires événementiels')
        ->assertSee('BAOBAA Signature Events')
        ->assertDontSee('Profils PEE');
});

test('public owner profiles and profile detail pages are available', function () {
    $ownerProfile = OwnerProfile::factory()->create([
        'business_name' => 'BAOBAA Signature Events',
    ]);

    Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salle signature PEE',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('owner-profiles.index'))
        ->assertOk()
        ->assertSee('BAOBAA Signature Events')
        ->assertSee($ownerProfile->public_uuid)
        ->assertDontSee($ownerProfile->slug);

    $this->get(route('owner-profiles.show', $ownerProfile))
        ->assertOk()
        ->assertSee('BAOBAA Signature Events')
        ->assertSee('Salle signature PEE')
        ->assertDontSee('payout_account_reference');
});

test('public service provider profiles and detail pages are available', function () {
    $serviceType = EventServiceType::factory()->create([
        'name' => 'Sonorisation premium',
        'is_active' => true,
    ]);
    $profile = ServiceProviderProfile::factory()->create([
        'business_name' => 'Studio Son Signature',
        'verification_status' => VerificationStatus::Verified,
        'city' => 'Abidjan',
        'district' => 'Cocody',
    ]);

    EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $serviceType->id,
        'name' => 'Pack audio corporate',
        'status' => VenueStatus::Published,
        'starting_price' => 250000,
        'currency' => 'XOF',
        'published_at' => now(),
    ]);

    EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $serviceType->id,
        'name' => 'Service invisible',
        'status' => VenueStatus::Draft,
    ]);
    $otherType = EventServiceType::factory()->create([
        'name' => 'Photo événementielle',
        'is_active' => true,
    ]);
    $otherProfile = ServiceProviderProfile::factory()->create([
        'business_name' => 'Capture Émotion',
        'verification_status' => VerificationStatus::Verified,
        'city' => 'Dakar',
    ]);

    EventService::factory()->create([
        'service_provider_profile_id' => $otherProfile->id,
        'event_service_type_id' => $otherType->id,
        'name' => 'Reportage photo premium',
        'status' => VenueStatus::Published,
        'starting_price' => 180000,
        'published_at' => now(),
    ]);

    $this->get(route('service-provider-profiles.index'))
        ->assertOk()
        ->assertSeeText('Prestataires événementiels')
        ->assertSeeText('Studio Son Signature')
        ->assertSeeText('Capture Émotion')
        ->assertSee($profile->public_uuid)
        ->assertDontSee($profile->slug)
        ->assertDontSeeText('Service invisible');

    $this->get(route('service-provider-profiles.index', ['city' => 'Abidjan']))
        ->assertOk()
        ->assertSeeText('Studio Son Signature')
        ->assertDontSeeText('Capture Émotion');

    $this->get(route('service-provider-profiles.index', ['type_id' => $otherType->id]))
        ->assertOk()
        ->assertSeeText('Capture Émotion')
        ->assertDontSeeText('Studio Son Signature');

    $this->get(route('service-provider-profiles.show', $profile))
        ->assertOk()
        ->assertSeeText('Studio Son Signature')
        ->assertSeeText('Pack audio corporate')
        ->assertSee(route('event-services.index', ['prestataire' => $profile->public_uuid]))
        ->assertDontSeeText('Service invisible')
        ->assertDontSeeText('PSE');

    $this->get(route('service-provider-profiles.show', [$profile, 'q' => 'introuvable']))
        ->assertOk()
        ->assertDontSeeText('Pack audio corporate');
});

test('each baobaa actor has a dedicated login route', function (string $portal, string $label) {
    $this->get(route('portal.login', ['portal' => $portal]))
        ->assertOk()
        ->assertSee($label);
})->with([
    ['sap', 'Portail SAP'],
    ['proprietaire', 'Portail proprietaire'],
    ['client', 'Portail client'],
]);

test('users are redirected to the dashboard matching their portal', function (UserRole $role, string $portal, string $dashboardRoute) {
    $user = User::factory()->create([
        'role' => $role,
        'password' => 'password',
    ]);

    $this->post(route('portal.login.store', ['portal' => $portal]), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route($dashboardRoute));

    $this->assertAuthenticatedAs($user);
})->with([
    [UserRole::Sap, 'sap', 'sap.dashboard'],
    [UserRole::Owner, 'proprietaire', 'owner.dashboard'],
    [UserRole::Client, 'client', 'client.dashboard'],
]);

test('a valid account cannot log in through another role portal', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'password' => 'password',
    ]);

    $this->from(route('portal.login', ['portal' => 'sap']))
        ->post(route('portal.login.store', ['portal' => 'sap']), [
            'email' => $client->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('portal.login', ['portal' => 'sap']))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
