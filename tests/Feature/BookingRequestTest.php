<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProformaInvoiceStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\BookingMessage;
use App\Models\EventProject;
use App\Models\EventProjectItem;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\OwnerDepositRule;
use App\Models\OwnerModuleTemplate;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueAddOn;
use App\Models\VenueMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest sees a premium reservation CTA and login requirement on the venue page', function () {
    $venue = Venue::factory()->create([
        'name' => 'Salle réservation sécurisée',
        'slug' => 'salle-reservation-securisee',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('Composer le package de mon événement')
        ->assertSeeText('Réserver uniquement un service événementiel')
        ->assertSee(route('venues.reservation', $venue->slug))
        ->assertSee(route('event-services.index'))
        ->assertSee(route('portal.login', ['portal' => 'client', 'redirect' => route('venues.show', $venue->slug)]))
        ->assertSeeText('Connexion client obligatoire avant la proforma et le paiement de l’acompte.');

    $this->assertDatabaseCount('bookings', 0);
});

test('venue detail keeps gallery available with a single image and initial active tab', function () {
    $venue = Venue::factory()->create([
        'name' => 'Jardin événementiel privatisable',
        'slug' => 'jardin-evenementiel-privatisable',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);
    VenueMedia::factory()->create([
        'venue_id' => $venue->id,
        'type' => 'image',
        'disk' => 'public',
        'path' => 'venues/jardin.jpg',
        'is_primary' => true,
        'sort_order' => 1,
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSee('data-gallery-index="0"', false)
        ->assertSee('id="venue-gallery-modal"', false)
        ->assertSee('id="gallery-active-image"', false)
        ->assertSee('id="gallery-counter"', false)
        ->assertSee('aria-current="true"', false);
});

test('client can open a full page reservation flow for a venue', function () {
    $venue = Venue::factory()->create([
        'name' => 'Salle réservation longue page',
        'slug' => 'salle-reservation-longue-page',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->actingAs($client)
        ->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertSeeText('Composez votre événement avec les bons prestataires.')
        ->assertSeeText('Package événementiel');
});

test('venue package page lets client select published event services outside venue city', function () {
    $venue = Venue::factory()->create([
        'name' => 'Salle package avec services',
        'slug' => 'salle-package-avec-services',
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'city' => 'Abidjan',
    ]);

    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $provider = ServiceProviderProfile::factory()->create([
        'business_name' => 'Studio Son Signature',
        'city' => 'Dakar',
    ]);
    $type = EventServiceType::factory()->create([
        'name' => 'Sonorisation',
    ]);
    EventService::factory()->create([
        'service_provider_profile_id' => $provider->id,
        'event_service_type_id' => $type->id,
        'name' => 'Pack audio conférence',
        'city' => 'Dakar',
        'status' => VenueStatus::Published,
    ]);

    $this->actingAs($client)
        ->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertSeeText('Choisir les prestataires événementiels')
        ->assertSeeText('Studio Son Signature')
        ->assertSeeText('Pack audio conférence')
        ->assertDontSeeText('PSE');
});

test('venue package page recovers active owner modules when venue modules were not synced yet', function () {
    $owner = User::factory()->create([
        'role' => UserRole::Owner,
        'portal_roles' => [UserRole::Owner->value],
    ]);
    $ownerProfile = OwnerProfile::factory()->create([
        'user_id' => $owner->id,
    ]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Villa événementielle lagune avec jardin privé',
        'slug' => 'villa-evenementielle-lagune-avec-jardin-prive',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    OwnerModuleTemplate::query()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Sonorisation premium',
        'description' => 'Microphones et régie',
        'price' => 150000,
        'currency' => 'XOF',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect($venue->addOns()->exists())->toBeFalse();

    $this->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertSeeText('Ajouter les modules optionnels')
        ->assertSeeText('Sonorisation premium')
        ->assertSee('selected_module_template_ids[]', false);
});

test('guest sees client login before saving a booking request', function () {
    $venue = Venue::factory()->create([
        'name' => 'Salle réservation sécurisée',
        'slug' => 'salle-reservation-securisee',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('Me connecter pour démarrer')
        ->assertSee(route('portal.login', ['portal' => 'client', 'redirect' => route('venues.show', $venue->slug)]))
        ->assertSeeText('Connexion client obligatoire avant la proforma et le paiement de l’acompte.');

    $this->assertDatabaseCount('bookings', 0);
});

test('sap can list service providers with their published services and reservations', function () {
    $sap = User::factory()->create([
        'role' => UserRole::Sap,
        'portal_roles' => [UserRole::Sap->value],
    ]);

    $profile = ServiceProviderProfile::factory()->create([
        'business_name' => 'Studio Lumière Signature',
        'city' => 'Abidjan',
        'verification_status' => 'verified',
    ]);

    EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'name' => 'Sonorisation mobile',
        'city' => 'Abidjan',
        'status' => VenueStatus::Published,
        'starting_price' => 120000,
    ]);

    $project = EventProject::factory()->create([
        'client_id' => User::factory()->create(['role' => UserRole::Client])->id,
    ]);

    EventProjectItem::factory()->create([
        'event_project_id' => $project->id,
        'provider_type' => 'service_provider_profile',
        'provider_id' => $profile->id,
        'title' => 'Sonorisation mobile',
        'status' => 'negotiating',
    ]);

    $this->actingAs($sap)
        ->get(route('sap.providers'))
        ->assertOk()
        ->assertSeeText('PSE')
        ->assertSeeText('Studio Lumière Signature')
        ->assertSeeText('Sonorisation mobile')
        ->assertSeeText('Réservations');
});

test('public venue flow lets clients choose a service provider before its services', function () {
    $venue = Venue::factory()->create([
        'name' => 'Salle premium',
        'slug' => 'salle-premium',
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'city' => 'Abidjan',
    ]);

    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);

    $profile = ServiceProviderProfile::factory()->create([
        'user_id' => $pse->id,
        'business_name' => 'Studio Lumière Signature',
        'city' => 'Abidjan',
    ]);

    EventService::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'name' => 'Sonorisation mobile',
        'city' => 'Abidjan',
        'status' => VenueStatus::Published,
        'starting_price' => 120000,
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('Choisir un prestataire événementiel')
        ->assertSeeText('Studio Lumière Signature')
        ->assertSeeText('Services du prestataire');
});

test('client can save a booking request and gets an initiated deposit payment', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $venue = Venue::factory()->create([
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 450000,
        'reservation_amount' => 90000,
        'max_capacity' => 250,
        'payment_methods' => ['wave', 'orange_money'],
    ]);
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $venue->owner_profile_id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 30,
        'minimum_amount' => 25000,
        'maximum_amount' => 500000,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'event_type' => 'conference',
            'guests_count' => 120,
            'payment_method' => 'wave',
        ])
        ->assertRedirect();

    $booking = Booking::query()->first();

    expect($booking)
        ->not->toBeNull()
        ->and($booking->client_id)->toBe($client->id)
        ->and($booking->venue_id)->toBe($venue->id)
        ->and($booking->status)->toBe(BookingStatus::PendingPayment)
        ->and($booking->reservation_amount)->toBe(135000);

    $payment = Payment::query()->first();

    expect($payment)
        ->not->toBeNull()
        ->and($payment->booking_id)->toBe($booking->id)
        ->and($payment->payer_id)->toBe($client->id)
        ->and($payment->status)->toBe(PaymentStatus::Initiated)
        ->and($payment->amount)->toBe(135000)
        ->and($payment->payment_method)->toBe('wave');

    $invoice = $booking->proformaInvoice()->with('items')->first();

    expect($invoice)
        ->not->toBeNull()
        ->and($invoice->status)->toBe(ProformaInvoiceStatus::Sent)
        ->and($invoice->total_amount)->toBe(450000)
        ->and($invoice->deposit_amount)->toBe(135000)
        ->and($invoice->items)->toHaveCount(1);

    expect($booking->eventProjectItem)
        ->not->toBeNull()
        ->and($booking->eventProjectItem->eventProject->client_id)->toBe($client->id)
        ->and($booking->eventProjectItem->quoted_amount)->toBe(450000)
        ->and($booking->eventProjectItem->deposit_amount)->toBe(135000);
});

test('client can include venue optional modules in a package reservation', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $venue = Venue::factory()->create([
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 500000,
        'reservation_amount' => 100000,
        'max_capacity' => 250,
        'payment_methods' => ['baobaa_checkout'],
    ]);

    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $venue->owner_profile_id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 20,
        'minimum_amount' => 25000,
        'maximum_amount' => null,
    ]);

    $audio = VenueAddOn::factory()->create([
        'venue_id' => $venue->id,
        'name' => 'Système audio premium',
        'price' => 150000,
        'currency' => 'XOF',
        'is_available' => true,
        'sort_order' => 1,
    ]);

    $podium = VenueAddOn::factory()->create([
        'venue_id' => $venue->id,
        'name' => 'Podium modulable',
        'price' => 75000,
        'currency' => 'XOF',
        'is_available' => true,
        'sort_order' => 2,
    ]);

    VenueAddOn::factory()->create([
        'venue_id' => $venue->id,
        'name' => 'Module indisponible',
        'price' => 50000,
        'currency' => 'XOF',
        'is_available' => false,
        'sort_order' => 3,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'event_type' => 'conference',
            'guests_count' => 120,
            'payment_method' => 'baobaa_checkout',
            'selected_add_on_ids' => [$audio->id, $podium->id],
        ])
        ->assertRedirect();

    $booking = Booking::query()->with(['proformaInvoice.items', 'eventProjectItem', 'payments'])->firstOrFail();

    expect($booking->selected_add_ons)->toHaveCount(2)
        ->and($booking->selected_add_ons[0]['name'])->toBe('Système audio premium')
        ->and($booking->selected_add_ons[1]['name'])->toBe('Podium modulable')
        ->and($booking->total_amount)->toBe(725000)
        ->and($booking->reservation_amount)->toBe(145000);

    expect($booking->proformaInvoice)
        ->not->toBeNull()
        ->and($booking->proformaInvoice->total_amount)->toBe(725000)
        ->and($booking->proformaInvoice->deposit_amount)->toBe(145000)
        ->and($booking->proformaInvoice->items)->toHaveCount(3);

    expect($booking->proformaInvoice->items->pluck('description')->all())->toContain(
        $venue->name,
        'Système audio premium',
        'Podium modulable'
    );

    expect($booking->eventProjectItem)
        ->quoted_amount->toBe(725000)
        ->deposit_amount->toBe(145000)
        ->and($booking->eventProjectItem->metadata['selected_add_ons'])->toHaveCount(2);

    expect($booking->payments->first())
        ->amount->toBe(145000)
        ->and($booking->payments->first()->provider_payload['selected_add_ons'])->toHaveCount(2);
});

