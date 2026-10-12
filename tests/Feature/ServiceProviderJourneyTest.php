<?php

use App\Enums\EventProjectItemStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Models\CommissionRule;
use App\Models\EventProject;
use App\Models\EventService;
use App\Models\EventServiceMedia;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use App\Models\Venue;
use App\Services\EventProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * @return array{client: User, providerUser: User, provider: ServiceProviderProfile, service: EventService, project: EventProject}
 */
function servicesOnlyProject(int $price = 300000, int $deposit = 90000): array
{
    $client = User::factory()->create(['role' => UserRole::Client, 'portal_roles' => [UserRole::Client->value]]);
    $providerUser = User::factory()->create(['role' => UserRole::ServiceProvider, 'portal_roles' => [UserRole::ServiceProvider->value]]);
    $provider = ServiceProviderProfile::factory()->verified()->create(['user_id' => $providerUser->id, 'business_name' => 'Studio Son Lagune']);
    $service = EventService::factory()->create([
        'service_provider_profile_id' => $provider->id,
        'name' => 'Sonorisation gala',
        'status' => VenueStatus::Published,
        'starting_price' => $price,
        'deposit_amount' => $deposit,
    ]);

    $project = app(EventProjectService::class)->createStandaloneProject($client, [
        'name' => 'Gala sans salle',
        'event_date' => now()->addMonth()->toDateString(),
        'city' => 'Abidjan',
    ], [$service->id], false);

    return compact('client', 'providerUser', 'provider', 'service', 'project');
}

test('client sees a services only project with its provider lane', function () {
    ['client' => $client, 'project' => $project] = servicesOnlyProject();

    $this->actingAs($client)
        ->get(route('client.projects'))
        ->assertOk()
        ->assertSee(route('client.projects.show', $project), false);

    $this->actingAs($client)
        ->get(route('client.projects.show', $project))
        ->assertOk()
        ->assertSeeText('Studio Son Lagune')
        ->assertSeeText('Sonorisation gala')
        ->assertSeeText('Accepter le devis de 300 000 XOF')
        ->assertSeeText('Messages avec Studio Son Lagune');
});

test('another client cannot open or act on a project', function () {
    ['project' => $project, 'provider' => $provider] = servicesOnlyProject();
    $intruder = User::factory()->create(['role' => UserRole::Client, 'portal_roles' => [UserRole::Client->value]]);

    $this->actingAs($intruder)->get(route('client.projects.show', $project))->assertNotFound();
    $this->actingAs($intruder)->post(route('client.projects.confirm', [$project, $provider]))->assertNotFound();
    $this->actingAs($intruder)->post(route('client.projects.pay', [$project, $provider]), ['payment_method' => 'wave'])->assertNotFound();
    $this->actingAs($intruder)->post(route('client.projects.messages.store', [$project, $provider]), ['message' => 'Bonjour'])->assertNotFound();
});

test('client and provider exchange messages on the project', function () {
    ['client' => $client, 'providerUser' => $providerUser, 'provider' => $provider, 'project' => $project] = servicesOnlyProject();

    $this->actingAs($client)
        ->post(route('client.projects.messages.store', [$project, $provider]), ['message' => 'Pouvez-vous installer la veille ?'])
        ->assertRedirect();

    $this->assertDatabaseHas('event_project_messages', [
        'event_project_id' => $project->id,
        'provider_id' => $provider->id,
        'sender_id' => $client->id,
        'recipient_id' => $providerUser->id,
    ]);

    $this->actingAs($providerUser)
        ->get(route('service-provider.reservations.show', $project))
        ->assertOk()
        ->assertSeeText('Pouvez-vous installer la veille ?');
});

