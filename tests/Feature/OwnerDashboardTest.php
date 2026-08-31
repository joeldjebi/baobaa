<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\PortalAccessRequest;
use App\Models\SponsorshipCampaign;
use App\Models\SponsorshipPlan;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner dashboard pages are available from dedicated routes', function (string $routeName, string $expectedText) {
    $owner = User::factory()->create([
        'role' => UserRole::Owner,
    ]);

    OwnerProfile::factory()->create([
        'user_id' => $owner->id,
        'business_name' => 'PEE Test Premium',
    ]);

    $this->actingAs($owner)
        ->get(route($routeName))
        ->assertOk()
        ->assertSeeText($expectedText)
        ->assertSeeText('PEE Test Premium');
})->with([
    ['owner.dashboard', 'Tableau de bord'],
    ['owner.venues', 'Mes espaces'],
    ['owner.venues.create', 'Ajouter un espace'],
    ['owner.bookings', 'Réservations'],
    ['owner.payments', 'Paiements'],
    ['owner.sponsorships', 'Sponsoriser mes espaces'],
    ['owner.calendar', 'Disponibilités'],
    ['owner.addons', 'Modules complémentaires'],
    ['owner.reviews', 'Avis clients'],
    ['owner.settings', 'Paramètres partenaire'],
]);

test('owner dashboard shows real owner metrics and tables', function () {
    $owner = User::factory()->create([
        'role' => UserRole::Owner,
    ]);
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);
    $ownerProfile = OwnerProfile::factory()->create([
        'user_id' => $owner->id,
        'business_name' => 'PEE Data Studio',
    ]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salon dashboard premium',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $ownerProfile->id,
        'venue_id' => $venue->id,
        'status' => BookingStatus::Confirmed,
        'reservation_amount' => 120000,
    ]);
    Payment::factory()->create([
        'booking_id' => $booking->id,
        'payer_id' => $client->id,
        'status' => PaymentStatus::Succeeded,
        'amount' => 120000,
        'paid_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSeeText('PEE Data Studio')
        ->assertSeeText('Salon dashboard premium')
        ->assertSeeText('120 000 XOF');
});

test('owner sees booking detail page with actions', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $client = User::factory()->create(['role' => UserRole::Client, 'name' => 'Client Premium']);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salon détail réservation',
    ]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $ownerProfile->id,
        'venue_id' => $venue->id,
        'reference' => 'BAO-DETAIL-TEST',
        'status' => BookingStatus::PendingOwner,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking))
        ->assertOk()
        ->assertSeeText('BAO-DETAIL-TEST')
        ->assertSeeText('Client Premium')
        ->assertSeeText('Confirmer la réservation');
});

test('owner filters calendar by venue and status', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $visibleVenue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Espace filtré',
    ]);
    $hiddenVenue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Espace masqué',
    ]);
    VenueAvailability::factory()->create([
        'venue_id' => $visibleVenue->id,
        'status' => 'available',
        'available_date' => now()->addDays(5)->toDateString(),
    ]);
    VenueAvailability::factory()->create([
        'venue_id' => $hiddenVenue->id,
        'status' => 'blocked',
        'available_date' => now()->addDays(9)->toDateString(),
    ]);

    $this->actingAs($owner)
        ->get(route('owner.calendar', ['venue_id' => $visibleVenue->id, 'status' => 'available']))
        ->assertOk()
        ->assertSeeText('Espace filtré')
        ->assertDontSee(now()->addDays(9)->format('d/m/Y'));
});

test('client dashboard shows reservations and payments', function () {
    $client = User::factory()->create(['role' => UserRole::Client, 'name' => 'Client Dashboard']);
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salle client premium',
    ]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $ownerProfile->id,
        'venue_id' => $venue->id,
        'reference' => 'BAO-CLIENT-TEST',
        'status' => BookingStatus::Confirmed,
        'reservation_amount' => 95000,
    ]);
    Payment::factory()->create([
        'booking_id' => $booking->id,
        'payer_id' => $client->id,
        'status' => PaymentStatus::Succeeded,
        'amount' => 95000,
        'paid_at' => now(),
    ]);

    $this->actingAs($client)
        ->get(route('client.dashboard'))
        ->assertOk()
        ->assertSeeText('Client Dashboard')
        ->assertSeeText('BAO-CLIENT-TEST')
        ->assertSeeText('Salle client premium')
        ->assertSeeText('95 000 XOF');

    $this->actingAs($client)
        ->get(route('client.reservations'))
        ->assertOk()
        ->assertSeeText('Historique des réservations')
        ->assertSeeText('BAO-CLIENT-TEST')
        ->assertSeeText('Salle client premium');

    $this->actingAs($client)
        ->get(route('client.payments'))
        ->assertOk()
        ->assertSeeText('Historique des paiements')
        ->assertSeeText('95 000 XOF');
});

test('client updates profile and password', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'password' => 'password',
    ]);

    $this->actingAs($client)
        ->patch(route('client.profile.update'), [
            'name' => 'Client Profil Premium',
            'phone' => '+2250102030405',
        ])
        ->assertRedirect();

    expect($client->refresh()->name)->toBe('Client Profil Premium')
        ->and($client->phone)->toBe('+2250102030405');

    $this->actingAs($client)
        ->put(route('client.password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-secure',
            'password_confirmation' => 'new-password-secure',
        ])
        ->assertRedirect();
});