test('client can include recovered owner module templates in a package reservation', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);
    $ownerProfile = OwnerProfile::factory()->create();
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 500000,
        'reservation_amount' => 100000,
        'max_capacity' => 250,
        'payment_methods' => ['baobaa_checkout'],
    ]);
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 20,
        'minimum_amount' => 25000,
        'maximum_amount' => null,
    ]);
    $module = OwnerModuleTemplate::query()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Service café et thé',
        'description' => 'Accueil et pauses chaudes',
        'price' => 50000,
        'currency' => 'XOF',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'event_type' => 'conference',
            'guests_count' => 120,
            'payment_method' => 'baobaa_checkout',
            'selected_module_template_ids' => [$module->id],
        ])
        ->assertRedirect();

    $booking = Booking::query()->with(['proformaInvoice.items', 'payments'])->firstOrFail();

    expect($booking->selected_add_ons)->toHaveCount(1)
        ->and($booking->selected_add_ons[0]['source_type'])->toBe('owner_module_template')
        ->and($booking->selected_add_ons[0]['name'])->toBe('Service café et thé')
        ->and($booking->total_amount)->toBe(550000)
        ->and($booking->reservation_amount)->toBe(110000)
        ->and($booking->proformaInvoice->items)->toHaveCount(2)
        ->and($booking->payments->first()->amount)->toBe(110000);
});