test('services only project goes from quote to paid deposit with commission snapshot', function () {
    ['client' => $client, 'providerUser' => $providerUser, 'provider' => $provider, 'project' => $project] = servicesOnlyProject(300000, 90000);
    CommissionRule::factory()->create(['scope' => 'service_provider', 'percentage_rate' => 8]);

    $this->actingAs($client)
        ->post(route('client.projects.pay', [$project, $provider]), ['payment_method' => 'wave'])
        ->assertSessionHas('project_status', 'Aucun acompte n’est à payer pour ce prestataire. Le devis doit d’abord être validé par les deux parties.');

    $this->actingAs($client)->post(route('client.projects.confirm', [$project, $provider]))->assertRedirect();
    expect($project->items()->first()->status)->toBe(EventProjectItemStatus::AwaitingProviderConfirmation);

    $this->actingAs($providerUser)->post(route('service-provider.reservations.proforma.confirm', $project))->assertRedirect();
    expect($project->items()->first()->status)->toBe(EventProjectItemStatus::AwaitingPayment);

    $this->actingAs($client)
        ->get(route('client.projects.show', $project))
        ->assertSeeText('Acompte à payer : 90 000 XOF');

    $this->actingAs($client)
        ->post(route('client.projects.pay', [$project, $provider]), ['payment_method' => 'orange_money'])
        ->assertSessionHas('project_status', 'Paiement test validé : la prestation est confirmée.');

    $item = $project->items()->first();
    $payment = $project->payments()->firstOrFail();

    expect($item->status)->toBe(EventProjectItemStatus::Confirmed)
        ->and($item->paid_at)->not->toBeNull()
        ->and($payment->booking_id)->toBeNull()
        ->and($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->amount)->toBe(90000)
        ->and($payment->payment_method)->toBe('orange_money')
        ->and($payment->provider_id)->toBe($provider->id)
        ->and($payment->provider_payload['commission']['amount'])->toBe(24000)
        ->and($payment->subjectLabel())->toContain('Studio Son Lagune')
        ->and($project->refresh()->confirmed_total_amount)->toBe(300000);

    $this->actingAs($client)
        ->post(route('client.projects.pay', [$project, $provider]), ['payment_method' => 'wave']);

    expect($project->payments()->count())->toBe(1);

    $this->actingAs($client)
        ->get(route('client.payments'))
        ->assertOk()
        ->assertSeeText('Studio Son Lagune');

    $this->actingAs($providerUser)
        ->get(route('service-provider.reservations.show', $project))
        ->assertSeeText('Acompte payé par le client');
});

test('service without deposit is confirmed as soon as both parties accept', function () {
    ['client' => $client, 'providerUser' => $providerUser, 'provider' => $provider, 'project' => $project] = servicesOnlyProject(150000, 0);

    $this->actingAs($providerUser)->post(route('service-provider.reservations.proforma.confirm', $project));
    $this->actingAs($client)->post(route('client.projects.confirm', [$project, $provider]));

    expect($project->items()->first()->status)->toBe(EventProjectItemStatus::Confirmed);
});

test('services from suspended or unverified providers are hidden and cannot be booked', function () {
    $suspendedUser = User::factory()->create(['role' => UserRole::ServiceProvider, 'status' => UserStatus::Suspended]);
    $suspendedProvider = ServiceProviderProfile::factory()->verified()->create(['user_id' => $suspendedUser->id, 'business_name' => 'Prestataire suspendu']);
    $suspendedService = EventService::factory()->create([
        'service_provider_profile_id' => $suspendedProvider->id,
        'name' => 'Service suspendu',
        'status' => VenueStatus::Published,
    ]);
    $pendingProvider = ServiceProviderProfile::factory()->create(['business_name' => 'Prestataire en attente']);
    EventService::factory()->create([
        'service_provider_profile_id' => $pendingProvider->id,
        'name' => 'Service en attente',
        'status' => VenueStatus::Published,
    ]);
    $venue = Venue::factory()->create(['status' => VenueStatus::Published, 'published_at' => now(), 'max_capacity' => 200]);
    $client = User::factory()->create(['role' => UserRole::Client]);

    $this->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertDontSeeText('Service suspendu')
        ->assertDontSeeText('Service en attente');

    $this->get(route('service-provider-profiles.index'))
        ->assertDontSeeText('Prestataire suspendu')
        ->assertDontSeeText('Prestataire en attente');

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'accept_terms' => '1',
            'start_date' => now()->addDays(10)->toDateString(),
            'starts_at' => '10:00',
            'ends_at' => '18:00',
            'guests_count' => 50,
            'payment_method' => 'baobaa_checkout',
            'event_service_ids' => [$suspendedService->id],
        ])
        ->assertSessionHasErrors('event_service_ids.0');
});

