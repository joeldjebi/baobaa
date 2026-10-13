<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\OwnerProfile;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueMedia;
use App\Models\VenueReview;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publishedVenue(array $attributes = []): Venue
{
    return Venue::factory()->create([
        'status' => VenueStatus::Published,
        'published_at' => now(),
        ...$attributes,
    ]);
}

test('date search keeps venues without calendar entries and excludes dates blocked by the owner', function () {
    $searchedDate = now()->addDays(20)->toDateString();

    publishedVenue(['name' => 'Salle sans calendrier']);
    publishedVenue(['name' => 'Salle ouverte ce jour'])->availabilities()->create([
        'available_date' => $searchedDate,
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'available',
    ]);
    publishedVenue(['name' => 'Salle fermée ce jour'])->availabilities()->create([
        'available_date' => $searchedDate,
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'blocked',
    ]);

    $this->get(route('venues.index', ['start_date' => $searchedDate, 'end_date' => $searchedDate]))
        ->assertOk()
        ->assertSeeText('Salle sans calendrier')
        ->assertSeeText('Salle ouverte ce jour')
        ->assertDontSeeText('Salle fermée ce jour');
});

test('date search with only a start date blocks that single day', function () {
    $searchedDate = now()->addDays(9)->toDateString();

    publishedVenue(['name' => 'Salle disponible'])->availabilities()->create([
        'available_date' => now()->addDays(10)->toDateString(),
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'blocked',
    ]);
    publishedVenue(['name' => 'Salle bloquée'])->availabilities()->create([
        'available_date' => $searchedDate,
        'starts_at' => '08:00:00',
        'ends_at' => '22:00:00',
        'status' => 'blocked',
    ]);

    $this->get(route('venues.index', ['start_date' => $searchedDate]))
        ->assertOk()
        ->assertSeeText('Salle disponible')
        ->assertDontSeeText('Salle bloquée');
});

test('date search ignores invalid and past dates instead of failing', function () {
    publishedVenue(['name' => 'Salle toujours visible']);

    $this->get(route('venues.index', ['start_date' => 'pas-une-date', 'end_date' => '2026-13-45']))
        ->assertOk()
        ->assertSeeText('Salle toujours visible');

    $this->get(route('venues.index', ['start_date' => now()->subYear()->toDateString(), 'end_date' => now()->subYear()->addDay()->toDateString()]))
        ->assertOk()
        ->assertSeeText('Salle toujours visible');
});