test('client can update package modules and services before deposit payment', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $ownerProfile = OwnerProfile::factory()->create();
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 500000,
        'reservation_amount' => 100000,
        'max_capacity' => 250,
        'payment_methods' => ['baobaa_checkout'],
    ]);
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 20,
        'minimum_amount' => 25000,
        'maximum_amount' => null,
    ]);
    $audio = VenueAddOn::factory()->create([
        'venue_id' => $venue->id,
        'name' => 'Système audio premium',
        'price' => 50000,
        'currency' => 'XOF',
        'is_available' => true,
        'sort_order' => 1,
    ]);
    $podium = VenueAddOn::factory()->create([
        'venue_id' => $venue->id,
        'name' => 'Podium modulable',
        'price' => 75000,
        'currency' => 'XOF',
        'is_available' => true,
        'sort_order' => 2,
    ]);
    $provider = ServiceProviderProfile::factory()->create([
        'business_name' => 'Studio Lumière Signature',
        'city' => $venue->city,
    ]);
    $type = EventServiceType::factory()->create([
        'name' => 'Lumière',
    ]);
    $firstService = EventService::factory()->create([
        'service_provider_profile_id' => $provider->id,
        'event_service_type_id' => $type->id,
        'name' => 'Pack lumière entrée',
        'status' => VenueStatus::Published,
        'starting_price' => 100000,
    ]);
    $secondService = EventService::factory()->create([
        'service_provider_profile_id' => $provider->id,
        'event_service_type_id' => $type->id,
        'name' => 'Pack lumière scène',
        'status' => VenueStatus::Published,
        'starting_price' => 80000,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'event_type' => 'conference',
            'guests_count' => 120,
            'payment_method' => 'baobaa_checkout',
            'selected_add_on_ids' => [$audio->id],
            'event_service_ids' => [$firstService->id],
        ])
        ->assertRedirect();

    $booking = Booking::query()->firstOrFail();

    $this->actingAs($client)
        ->get(route('client.reservations.show', $booking))
        ->assertOk()
        ->assertSeeText('Ajuster mon package')
        ->assertSeeText('Système audio premium')
        ->assertSeeText('Pack lumière entrée');

    $this->actingAs($client)
        ->patch(route('client.reservations.package.update', $booking), [
            'selected_add_on_ids' => [$podium->id],
            'add_on_quantities' => [
                $podium->id => 2,
            ],
            'event_service_ids' => [$secondService->id],
            'event_service_quantities' => [
                $secondService->id => 3,
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('package_status');

    $booking->refresh()->load(['proformaInvoice.items', 'payments', 'eventProjectItem.eventProject.items']);

    expect($booking->selected_add_ons)->toHaveCount(1)
        ->and($booking->selected_add_ons[0]['name'])->toBe('Podium modulable')
        ->and($booking->selected_add_ons[0]['quantity'])->toBe(2)
        ->and($booking->total_amount)->toBe(650000)
        ->and($booking->reservation_amount)->toBe(130000)
        ->and($booking->proformaInvoice->total_amount)->toBe(650000)
        ->and($booking->proformaInvoice->deposit_amount)->toBe(130000)
        ->and($booking->payments->first()->amount)->toBe(130000);

    expect($booking->proformaInvoice->items->pluck('description')->all())->toContain('Podium modulable')
        ->and($booking->proformaInvoice->items->last()->quantity)->toBe(2)
        ->and($booking->proformaInvoice->items->last()->total_price)->toBe(150000);

    $project = $booking->eventProjectItem->eventProject;

    expect($project->items()->where('item_type', 'event_service')->where('source_id', $firstService->id)->exists())->toBeFalse();

    $serviceItem = $project->items()->where('item_type', 'event_service')->where('source_id', $secondService->id)->firstOrFail();

    expect($serviceItem->quoted_amount)->toBe(240000)
        ->and($serviceItem->metadata['quantity'])->toBe(3);
});

test('client can start a booking with a negotiated amount and initial message', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $owner = User::factory()->create([
        'role' => UserRole::Owner,
        'portal_roles' => [UserRole::Owner->value],
    ]);

    $ownerProfile = OwnerProfile::factory()->create([
        'user_id' => $owner->id,
    ]);

    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 500000,
        'reservation_amount' => 100000,
        'max_capacity' => 250,
        'payment_methods' => ['wave'],
    ]);

    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 20,
        'minimum_amount' => 10000,
        'maximum_amount' => null,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'starts_at' => '10:00',
            'ends_at' => '17:00',
            'event_type' => 'conference',
            'guests_count' => 90,
            'payment_method' => 'wave',
            'booking_intent' => 'negotiate',
            'proposed_amount' => 420000,
            'client_notes' => 'Budget validé par notre comité, pouvons-nous avancer sur cette base ?',
        ])
        ->assertRedirect();

    $booking = Booking::query()->firstOrFail();

    expect($booking->total_amount)->toBe(420000)
        ->and($booking->reservation_amount)->toBe(84000)
        ->and($booking->client_notes)->toBe('Budget validé par notre comité, pouvons-nous avancer sur cette base ?');

    expect($booking->messages()->first())
        ->message->toBe('Budget validé par notre comité, pouvons-nous avancer sur cette base ?')
        ->proposed_amount->toBe(420000)
        ->recipient_id->toBe($owner->id);

    expect($booking->proformaInvoice()->first())
        ->total_amount->toBe(420000)
        ->deposit_amount->toBe(84000);

    expect($booking->payments()->first())
        ->amount->toBe(84000)
        ->payment_method->toBe('wave');
});

