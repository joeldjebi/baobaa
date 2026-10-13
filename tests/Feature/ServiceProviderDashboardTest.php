<?php

use App\Enums\EventProjectItemStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\PortalAccessRequest;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can request a service provider account and waits for SAP approval', function () {
    $this->get(route('service-provider.register'))
        ->assertOk()
        ->assertSeeText('Créer mon compte professionnel');

    $this->post(route('service-provider.register.store'), [
        'name' => 'Prestataire Son',
        'email' => 'pse.son@baobaa.local',
        'phone' => '+2250101010101',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => '1',
        'business_name' => 'Studio Son Premium',
        'legal_name' => 'Studio Son Premium SARL',
        'tax_identifier' => 'RCCM-CI-PSE',
        'country_code' => 'CI',
        'city' => 'Abidjan',
        'district' => 'Cocody',
        'whatsapp_phone' => '+2250707070707',
        'service_area' => 'Grand Abidjan',
        'description' => 'Sonorisation, lumière et régie technique pour événements.',
    ])
        ->assertRedirect(route('service-provider.dashboard'));

    $user = User::query()->where('email', 'pse.son@baobaa.local')->firstOrFail();

    expect($user->hasPortal(UserRole::Client))->toBeFalse()
        ->and($user->hasPortal(UserRole::ServiceProvider))->toBeTrue()
        ->and($user->serviceProviderProfile)->not->toBeNull()
        ->and($user->serviceProviderProfile->verification_status)->toBe(VerificationStatus::Pending)
        ->and(PortalAccessRequest::query()->where('user_id', $user->id)->where('requested_role', UserRole::ServiceProvider)->where('status', 'pending')->exists())->toBeTrue();
});