test('city search matches partial city and district names', function () {
    publishedVenue(['name' => 'Rooftop Cocody', 'city' => 'Abidjan', 'district' => 'Cocody']);
    publishedVenue(['name' => 'Jardin Almadies', 'city' => 'Dakar', 'district' => 'Almadies']);

    $this->get(route('venues.index', ['city' => 'abid']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSeeText('Rooftop Cocody')
        ->assertDontSeeText('Jardin Almadies');

    $this->get(route('venues.index', ['city' => 'Cocody']))
        ->assertOk()
        ->assertSeeText('Rooftop Cocody');
});

test('venue cover uses the primary image and never a video or a rejected image', function () {
    $venue = publishedVenue();

    VenueMedia::factory()->for($venue)->create([
        'type' => 'video',
        'path' => 'https://cdn.example.com/visite.mp4',
        'sort_order' => 1,
    ]);
    VenueMedia::factory()->for($venue)->create([
        'path' => 'https://cdn.example.com/refusee.jpg',
        'sort_order' => 2,
        'moderation_status' => 'rejected',
    ]);
    VenueMedia::factory()->for($venue)->create([
        'path' => 'https://cdn.example.com/secondaire.jpg',
        'sort_order' => 3,
    ]);
    VenueMedia::factory()->for($venue)->create([
        'path' => 'https://cdn.example.com/principale.jpg',
        'sort_order' => 4,
        'is_primary' => true,
    ]);

    $venue->load('media');

    expect($venue->cover_image_url)->toBe('https://cdn.example.com/principale.jpg')
        ->and($venue->publicImageUrls())->toBe([
            'https://cdn.example.com/principale.jpg',
            'https://cdn.example.com/secondaire.jpg',
        ]);

    $this->get(route('venues.index'))
        ->assertOk()
        ->assertSee('https://cdn.example.com/principale.jpg', false)
        ->assertDontSee('https://cdn.example.com/visite.mp4', false);
});

test('venues without photos show a neutral placeholder instead of stock pictures', function () {
    $venue = publishedVenue(['name' => 'Salle sans photo']);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSee(Venue::placeholderImageUrl(), false)
        ->assertDontSee('images.unsplash.com', false);

    $this->get(route('venues.index'))
        ->assertOk()
        ->assertDontSee('images.unsplash.com', false);
});

test('public rating is computed from approved reviews only and ignores stored fake ratings', function () {
    $venue = publishedVenue([
        'average_rating' => 4.9,
        'reviews_count' => 38,
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertDontSeeText('4.9 ★')
        ->assertDontSeeText('(38)')
        ->assertSeeText('0 avis publié');

    VenueReview::factory()->for($venue)->create(['rating' => 4, 'status' => 'approved', 'approved_at' => now()]);
    VenueReview::factory()->for($venue)->create(['rating' => 2, 'status' => 'pending']);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('4,0 ★ (1)')
        ->assertSeeText('1 avis publié');
});

test('venue page hides empty sections instead of filling them with demonstration content', function () {
    $venue = publishedVenue([
        'highlights' => [],
        'included_items' => [],
        'house_rules' => [],
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertDontSeeText('Système de vidéoconférence Owl')
        ->assertDontSeeText('Répond en moins de 2 heures')
        ->assertDontSeeText('4-15 heures')
        ->assertDontSee('href="#modules"', false)
        ->assertDontSee('href="#inclusions"', false);
});

test('venue page lists the real additional services the client can select', function () {
    $venue = publishedVenue();
    $venue->addOns()->create([
        'name' => 'Vidéoprojecteur laser',
        'description' => 'Écran et projecteur installés.',
        'price' => 45000,
        'currency' => 'XOF',
        'is_available' => true,
        'sort_order' => 1,
    ]);
    $venue->addOns()->create([
        'name' => 'Option retirée',
        'price' => 10000,
        'currency' => 'XOF',
        'is_available' => false,
        'sort_order' => 2,
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('Vidéoprojecteur laser')
        ->assertSeeText('45 000 XOF')
        ->assertDontSeeText('Option retirée');

    $this->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertSeeText('Vidéoprojecteur laser')
        ->assertSeeText('Écran et projecteur installés.')
        ->assertDontSeeText('Option retirée');
});

test('booking request requires accepting the terms and shows the errors on the reservation page', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $venue = publishedVenue(['max_capacity' => 200, 'payment_methods' => ['baobaa_checkout']]);

    $this->actingAs($client)
        ->from(route('venues.reservation', $venue->slug))
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(12)->toDateString(),
            'starts_at' => '10:00',
            'ends_at' => '18:00',
            'guests_count' => 80,
            'payment_method' => 'baobaa_checkout',
        ])
        ->assertRedirect(route('venues.reservation', $venue->slug))
        ->assertSessionHasErrors('accept_terms');

    $this->assertDatabaseCount('bookings', 0);

    $this->actingAs($client)
        ->from(route('venues.reservation', $venue->slug))
        ->followingRedirects()
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(12)->toDateString(),
            'starts_at' => '10:00',
            'ends_at' => '18:00',
            'guests_count' => 80,
            'payment_method' => 'baobaa_checkout',
        ])
        ->assertOk()
        ->assertSeeText('Merci de corriger les points suivants')
        ->assertSee('name="accept_terms"', false);
});

test('public pages expose the mobile menu, the footer legal links and no dead cart link', function (string $url) {
    publishedVenue(['slug' => 'salle-mobile']);

    $this->get($url)
        ->assertOk()
        ->assertSee('data-mobile-menu-open', false)
        ->assertSee(route('pages.terms'), false)
        ->assertSee(route('pages.legal-notice'), false)
        ->assertDontSee('aria-label="Panier"', false);
})->with([
    'accueil' => fn () => url('/'),
    'catalogue' => fn () => route('venues.index'),
    'détail espace' => fn () => route('venues.show', 'salle-mobile'),
    'partenaires' => fn () => route('owner-profiles.index'),
    'prestataires' => fn () => route('service-provider-profiles.index'),
]);

test('homepage shows real counters instead of marketing placeholders', function () {
    publishedVenue(['city' => 'Abidjan']);
    publishedVenue(['city' => 'Dakar']);
    Venue::factory()->create(['status' => VenueStatus::Draft, 'city' => 'Lomé']);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-count-to="2"', false)
        ->assertSeeText('Publiés et vérifiés')
        ->assertSeeText('Où organiser votre événement')
        ->assertDontSeeText('2 000+ espaces vérifiés')
        ->assertDontSeeText('40+ villes servies');
});

test('service provider counters use every verified provider and not only the current page', function () {
    $verifiedProviders = ServiceProviderProfile::factory()
        ->count(13)
        ->create(['verification_status' => VerificationStatus::Verified]);

    $serviceType = EventServiceType::factory()->create();

    $verifiedProviders->each(fn (ServiceProviderProfile $profile) => EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $serviceType->id,
        'status' => VenueStatus::Published,
    ]));

    $pendingProvider = ServiceProviderProfile::factory()->create([
        'business_name' => 'Prestataire non vérifié',
        'verification_status' => VerificationStatus::Pending,
    ]);
    EventService::factory()->create([
        'service_provider_profile_id' => $pendingProvider->id,
        'event_service_type_id' => $serviceType->id,
        'status' => VenueStatus::Published,
    ]);

    $this->get(route('service-provider-profiles.index'))
        ->assertOk()
        ->assertSeeText('13 professionnels vérifiés')
        ->assertSeeText('13 services disponibles')
        ->assertDontSeeText('Prestataire non vérifié')
        ->assertDontSeeText('Studio Son Signature');

    $this->get(route('service-provider-profiles.show', $pendingProvider->public_uuid))
        ->assertNotFound();
});

test('partner search filters profiles by name and city', function () {
    $abidjanOwner = OwnerProfile::factory()->create(['business_name' => 'Maison Lagune']);
    $dakarOwner = OwnerProfile::factory()->create(['business_name' => 'Terrasse Atlantique']);
    publishedVenue(['owner_profile_id' => $abidjanOwner->id, 'city' => 'Abidjan']);
    publishedVenue(['owner_profile_id' => $dakarOwner->id, 'city' => 'Dakar']);

    $this->get(route('owner-profiles.index', ['q' => 'Lagune']))
        ->assertOk()
        ->assertSeeText('Maison Lagune')
        ->assertDontSeeText('Terrasse Atlantique');

    $this->get(route('owner-profiles.index', ['city' => 'Dakar']))
        ->assertOk()
        ->assertSeeText('Terrasse Atlantique')
        ->assertDontSeeText('Maison Lagune');
});

test('purge demo data command only lists changes without the force option', function () {
    $demoOwner = User::factory()->create(['email' => 'demo.owner@baobaa.local', 'role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $demoOwner->id]);
    $demoVenue = publishedVenue(['owner_profile_id' => $ownerProfile->id]);

    $this->artisan('baobaa:purge-demo-data')->assertSuccessful();

    expect($demoVenue->refresh()->status)->toBe(VenueStatus::Published)
        ->and($demoOwner->refresh()->status)->not->toBe(UserStatus::Suspended);
});

test('purge demo data command hides demonstration content and fake reviews', function () {
    $sap = User::factory()->create(['email' => 'sap@baobaa.local', 'role' => UserRole::Sap]);
    $demoOwner = User::factory()->create(['email' => 'demo.owner@baobaa.local', 'role' => UserRole::Owner]);
    $demoClient = User::factory()->create(['email' => 'demo.client@baobaa.local', 'role' => UserRole::Client]);
    $realOwner = User::factory()->create(['email' => 'vrai.partenaire@example.com', 'role' => UserRole::Owner]);

    $demoVenue = publishedVenue([
        'owner_profile_id' => OwnerProfile::factory()->create(['user_id' => $demoOwner->id])->id,
        'average_rating' => 5,
        'reviews_count' => 52,
    ]);
    $realVenue = publishedVenue([
        'owner_profile_id' => OwnerProfile::factory()->create(['user_id' => $realOwner->id])->id,
        'average_rating' => 4.8,
        'reviews_count' => 31,
    ]);
    $fakeReview = VenueReview::factory()->for($realVenue)->create([
        'client_id' => $demoClient->id,
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $this->artisan('baobaa:purge-demo-data', ['--force' => true])->assertSuccessful();

    expect($demoVenue->refresh()->status)->toBe(VenueStatus::Archived)
        ->and($realVenue->refresh()->status)->toBe(VenueStatus::Published)
        ->and((int) $realVenue->reviews_count)->toBe(0)
        ->and((float) $realVenue->average_rating)->toBe(0.0)
        ->and($fakeReview->refresh()->status)->toBe('rejected')
        ->and($demoOwner->refresh()->status)->toBe(UserStatus::Suspended)
        ->and($sap->refresh()->status)->not->toBe(UserStatus::Suspended)
        ->and($realOwner->refresh()->status)->not->toBe(UserStatus::Suspended);
});