test('client can attach PSE services and SAP ticketing to a venue booking project', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);
    $owner = User::factory()->create([
        'role' => UserRole::Owner,
        'portal_roles' => [UserRole::Owner->value],
    ]);
    $pse = User::factory()->create([
        'role' => UserRole::ServiceProvider,
        'portal_roles' => [UserRole::ServiceProvider->value],
    ]);
    $ownerProfile = OwnerProfile::factory()->create([
        'user_id' => $owner->id,
    ]);
    $serviceProviderProfile = ServiceProviderProfile::factory()->create([
        'user_id' => $pse->id,
        'business_name' => 'Studio Lumière Signature',
        'city' => 'Abidjan',
    ]);
    $serviceType = EventServiceType::factory()->create([
        'name' => 'Lumière',
        'is_active' => true,
    ]);
    $eventService = EventService::factory()->create([
        'service_provider_profile_id' => $serviceProviderProfile->id,
        'event_service_type_id' => $serviceType->id,
        'name' => 'Pack lumière gala',
        'status' => VenueStatus::Published,
        'city' => 'Abidjan',
        'starting_price' => 250000,
        'deposit_amount' => 50000,
    ]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'city' => 'Abidjan',
        'starting_price' => 500000,
        'reservation_amount' => 100000,
        'max_capacity' => 250,
        'payment_methods' => ['baobaa_checkout'],
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'starts_at' => '10:00',
            'ends_at' => '17:00',
            'event_type' => 'gala',
            'guests_count' => 120,
            'payment_method' => 'baobaa_checkout',
            'booking_intent' => 'reserve',
            'event_service_ids' => [$eventService->id],
            'ticketing_requested' => true,
        ])
        ->assertRedirect();

    $booking = Booking::query()->firstOrFail();
    $project = $booking->eventProjectItem->eventProject()->with('items')->firstOrFail();

    expect($project->items)->toHaveCount(3)
        ->and($project->items->pluck('item_type')->sort()->values()->all())->toBe([
            'event_service',
            'ticketing',
            'venue_booking',
        ])
        ->and($project->items()->where('item_type', 'event_service')->first())
        ->title->toBe('Pack lumière gala')
        ->provider_type->toBe('service_provider_profile')
        ->provider_id->toBe($serviceProviderProfile->id)
        ->quoted_amount->toBe(250000)
        ->and($project->items()->where('item_type', 'ticketing')->first())
        ->title->toBe('Billetterie BAOBAA')
        ->provider_type->toBe('sap')
        ->quoted_amount->toBe(0);
});