test('provider uploads service photos shown on the public profile', function () {
    Storage::fake('wasabi');
    $providerUser = User::factory()->create(['role' => UserRole::ServiceProvider, 'portal_roles' => [UserRole::ServiceProvider->value]]);
    $provider = ServiceProviderProfile::factory()->verified()->create(['user_id' => $providerUser->id]);
    $service = EventService::factory()->create([
        'service_provider_profile_id' => $provider->id,
        'status' => VenueStatus::Published,
    ]);

    $this->actingAs($providerUser)
        ->patch(route('service-provider.services.update', $service), [
            'event_service_type_id' => $service->event_service_type_id,
            'name' => $service->name,
            'status' => 'published',
            'country_code' => 'CI',
            'city' => 'Abidjan',
            'pricing_unit' => 'event',
            'starting_price' => 100000,
            'images' => [UploadedFile::fake()->image('scene.jpg'), UploadedFile::fake()->image('regie.jpg')],
        ])
        ->assertRedirect();

    $media = $service->media()->get();

    expect($media)->toHaveCount(2)
        ->and($media->first()->is_primary)->toBeTrue();
    Storage::disk('wasabi')->assertExists($media->first()->path);

    $this->get(route('service-provider-profiles.show', $provider))
        ->assertOk()
        ->assertSeeText('2 photos');

    $this->actingAs($providerUser)
        ->delete(route('service-provider.services.media.destroy', [$service, $media->first()]))
        ->assertRedirect();

    expect($service->media()->count())->toBe(1)
        ->and($service->media()->first()->is_primary)->toBeTrue();
});

test('provider cannot delete the photo of another provider service', function () {
    $providerUser = User::factory()->create(['role' => UserRole::ServiceProvider, 'portal_roles' => [UserRole::ServiceProvider->value]]);
    ServiceProviderProfile::factory()->verified()->create(['user_id' => $providerUser->id]);
    $otherMedia = EventServiceMedia::factory()->create();

    $this->actingAs($providerUser)
        ->delete(route('service-provider.services.media.destroy', [$otherMedia->eventService, $otherMedia]))
        ->assertNotFound();

    $this->assertModelExists($otherMedia);
});

test('sap creates a commission rule for one service provider shown in its settings', function () {
    $sap = User::factory()->create(['role' => UserRole::Sap, 'portal_roles' => [UserRole::Sap->value]]);
    $providerUser = User::factory()->create(['role' => UserRole::ServiceProvider, 'portal_roles' => [UserRole::ServiceProvider->value]]);
    $provider = ServiceProviderProfile::factory()->verified()->create(['user_id' => $providerUser->id]);
    CommissionRule::factory()->create(['scope' => 'global', 'percentage_rate' => 10]);

    $this->actingAs($sap)
        ->post(route('sap.commissions.store'), [
            'name' => 'Commission prestataire négociée',
            'scope' => 'service_provider',
            'service_provider_profile_id' => $provider->id,
            'commission_type' => 'percentage',
            'percentage_rate' => 6,
        ])
        ->assertRedirect();

    $this->actingAs($providerUser)
        ->get(route('service-provider.settings'))
        ->assertOk()
        ->assertSeeText('6 % du montant de chaque réservation confirmée');
});
