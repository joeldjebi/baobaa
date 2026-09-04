<?php

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\EventProject;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('event composer page displays PSE providers and SAP ticketing', function () {
    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $profile = ServiceProviderProfile::factory()->create([
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

    $this->get(route('event-composer.create'))
        ->assertOk()
        ->assertSeeText('Composer mon événement')
        ->assertSeeText('Studio Son Signature')
        ->assertSeeText('Pack audio corporate')
        ->assertSeeText('Billetterie BAOBAA');
});

test('guest cannot save an event composer project', function () {
    $this->post(route('event-composer.store'), [
        'name' => 'Gala visiteur',
        'country_code' => 'CI',
        'ticketing_requested' => true,
    ])
        ->assertRedirect(route('login'));
});

test('client creates a standalone event project with PSE services and ticketing', function () {
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
            'name' => 'Gala annuel BAOBAA',
            'event_type' => 'Gala',
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
        ->assertRedirect(route('client.projects'))
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