test('booking deposit falls back to venue reservation amount when sap rule is missing', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $venue = Venue::factory()->create([
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 450000,
        'reservation_amount' => 90000,
        'max_capacity' => 250,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'event_type' => 'conference',
            'guests_count' => 120,
            'payment_method' => 'baobaa_checkout',
        ])
        ->assertRedirect();

    expect(Booking::query()->first())
        ->reservation_amount->toBe(90000);

    expect(Payment::query()->first())
        ->amount->toBe(90000)
        ->payment_method->toBe('baobaa_checkout');
});

test('client cannot choose a payment method disabled for the venue', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $venue = Venue::factory()->create([
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'payment_methods' => ['wave'],
        'max_capacity' => 250,
    ]);

    $this->actingAs($client)
        ->post(route('bookings.store', $venue), [
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'starts_at' => '09:00',
            'ends_at' => '18:00',
            'event_type' => 'conference',
            'guests_count' => 120,
            'payment_method' => 'bank_transfer',
        ])
        ->assertSessionHasErrors('payment_method');

    $this->assertDatabaseCount('bookings', 0);
    $this->assertDatabaseCount('payments', 0);
});

test('client and owner negotiate confirm proforma and client pays test deposit', function () {
    $client = User::factory()->create([
        'role' => UserRole::Client,
        'portal_roles' => [UserRole::Client->value],
    ]);
    $owner = User::factory()->create([
        'role' => UserRole::Owner,
        'portal_roles' => [UserRole::Owner->value],
    ]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => OwnerProfile::factory()->create(['user_id' => $owner->id])->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 400000,
        'reservation_amount' => 100000,
        'payment_methods' => ['baobaa_checkout'],
    ]);
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $venue->owner_profile_id,
        'deposit_type' => 'percentage',
        'percentage_rate' => 25,
        'minimum_amount' => 10000,
        'maximum_amount' => null,
    ]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $venue->owner_profile_id,
        'venue_id' => $venue->id,
        'status' => BookingStatus::PendingPayment,
        'total_amount' => 400000,
        'reservation_amount' => 100000,
    ]);
    Payment::factory()->create([
        'booking_id' => $booking->id,
        'payer_id' => $client->id,
        'status' => PaymentStatus::Initiated,
        'amount' => 100000,
    ]);
    ProformaInvoice::factory()
        ->has(ProformaInvoiceItem::factory()->count(1), 'items')
        ->create([
            'booking_id' => $booking->id,
            'status' => ProformaInvoiceStatus::Sent,
            'total_amount' => 400000,
            'deposit_amount' => 100000,
        ]);

    $this->actingAs($client)
        ->get(route('client.reservations.show', $booking))
        ->assertOk()
        ->assertSeeText('Facture proforma')
        ->assertSeeText('Paiement test après double confirmation');

    $this->actingAs($client)
        ->post(route('client.reservations.messages.store', $booking), [
            'message' => 'Pouvez-vous ajuster le budget pour notre association ?',
            'proposed_amount' => 350000,
        ])
        ->assertRedirect();

    expect(BookingMessage::query()->first())
        ->message->toBe('Pouvez-vous ajuster le budget pour notre association ?')
        ->proposed_amount->toBe(350000);

    expect($booking->refresh())
        ->total_amount->toBe(350000)
        ->reservation_amount->toBe(87500);

    $invoice = $booking->proformaInvoice()->with('items')->first();

    expect($invoice)
        ->total_amount->toBe(350000)
        ->deposit_amount->toBe(87500)
        ->status->toBe(ProformaInvoiceStatus::Sent)
        ->and($invoice->items->first()->total_price)->toBe(350000);

    expect($booking->payments()->first())
        ->amount->toBe(87500);

    expect($booking->eventProjectItem()->first())
        ->quoted_amount->toBe(350000)
        ->deposit_amount->toBe(87500);

    $this->actingAs($client)
        ->post(route('client.reservations.test-payment', $booking))
        ->assertRedirect()
        ->assertSessionHas('payment_status', 'La proforma doit être confirmée par le client et le partenaire avant le paiement.');

    $this->actingAs($client)
        ->post(route('client.reservations.proforma.confirm', $booking))
        ->assertRedirect();

    expect($booking->proformaInvoice()->first()->status)->toBe(ProformaInvoiceStatus::AcceptedByClient);

    $this->actingAs($owner)
        ->post(route('owner.bookings.messages.store', $booking), [
            'message' => 'Nous validons ce principe et gardons les services inclus.',
        ])
        ->assertRedirect();

    $this->actingAs($owner)
        ->post(route('owner.bookings.proforma.confirm', $booking))
        ->assertRedirect();

    expect($booking->proformaInvoice()->first()->status)->toBe(ProformaInvoiceStatus::Confirmed);

    $this->actingAs($client)
        ->post(route('client.reservations.test-payment', $booking))
        ->assertRedirect()
        ->assertSessionHas('payment_status', 'Paiement test validé. L’acompte est enregistré dans l’historique.');

    expect($booking->refresh()->status)->toBe(BookingStatus::PendingOwner)
        ->and($booking->payments()->first()->status)->toBe(PaymentStatus::Succeeded);
});

