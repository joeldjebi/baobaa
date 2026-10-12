<?php

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\AvailabilityHold;
use App\Models\EventProject;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\ManagedLocation;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('event composer page displays PSE providers and SAP ticketing', function () {
    createActiveComposerLocations();

    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $profile = ServiceProviderProfile::factory()->verified()->create([
        'user_id' => $pse->id,
        'business_name' => 'Studio Son Signature',
    ]);
    $type = EventServiceType::factory()->create([
        'name' => 'Sonorisation',
    ]);
    EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $type->id,
        'name' => 'Pack audio corporate',
        'status' => VenueStatus::Published,
    ]);

    $this->get(route('event-services.index'))
        ->assertOk()
        ->assertSeeText('Réservez les prestataires qui donnent vie à votre événement.')
        ->assertSeeText('Conférence / Séminaire')
        ->assertSeeText('Côte d’Ivoire')
        ->assertSeeText('Abidjan')
        ->assertSeeText('Cocody')
        ->assertSeeText('Studio Son Signature')
        ->assertSeeText('Pack audio corporate')
        ->assertSeeText('Billetterie digitale BAOBAA')
        ->assertDontSeeText('Réserver un espace obligatoire');
});

test('legacy event composer route still opens the dedicated PSE service booking page', function () {
    createActiveComposerLocations();

    $this->get(route('event-composer.create'))
        ->assertOk()
        ->assertSeeText('Réservez les prestataires qui donnent vie à votre événement.')
        ->assertSeeText('Aucun espace obligatoire');
});

test('guest cannot save an event composer project', function () {
    createActiveComposerLocations();

    $this->post(route('event-composer.store'), [
        'accept_terms' => '1',
        'name' => 'Gala visiteur',
        'country_code' => 'CI',
        'ticketing_requested' => true,
    ])
        ->assertRedirect(route('login'));
});

test('client creates a standalone event project with PSE services and ticketing', function () {
    createActiveComposerLocations();

    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $profile = ServiceProviderProfile::factory()->verified()->create([
        'user_id' => $pse->id,
        'business_name' => 'Studio Lumière Signature',
    ]);
    $type = EventServiceType::factory()->create([
        'name' => 'Lumière',
    ]);
    $service = EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $type->id,
        'name' => 'Pack lumière gala',
        'status' => VenueStatus::Published,
        'starting_price' => 275000,
        'deposit_amount' => 50000,
    ]);

    $this->actingAs($client)
        ->post(route('event-composer.store'), [
            'accept_terms' => '1',
            'name' => 'Gala annuel BAOBAA',
            'event_type' => 'gala',
            'event_date' => now()->addMonth()->toDateString(),
            'starts_at' => '18:00',
            'ends_at' => '23:00',
            'guests_count' => 320,
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'district' => 'Cocody',
            'client_notes' => 'Nous voulons une ambiance premium et une billetterie simple.',
            'event_service_ids' => [$service->id],
            'ticketing_requested' => true,
        ])
        ->assertRedirectContains('/client/evenements/')
        ->assertSessionHas('project_status');

    $project = EventProject::query()->with('items')->firstOrFail();

    expect($project->client_id)->toBe($client->id)
        ->and($project->name)->toBe('Gala annuel BAOBAA')
        ->and($project->estimated_total_amount)->toBe(275000)
        ->and($project->metadata['created_from'])->toBe('event_composer')
        ->and($project->metadata['guests_count'])->toBe(320)
        ->and($project->items)->toHaveCount(2)
        ->and($project->items->pluck('item_type')->sort()->values()->all())->toBe(['event_service', 'ticketing']);

    expect($project->items()->where('item_type', 'event_service')->first())
        ->title->toBe('Pack lumière gala')
        ->provider_type->toBe('service_provider_profile')
        ->provider_id->toBe($profile->id)
        ->quoted_amount->toBe(275000);

    expect($project->items()->where('item_type', 'ticketing')->first())
        ->title->toBe('Billetterie BAOBAA')
        ->provider_type->toBe('sap')
        ->quoted_amount->toBe(0);
});