test('a professional account cannot sign in as a client and sees its pending validation', function () {
    $this->post(route('service-provider.register.store'), [
        'name' => 'Awa Lumière',
        'email' => 'awa.pro@baobaa.local',
        'phone' => '+2250101010102',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => '1',
        'business_name' => 'Awa Lumière Events',
        'country_code' => 'CI',
        'city' => 'Abidjan',
    ]);

    $this->get(route('service-provider.dashboard'))
        ->assertOk()
        ->assertSeeText('Votre profil est en cours de validation');

    $this->get(route('client.dashboard'))->assertForbidden();

    auth()->logout();

    $this->from(route('portal.login', ['portal' => 'client']))
        ->post(route('portal.login.store', ['portal' => 'client']), ['email' => 'awa.pro@baobaa.local', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Ce compte est un compte professionnel : connectez-vous depuis l’onglet « Professionnel ».']);

    $this->assertGuest();

    $this->post(route('portal.login.store', ['portal' => 'prestataire']), ['email' => 'awa.pro@baobaa.local', 'password' => 'password'])
        ->assertRedirect(route('service-provider.dashboard'));
});

test('a professional without services is invited to create the first one', function () {
    $providerUser = User::factory()->create(['role' => UserRole::ServiceProvider, 'portal_roles' => [UserRole::ServiceProvider->value]]);
    ServiceProviderProfile::factory()->verified()->create(['user_id' => $providerUser->id]);

    $this->actingAs($providerUser)
        ->get(route('service-provider.services'))
        ->assertOk()
        ->assertSeeText('Vous n’avez pas encore de service.')
        ->assertSeeText('Créer mon premier service')
        ->assertSee(route('service-provider.services.create'), false);

    $this->actingAs($providerUser)
        ->get(route('service-provider.services.create'))
        ->assertOk()
        ->assertSee(route('service-provider.services.store'), false);
});

test('SAP validates a service provider request and grants the PSE portal', function () {
    $sap = User::factory()->create([
        'role' => UserRole::Sap,
        'portal_roles' => [UserRole::Sap->value],
    ]);
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);

    $this->actingAs($client)
        ->post(route('portals.service-provider.request'), [
            'business_name' => 'Lumière Signature',
            'legal_name' => 'Lumière Signature SARL',
            'tax_identifier' => 'RCCM-CI-LIGHT',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'district' => 'Plateau',
            'whatsapp_phone' => '+2250505050505',
            'service_area' => 'Abidjan et villes proches',
            'description' => 'Location lumière, scène et écrans LED.',
        ])
        ->assertRedirect(route('client.dashboard'));

    $accessRequest = PortalAccessRequest::query()
        ->where('user_id', $client->id)
        ->where('requested_role', UserRole::ServiceProvider)
        ->firstOrFail();

    $this->actingAs($sap)
        ->post(route('sap.portal-requests.decide', $accessRequest), [
            'decision' => 'approve',
        ])
        ->assertRedirect();

    expect($client->refresh()->hasPortal(UserRole::ServiceProvider))->toBeTrue()
        ->and($client->serviceProviderProfile)->not->toBeNull()
        ->and($client->serviceProviderProfile->verification_status)->toBe(VerificationStatus::Verified);
});

test('service provider dashboard and service CRUD are protected and functional', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $profile = ServiceProviderProfile::factory()->create([
        'user_id' => $pse->id,
        'business_name' => 'Régie Événementielle Pro',
        'verification_status' => VerificationStatus::Verified,
    ]);
    $type = EventServiceType::factory()->create([
        'name' => 'Captation vidéo',
        'is_active' => true,
    ]);

    $this->actingAs($client)
        ->get(route('service-provider.dashboard'))
        ->assertForbidden();

    $this->actingAs($pse)
        ->get(route('service-provider.dashboard'))
        ->assertOk()
        ->assertSeeText('Régie Événementielle Pro')
        ->assertSeeText('Ajouter un service');

    $this->actingAs($pse)
        ->post(route('service-provider.services.store'), [
            'event_service_type_id' => $type->id,
            'name' => 'Captation multicaméra avec régie',
            'short_description' => 'Une équipe vidéo complète pour conférences et concerts.',
            'description' => 'Captation professionnelle avec caméras, ingénieur vidéo, habillage simple et livraison des rushs.',
            'status' => VenueStatus::Published->value,
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'district' => 'Marcory',
            'service_area' => 'Abidjan, Bassam, Yamoussoukro',
            'pricing_unit' => 'event',
            'starting_price' => 350000,
            'deposit_amount' => 100000,
            'attributes_text' => "Caméras: 3\nÉquipe: 2 techniciens",
            'availability_notes_text' => "Réservation 72h avant\nInstallation incluse",
        ])
        ->assertRedirect();

    $service = EventService::query()->where('name', 'Captation multicaméra avec régie')->firstOrFail();

    expect($service->service_provider_profile_id)->toBe($profile->id)
        ->and($service->status)->toBe(VenueStatus::Published)
        ->and($service->attributes)->toBe([
            'Caméras' => '3',
            'Équipe' => '2 techniciens',
        ]);

    $this->actingAs($pse)
        ->get(route('service-provider.services'))
        ->assertOk()
        ->assertSeeText('Captation multicaméra avec régie');

    $this->actingAs($pse)
        ->patch(route('service-provider.services.update', $service), [
            'event_service_type_id' => $type->id,
            'name' => 'Captation multicaméra premium',
            'short_description' => 'Une régie vidéo renforcée.',
            'description' => 'Captation professionnelle avec captation, régie et livraison prioritaire.',
            'status' => VenueStatus::Draft->value,
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'district' => 'Marcory',
            'service_area' => 'Grand Abidjan',
            'pricing_unit' => 'event',
            'starting_price' => 420000,
            'deposit_amount' => 120000,
            'attributes_text' => 'Livraison: 48 heures',
            'availability_notes_text' => 'Contrat obligatoire',
        ])
        ->assertRedirect();

    expect($service->refresh()->name)->toBe('Captation multicaméra premium')
        ->and($service->status)->toBe(VenueStatus::Draft)
        ->and($service->attributes)->toBe(['Livraison' => '48 heures']);

    $this->actingAs($pse)
        ->post(route('service-provider.services.toggle', $service))
        ->assertRedirect();

    expect($service->refresh()->status)->toBe(VenueStatus::Published);
});