test('portal switching is controlled by SAP approval', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $sap = User::factory()->create([
        'role' => UserRole::Sap,
        'portal_roles' => [UserRole::Sap->value],
    ]);

    $this->actingAs($client)
        ->post(route('portals.owner.request'), [
            'applicant_type' => 'company',
            'business_name' => 'Nouveau PEE Validable',
            'legal_name' => 'Nouveau PEE SARL',
            'tax_identifier' => 'RCCM-TEST',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'whatsapp_phone' => '+2250102030405',
            'motivation' => 'Nous avons plusieurs espaces événementiels à publier.',
        ])
        ->assertRedirect(route('client.dashboard'));

    expect($client->refresh()->hasPortal(UserRole::Owner))->toBeFalse();

    $accessRequest = PortalAccessRequest::query()->where('user_id', $client->id)->firstOrFail();

    $this->actingAs($sap)
        ->post(route('sap.portal-requests.decide', $accessRequest), [
            'decision' => 'approve',
        ])
        ->assertRedirect();

    expect($client->refresh()->hasPortal(UserRole::Owner))->toBeTrue()
        ->and($client->ownerProfile)->not->toBeNull()
        ->and($client->ownerProfile->verification_status)->toBe(VerificationStatus::Verified);

    $owner = User::factory()->create([
        'role' => UserRole::Owner,
        'portal_roles' => [UserRole::Owner->value],
    ]);

    $this->actingAs($owner)
        ->post(route('portals.client.enable'))
        ->assertRedirect();

    expect($owner->refresh()->hasPortal(UserRole::Client))->toBeFalse();

    $clientAccessRequest = PortalAccessRequest::query()
        ->where('user_id', $owner->id)
        ->where('requested_role', UserRole::Client)
        ->firstOrFail();

    $this->actingAs($sap)
        ->post(route('sap.portal-requests.decide', $clientAccessRequest), [
            'decision' => 'approve',
        ])
        ->assertRedirect();

    expect($owner->refresh()->hasPortal(UserRole::Client))->toBeTrue();
});

test('owner can create sponsorship campaign for a published venue', function () {
    $owner = User::factory()->create([
        'role' => UserRole::Owner,
        'portal_roles' => [UserRole::Owner->value],
    ]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salle sponsorisée',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);
    $plan = SponsorshipPlan::factory()->create([
        'name' => 'Découverte test',
        'price' => 500,
        'duration_days' => 1,
        'placement' => 'catalog_top',
        'is_active' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.sponsorships', ['venue_id' => $venue->id]))
        ->assertOk()
        ->assertSeeText('Salle sponsorisée')
        ->assertSeeText('Découverte test');

    $this->actingAs($owner)
        ->post(route('owner.sponsorships.store'), [
            'venue_id' => $venue->id,
            'sponsorship_plan_id' => $plan->id,
            'name' => 'Boost corporate',
            'goal' => 'booking',
            'starts_on' => now()->addDay()->toDateString(),
            'target_cities' => 'Abidjan, Dakar',
        ])
        ->assertRedirect();

    $campaign = SponsorshipCampaign::query()->firstOrFail();

    expect($campaign->owner_profile_id)->toBe($ownerProfile->id)
        ->and($campaign->venue_id)->toBe($venue->id)
        ->and($campaign->sponsorship_plan_id)->toBe($plan->id)
        ->and($campaign->budget_amount)->toBe(500)
        ->and($campaign->status)->toBe('pending');
});

test('sap manages dashboard pages and pricing plans', function () {
    $sap = User::factory()->create([
        'role' => UserRole::Sap,
        'portal_roles' => [UserRole::Sap->value],
    ]);
    $client = User::factory()->create(['role' => UserRole::Client, 'name' => 'Client SAP']);
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id, 'business_name' => 'PEE SAP']);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Espace SAP',
        'status' => VenueStatus::PendingReview,
    ]);

    foreach (['sap.dashboard', 'sap.owners', 'sap.clients', 'sap.venues', 'sap.bookings', 'sap.payments', 'sap.subscription-plans', 'sap.commissions', 'sap.sponsorship-plans', 'sap.portal-requests'] as $routeName) {
        $this->actingAs($sap)->get(route($routeName))->assertOk();
    }

    $this->actingAs($sap)
        ->post(route('sap.sponsorship-plans.store'), [
            'name' => 'Découverte 1 jour',
            'placement' => 'catalog_top',
            'price' => 500,
            'duration_days' => 1,
            'description' => 'Un jour de visibilité.',
            'features' => "Badge sponsorisé\nPriorité catalogue",
        ])
        ->assertRedirect();

    expect(SponsorshipPlan::query()->where('price', 500)->where('duration_days', 1)->exists())->toBeTrue();

    $this->actingAs($sap)
        ->post(route('sap.commissions.store'), [
            'name' => 'Commission premium SAP',
            'scope' => 'global',
            'commission_type' => 'percentage',
            'percentage_rate' => 10,
        ])
        ->assertRedirect();

    $this->actingAs($sap)
        ->post(route('sap.venues.status', $venue), ['status' => 'published'])
        ->assertRedirect();

    $this->actingAs($sap)
        ->post(route('sap.users.status', $client), ['status' => 'suspended'])
        ->assertRedirect();

    expect($venue->refresh()->status)->toBe(VenueStatus::Published)
        ->and($client->refresh()->status)->toBe(UserStatus::Suspended);
});