test('client can reserve only PSE services without a venue booking item', function () {
    createActiveComposerLocations();

    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $profile = ServiceProviderProfile::factory()->verified()->create([
        'user_id' => $pse->id,
        'business_name' => 'Maison Podium Pro',
    ]);
    $type = EventServiceType::factory()->create([
        'name' => 'Podium',
    ]);
    $service = EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $type->id,
        'name' => 'Podium modulable avec lumière',
        'status' => VenueStatus::Published,
        'starting_price' => 180000,
    ]);

    $this->actingAs($client)
        ->post(route('event-composer.store'), [
            'accept_terms' => '1',
            'name' => 'Installation scène sans espace',
            'event_type' => 'concert',
            'event_date' => now()->addWeeks(3)->toDateString(),
            'guests_count' => 150,
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'event_service_ids' => [$service->id],
        ])
        ->assertRedirectContains('/client/evenements/');

    $project = EventProject::query()->with('items')->firstOrFail();

    expect($project->items)->toHaveCount(1)
        ->and($project->items->first()->item_type)->toBe('event_service')
        ->and($project->items()->where('item_type', 'venue_booking')->exists())->toBeFalse()
        ->and($project->estimated_total_amount)->toBe(180000)
        ->and($project->metadata['created_from'])->toBe('event_composer');
});

test('service booking blocks overlapping reservations for ten minutes', function () {
    createActiveComposerLocations();

    $firstClient = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $secondClient = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $profile = ServiceProviderProfile::factory()->verified()->create();
    $type = EventServiceType::factory()->create([
        'name' => 'Sonorisation',
    ]);
    $service = EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'event_service_type_id' => $type->id,
        'name' => 'Pack audio premium',
        'status' => VenueStatus::Published,
        'starting_price' => 250000,
    ]);
    $eventDate = now()->addWeeks(3)->toDateString();

    $this->actingAs($firstClient)
        ->post(route('event-composer.store'), [
            'accept_terms' => '1',
            'name' => 'Conférence matin',
            'event_type' => 'conference',
            'event_date' => $eventDate,
            'starts_at' => '09:00',
            'ends_at' => '14:00',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'event_service_ids' => [$service->id],
        ])
        ->assertRedirectContains('/client/evenements/');

    $this->actingAs($secondClient)
        ->from(route('event-services.index'))
        ->post(route('event-composer.store'), [
            'accept_terms' => '1',
            'name' => 'Séminaire midi',
            'event_type' => 'conference',
            'event_date' => $eventDate,
            'starts_at' => '12:00',
            'ends_at' => '18:00',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'event_service_ids' => [$service->id],
        ])
        ->assertRedirect(route('event-services.index'))
        ->assertSessionHasErrors('event_service_ids');

    expect(EventProject::query()->count())->toBe(1)
        ->and(AvailabilityHold::query()->where('holdable_type', EventService::class)->where('holdable_id', $service->id)->count())->toBe(1)
        ->and(AvailabilityHold::query()->first()?->expires_at->diffInMinutes(now()))->toBeLessThanOrEqual(10);
});

test('event composer only exposes and accepts SAP active locations', function () {
    createActiveComposerLocations();
    ManagedLocation::factory()->country()->inactive()->create([
        'name' => 'Pays masqué',
        'slug' => 'pays-masque',
        'code' => 'PM',
        'country_code' => 'PM',
    ]);
    ManagedLocation::factory()->city()->inactive()->create([
        'name' => 'Ville masquée',
        'slug' => 'ville-masquee',
        'country_code' => 'CI',
    ]);

    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);

    $this->get(route('event-services.index'))
        ->assertOk()
        ->assertSeeText('Côte d’Ivoire')
        ->assertSeeText('Abidjan')
        ->assertDontSeeText('Pays masqué')
        ->assertDontSeeText('Ville masquée');

    $this->actingAs($client)
        ->from(route('event-services.index'))
        ->post(route('event-composer.store'), [
            'accept_terms' => '1',
            'name' => 'Service dans une ville masquée',
            'country_code' => 'CI',
            'city' => 'Ville masquée',
            'ticketing_requested' => true,
        ])
        ->assertRedirect(route('event-services.index'))
        ->assertSessionHasErrors('city');
});

function createActiveComposerLocations(): void
{
    $country = ManagedLocation::factory()->country()->create([
        'name' => 'Côte d’Ivoire',
        'slug' => 'cote-divoire',
        'code' => 'CI',
        'country_code' => 'CI',
        'sort_order' => 1,
    ]);
    $city = ManagedLocation::factory()->city()->create([
        'parent_id' => $country->id,
        'name' => 'Abidjan',
        'slug' => 'abidjan',
        'country_code' => 'CI',
        'sort_order' => 2,
    ]);
    ManagedLocation::factory()->district()->create([
        'parent_id' => $city->id,
        'name' => 'Cocody',
        'slug' => 'cocody',
        'country_code' => 'CI',
        'sort_order' => 3,
    ]);
}
