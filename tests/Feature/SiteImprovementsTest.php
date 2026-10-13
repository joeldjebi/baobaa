<?php

use App\Enums\ClientType;
use App\Enums\PriceUnit;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\CommissionRule;
use App\Models\OwnerDepositRule;
use App\Models\OwnerProfile;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueAvailability;
use App\Services\MediaEmbedService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function improvementsVenue(array $attributes = []): Venue
{
    return Venue::factory()->create([
        'status' => VenueStatus::Published,
        'published_at' => now(),
        ...$attributes,
    ]);
}

function ownerWithProfile(): array
{
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);

    return [$owner, $ownerProfile];
}

test('catalog filters venues by country', function () {
    improvementsVenue(['name' => 'Salle ivoirienne', 'country_code' => 'CI', 'city' => 'Abidjan']);
    improvementsVenue(['name' => 'Salle sénégalaise', 'country_code' => 'SN', 'city' => 'Dakar']);

    $this->get(route('venues.index', ['country' => 'SN']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSeeText('Salle sénégalaise')
        ->assertDontSeeText('Salle ivoirienne');

    $this->get(route('venues.index'))
        ->assertOk()
        ->assertSee('name="country"', false)
        ->assertSee('name="start_time"', false);
});

test('catalog hours filter keeps venues open during the event and venues without declared hours', function () {
    improvementsVenue(['name' => 'Salle de jour', 'opens_at' => '08:00', 'closes_at' => '18:00']);
    improvementsVenue(['name' => 'Rooftop de nuit', 'opens_at' => '18:00', 'closes_at' => '02:00']);
    improvementsVenue(['name' => 'Salle sans horaires']);

    $this->get(route('venues.index', ['start_time' => '10:00', 'end_time' => '16:00']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSeeText('Salle de jour')
        ->assertSeeText('Salle sans horaires')
        ->assertDontSeeText('Rooftop de nuit');

    $this->get(route('venues.index', ['start_time' => '20:00', 'end_time' => '01:00']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSeeText('Rooftop de nuit')
        ->assertSeeText('Salle sans horaires')
        ->assertDontSeeText('Salle de jour');

    $this->get(route('venues.index', ['start_time' => 'n’importe quoi']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertSeeText('Salle de jour');
});

test('owner can block a period which disappears from date search and the public calendar data', function () {
    [$owner, $ownerProfile] = ownerWithProfile();
    $venue = improvementsVenue(['owner_profile_id' => $ownerProfile->id, 'name' => 'Salle à bloquer']);
    $firstDay = now()->addDays(30)->toDateString();
    $lastDay = now()->addDays(32)->toDateString();

    $this->actingAs($owner)
        ->post(route('owner.calendar.store'), [
            'venue_id' => $venue->id,
            'status' => 'blocked',
            'start_date' => $firstDay,
            'end_date' => $lastDay,
            'block_reason' => 'Travaux',
        ])
        ->assertRedirect()
        ->assertSessionHas('calendar_status', '3 jours bloqués dans votre calendrier.');

    expect(VenueAvailability::query()->where('venue_id', $venue->id)->where('status', 'blocked')->count())->toBe(3);

    $this->get(route('venues.index', ['start_date' => now()->addDays(31)->toDateString()]), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertDontSeeText('Salle à bloquer');

    $this->getJson(route('venues.blocked-dates', $venue->slug))
        ->assertOk()
        ->assertJsonFragment(['blocked_dates' => [$firstDay, now()->addDays(31)->toDateString(), $lastDay]]);

    $availability = VenueAvailability::query()->where('venue_id', $venue->id)->firstOrFail();

    $this->actingAs($owner)
        ->delete(route('owner.calendar.destroy', $availability))
        ->assertRedirect();

    $this->assertModelMissing($availability);
});

test('owner cannot manage the calendar of another owner venue', function () {
    [$owner] = ownerWithProfile();
    [, $otherProfile] = ownerWithProfile();
    $otherVenue = improvementsVenue(['owner_profile_id' => $otherProfile->id]);
    $otherAvailability = $otherVenue->availabilities()->create([
        'available_date' => now()->addDays(5)->toDateString(),
        'starts_at' => '00:00',
        'ends_at' => '23:59',
        'status' => 'blocked',
    ]);

    $this->actingAs($owner)
        ->post(route('owner.calendar.store'), [
            'venue_id' => $otherVenue->id,
            'status' => 'blocked',
            'start_date' => now()->addDays(5)->toDateString(),
        ])
        ->assertSessionHasErrors('venue_id');

    $this->actingAs($owner)
        ->delete(route('owner.calendar.destroy', $otherAvailability))
        ->assertNotFound();
});

test('venue page shows the availability calendar, an approximate map and clear payment terms', function () {
    [, $ownerProfile] = ownerWithProfile();
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 30,
        'minimum_amount' => 25000,
        'maximum_amount' => null,
    ]);
    $venue = improvementsVenue([
        'owner_profile_id' => $ownerProfile->id,
        'starting_price' => 200000,
        'price_unit' => PriceUnit::Day,
        'minimum_duration_hours' => 6,
        'opens_at' => '09:00',
        'closes_at' => '23:00',
        'latitude' => 5.3612345,
        'longitude' => -3.9518765,
        'payment_methods' => ['wave', 'orange_money'],
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSee('data-venue-calendar', false)
        ->assertSee(route('venues.blocked-dates', $venue->slug), false)
        ->assertSee('openstreetmap.org/export/embed.html', false)
        ->assertDontSee('5.3612345', false)
        ->assertSeeText('200 000 XOF par journée')
        ->assertSeeText('Location minimum : 6 h')
        ->assertSeeText('Ouvert de 09 h 00 – 23 h 00')
        ->assertSeeText('Acompte de 30 % du montant total, minimum 25 000 XOF.')
        ->assertSeeText('Wave')
        ->assertSeeText('Orange Money');
});

test('venue without coordinates keeps the textual location instead of a map', function () {
    $venue = improvementsVenue(['latitude' => null, 'longitude' => null]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertDontSee('openstreetmap.org', false)
        ->assertSeeText('L’adresse exacte vous est communiquée une fois la réservation confirmée.');
});

test('venue page embeds online videos and the 360 virtual tour', function () {
    $venue = improvementsVenue([
        'video_urls' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://vimeo.com/76979871'],
        'virtual_tour_url' => 'https://my.matterport.com/show/?m=SxQL3iGyoDo',
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
        ->assertSee('https://player.vimeo.com/video/76979871', false)
        ->assertSee('https://my.matterport.com/show/?m=SxQL3iGyoDo', false)
        ->assertSeeText('Visite virtuelle 360°');
});

test('media embed service only accepts known video and virtual tour providers over https', function () {
    $service = app(MediaEmbedService::class);

    expect($service->videoEmbedUrl('https://youtu.be/dQw4w9WgXcQ'))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($service->videoEmbedUrl('https://www.youtube.com/shorts/dQw4w9WgXcQ'))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($service->videoEmbedUrl('http://www.youtube.com/watch?v=dQw4w9WgXcQ'))->toBeNull()
        ->and($service->videoEmbedUrl('https://evil.example.com/watch?v=dQw4w9WgXcQ'))->toBeNull()
        ->and($service->videoEmbedUrl('javascript:alert(1)'))->toBeNull()
        ->and($service->virtualTourEmbedUrl('https://kuula.co/share/collection/7lVLq'))->toBe('https://kuula.co/share/collection/7lVLq')
        ->and($service->virtualTourEmbedUrl('https://www.google.com/maps/embed?pb=abc'))->toBe('https://www.google.com/maps/embed?pb=abc')
        ->and($service->virtualTourEmbedUrl('https://www.google.com/search?q=test'))->toBeNull()
        ->and($service->virtualTourEmbedUrl('https://attacker.example.com/tour'))->toBeNull();
});

test('owner saves pricing details, media links, opening hours and coordinates on a venue draft', function () {
    [$owner, $ownerProfile] = ownerWithProfile();
    $venue = Venue::factory()->create(['owner_profile_id' => $ownerProfile->id, 'status' => VenueStatus::Draft]);

    $this->actingAs($owner)
        ->post(route('owner.venues.draft.store'), [
            'step' => 'base',
            'venue_id' => $venue->id,
            'name' => 'Salle Lagune',
            'venue_category_id' => $venue->venue_category_id,
            'country_code' => 'SN',
            'city' => 'Dakar',
            'min_capacity' => 10,
            'max_capacity' => 200,
            'starting_price' => 150000,
            'price_unit' => 'half_day',
            'minimum_duration_hours' => 4,
            'booking_mode' => 'request',
            'payment_methods' => ['wave'],
        ])
        ->assertRedirect();

    $this->actingAs($owner)
        ->post(route('owner.venues.draft.store'), [
            'step' => 'details',
            'venue_id' => $venue->id,
            'short_description' => 'Une salle lumineuse.',
            'description' => 'Une salle lumineuse au bord de la lagune.',
            'video_urls' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', ''],
            'virtual_tour_url' => 'https://kuula.co/share/collection/7lVLq',
        ])
        ->assertRedirect();

    $this->actingAs($owner)
        ->post(route('owner.venues.draft.store'), [
            'step' => 'localisation',
            'venue_id' => $venue->id,
            'opens_at' => '18:00',
            'closes_at' => '02:00',
            'latitude' => '14.6928',
            'longitude' => '-17.4467',
        ])
        ->assertRedirect();

    $venue->refresh();

    expect($venue->country_code)->toBe('SN')
        ->and($venue->price_unit)->toBe(PriceUnit::HalfDay)
        ->and($venue->minimum_duration_hours)->toBe(4)
        ->and($venue->video_urls)->toBe(['https://www.youtube.com/watch?v=dQw4w9WgXcQ'])
        ->and($venue->virtual_tour_url)->toBe('https://kuula.co/share/collection/7lVLq')
        ->and(substr($venue->opens_at, 0, 5))->toBe('18:00')
        ->and(substr($venue->closes_at, 0, 5))->toBe('02:00')
        ->and((float) $venue->latitude)->toBe(14.6928);
});

test('owner cannot save an unsupported video or virtual tour link', function () {
    [$owner, $ownerProfile] = ownerWithProfile();
    $venue = Venue::factory()->create(['owner_profile_id' => $ownerProfile->id, 'status' => VenueStatus::Draft]);

    $this->actingAs($owner)
        ->from(route('owner.venues.edit', ['venue' => $venue, 'step' => 'details']))
        ->post(route('owner.venues.draft.store'), [
            'step' => 'details',
            'venue_id' => $venue->id,
            'short_description' => 'Une salle lumineuse.',
            'description' => 'Une salle lumineuse au bord de la lagune.',
            'video_urls' => ['https://evil.example.com/video'],
            'virtual_tour_url' => 'https://attacker.example.com/tour',
        ])
        ->assertSessionHasErrors(['video_urls.0', 'virtual_tour_url']);

    expect($venue->refresh()->virtual_tour_url)->toBeNull();
});

test('client registration records the client type and requires an organization name for companies', function () {
    $this->get(route('client.register'))
        ->assertOk()
        ->assertSee('name="client_type"', false)
        ->assertSeeText('Agence événementielle');

    $this->post(route('client.register.store'), [
        'name' => 'Awa Koné',
        'email' => 'awa.entreprise@example.com',
        'client_type' => 'company',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => '1',
    ])->assertSessionHasErrors('organization_name');

    $this->post(route('client.register.store'), [
        'name' => 'Awa Koné',
        'email' => 'awa.entreprise@example.com',
        'client_type' => 'company',
        'organization_name' => 'Koné Events SARL',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => '1',
    ])->assertRedirect(route('client.dashboard'));

    $client = User::query()->where('email', 'awa.entreprise@example.com')->firstOrFail();

    expect($client->client_type)->toBe(ClientType::Company)
        ->and($client->organization_name)->toBe('Koné Events SARL');
});

test('partner signup page explains commission and subscription plans in simple words', function () {
    CommissionRule::factory()->create(['scope' => 'global', 'percentage_rate' => 10]);
    SubscriptionPlan::factory()->create(['name' => 'Formule Pro', 'price' => 25000, 'active_venues_limit' => 5, 'reduced_commission_rate' => 7]);

    $this->get(route('owner.register'))
        ->assertOk()
        ->assertSeeText('10 % du montant de chaque réservation confirmée')
        ->assertSeeText('Formule Pro')
        ->assertSeeText('25 000 XOF / mois')
        ->assertSeeText('Commission réduite à 7 %')
        ->assertDontSee('images.unsplash.com', false);
});

test('owner settings show the commission and deposit conditions', function () {
    [$owner, $ownerProfile] = ownerWithProfile();
    CommissionRule::factory()->create(['scope' => 'global', 'percentage_rate' => 12.5]);
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'deposit_type' => 'fixed',
        'fixed_amount' => 50000,
        'minimum_amount' => 0,
        'maximum_amount' => null,
    ]);
    improvementsVenue(['owner_profile_id' => $ownerProfile->id]);

    $this->actingAs($owner)
        ->get(route('owner.settings'))
        ->assertOk()
        ->assertSeeText('Vos conditions BAOBAA')
        ->assertSeeText('12,5 % du montant de chaque réservation confirmée')
        ->assertSeeText('Acompte fixe de 50 000 XOF');
});

test('sitemap lists public pages and published venues only', function () {
    $publishedVenue = improvementsVenue(['slug' => 'salle-publiee']);
    Venue::factory()->create(['slug' => 'salle-brouillon', 'status' => VenueStatus::Draft]);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('venues.show', $publishedVenue->slug), false)
        ->assertSee(route('pages.terms'), false)
        ->assertDontSee('salle-brouillon', false);

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Sitemap: '.route('sitemap'), false)
        ->assertSee('Disallow: /sap/', false);
});

test('public pages are indexable with canonical and structured data while private pages are not', function () {
    $venue = improvementsVenue(['name' => 'Salle référencée', 'max_capacity' => 300]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSee('<html lang="fr">', false)
        ->assertSee('<link rel="canonical" href="'.route('venues.show', $venue->slug).'">', false)
        ->assertSee('"@type":"EventVenue"', false)
        ->assertSee('"maximumAttendeeCapacity":300', false)
        ->assertDontSee('aggregateRating', false)
        ->assertDontSee('noindex', false);

    $this->get(route('client.register'))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    $this->get(route('owner.register'))
        ->assertOk()
        ->assertDontSee('noindex', false);
});