test('service provider manages one reservation containing multiple service items', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $otherPse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $profile = ServiceProviderProfile::factory()->create([
        'user_id' => $pse->id,
        'business_name' => 'Studio Son Signature',
        'verification_status' => VerificationStatus::Verified,
    ]);
    $otherProfile = ServiceProviderProfile::factory()->create([
        'user_id' => $otherPse->id,
        'verification_status' => VerificationStatus::Verified,
    ]);
    $project = EventProject::factory()->create([
        'client_id' => $client->id,
        'name' => 'Gala corporate',
        'city' => 'Abidjan',
    ]);
    $reservation = EventProjectItem::factory()->create([
        'event_project_id' => $project->id,
        'item_type' => 'event_service',
        'provider_type' => 'service_provider_profile',
        'provider_id' => $profile->id,
        'title' => 'Pack audio corporate',
        'quoted_amount' => 300000,
        'deposit_amount' => 90000,
        'metadata' => [
            'service_type' => 'Sonorisation',
            'quantity' => 1,
            'unit_amount' => 300000,
        ],
    ]);
    $secondReservationItem = EventProjectItem::factory()->create([
        'event_project_id' => $project->id,
        'item_type' => 'event_service',
        'provider_type' => 'service_provider_profile',
        'provider_id' => $profile->id,
        'title' => 'Pack lumière architectural',
        'quoted_amount' => 200000,
        'deposit_amount' => 40000,
        'metadata' => [
            'service_type' => 'Lumière',
            'quantity' => 1,
            'unit_amount' => 200000,
        ],
    ]);
    $privateProject = EventProject::factory()->create([
        'client_id' => $client->id,
        'name' => 'Projet privé',
    ]);
    $privateReservation = EventProjectItem::factory()->create([
        'event_project_id' => $privateProject->id,
        'item_type' => 'event_service',
        'provider_type' => 'service_provider_profile',
        'provider_id' => $otherProfile->id,
        'title' => 'Pack privé',
    ]);

    $this->actingAs($pse)
        ->get(route('service-provider.reservations'))
        ->assertOk()
        ->assertSeeText('Pack audio corporate')
        ->assertSeeText('Pack lumière architectural')
        ->assertDontSeeText('Pack privé');

    $this->actingAs($pse)
        ->get(route('service-provider.reservations.show', $project))
        ->assertOk()
        ->assertSeeText('Proforma de réservation')
        ->assertSeeText('Pack audio corporate')
        ->assertSeeText('Pack lumière architectural')
        ->assertSeeText('Gala corporate');

    $this->actingAs($pse)
        ->post(route('service-provider.reservations.messages.store', $project), [
            'message' => 'Nous pouvons ajuster la formule avec un technicien supplémentaire.',
            'proposed_amount' => 400000,
        ])
        ->assertRedirect()
        ->assertSessionHas('pse_status');

    expect($reservation->refresh()->quoted_amount)->toBe(240000)
        ->and($reservation->deposit_amount)->toBe(72000)
        ->and($reservation->status)->toBe(EventProjectItemStatus::Negotiating)
        ->and($secondReservationItem->refresh()->quoted_amount)->toBe(160000)
        ->and($secondReservationItem->deposit_amount)->toBe(32000)
        ->and($project->messages)->toHaveCount(1);

    $this->actingAs($pse)
        ->post(route('service-provider.reservations.proforma.confirm', $project))
        ->assertRedirect()
        ->assertSessionHas('pse_status');

    expect($reservation->refresh()->provider_confirmed_at)->not->toBeNull()
        ->and($reservation->status)->toBe(EventProjectItemStatus::AwaitingClientConfirmation)
        ->and($secondReservationItem->refresh()->provider_confirmed_at)->not->toBeNull()
        ->and($secondReservationItem->status)->toBe(EventProjectItemStatus::AwaitingClientConfirmation);

    $this->actingAs($pse)
        ->get(route('service-provider.reservations.show', $privateProject))
        ->assertNotFound();
});