test('client cannot open another client booking workflow', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $otherClient = User::factory()->create(['role' => UserRole::Client]);
    $booking = Booking::factory()->create(['client_id' => $otherClient->id]);

    $this->actingAs($client)
        ->get(route('client.reservations.show', $booking))
        ->assertNotFound();
});

test('legacy booking detail generates missing proforma and deposit payment', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'status' => VenueStatus::Published,
        'published_at' => now(),
        'starting_price' => 650000,
        'reservation_amount' => 150000,
        'payment_methods' => ['wave'],
    ]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $ownerProfile->id,
        'venue_id' => $venue->id,
        'status' => BookingStatus::PendingPayment,
        'total_amount' => 650000,
        'reservation_amount' => 150000,
    ]);

    $this->assertDatabaseMissing('proforma_invoices', ['booking_id' => $booking->id]);
    $this->assertDatabaseMissing('payments', ['booking_id' => $booking->id]);

    $this->actingAs($owner)
        ->get(route('owner.bookings.show', $booking))
        ->assertOk()
        ->assertSeeText('Facture proforma');

    $this->assertDatabaseHas('proforma_invoices', [
        'booking_id' => $booking->id,
        'total_amount' => 650000,
        'deposit_amount' => 150000,
    ]);
    $this->assertDatabaseHas('payments', [
        'booking_id' => $booking->id,
        'payer_id' => $client->id,
        'status' => PaymentStatus::Initiated,
        'amount' => 150000,
        'payment_method' => 'wave',
    ]);
    $this->assertDatabaseHas('event_projects', [
        'client_id' => $client->id,
        'estimated_total_amount' => 650000,
    ]);
    $this->assertDatabaseHas('event_project_items', [
        'provider_type' => 'owner_profile',
        'provider_id' => $ownerProfile->id,
        'source_type' => Venue::class,
        'source_id' => $venue->id,
        'quoted_amount' => 650000,
        'deposit_amount' => 150000,
    ]);

    $this->actingAs($client)
        ->get(route('client.reservations.show', $booking))
        ->assertOk()
        ->assertSeeText('Paiement test après double confirmation');
});

test('client can see generated event projects', function () {
    $client = User::factory()->create(['role' => UserRole::Client]);
    $project = EventProject::factory()->create([
        'client_id' => $client->id,
        'name' => 'Gala entreprise BAOBAA',
        'estimated_total_amount' => 900000,
    ]);
    EventProjectItem::factory()->create([
        'event_project_id' => $project->id,
        'title' => 'Salle premium Cocody',
        'quoted_amount' => 900000,
        'deposit_amount' => 180000,
    ]);

    $this->actingAs($client)
        ->get(route('client.projects'))
        ->assertOk()
        ->assertSeeText('Gala entreprise BAOBAA')
        ->assertSeeText('Salle premium Cocody')
        ->assertSeeText('900 000 XOF');
});
