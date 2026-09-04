<?php

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
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
        ->assertSeeText('Prestataire événementiel');

    $this->post(route('service-provider.register.store'), [
        'name' => 'Prestataire Son',
        'email' => 'pse.son@baobaa.local',
        'phone' => '+2250101010101',
        'password' => 'password',
        'password_confirmation' => 'password',
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
        ->assertRedirect(route('client.dashboard'));

    $user = User::query()->where('email', 'pse.son@baobaa.local')->firstOrFail();

    expect($user->hasPortal(UserRole::Client))->toBeTrue()
        ->and($user->hasPortal(UserRole::ServiceProvider))->toBeFalse()
        ->and($user->serviceProviderProfile)->not->toBeNull()
        ->and($user->serviceProviderProfile->verification_status)->toBe(VerificationStatus::Pending)
        ->and(PortalAccessRequest::query()->where('user_id', $user->id)->where('requested_role', UserRole::ServiceProvider)->where('status', 'pending')->exists())->toBeTrue();
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
