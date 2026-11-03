<?php

use App\Enums\BookingStatus;
use App\Enums\EventProjectItemStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProformaInvoiceStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\CommissionRule;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\OwnerDepositRule;
use App\Models\PortalAccessRequest;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use App\Services\EventProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * End-to-end marketplace journey: a client, a venue owner (PEE) and a professional sign up,
 * the SAP validates the partners, the owner publishes a venue with options, the professional publishes
 * a service, then the client books (resuming the form after login) and negotiates with both partners.
 */
test('client, venue owner and professional complete a full booking and negotiation journey', function () {
    $sap = User::factory()->create(['role' => UserRole::Sap, 'portal_roles' => [UserRole::Sap->value]]);
    $category = VenueCategory::factory()->create(['name' => 'Salles de réception', 'is_active' => true, 'sort_order' => 1]);
    $serviceType = EventServiceType::factory()->create(['name' => 'DJ et sonorisation', 'is_active' => true]);
    CommissionRule::factory()->create(['scope' => 'service_provider', 'commission_type' => 'percentage', 'percentage_rate' => 10]);

    $password = ['password' => 'Baobaa!2026', 'password_confirmation' => 'Baobaa!2026'];
    $eventDay = now()->addMonth()->toDateString();

    // 1. Accounts: client, venue owner and professional sign up.
    $this->post(route('client.register.store'), [
        'name' => 'Aïcha Client', 'email' => 'client.journey@baobaa.local', 'phone' => '+2250700000001',
        'client_type' => 'individual', 'accept_terms' => '1', ...$password,
    ])->assertRedirect(route('client.dashboard'));
    $client = User::query()->where('email', 'client.journey@baobaa.local')->firstOrFail();
    auth()->logout();

    $this->post(route('owner.register.store'), [
        'name' => 'Koffi Propriétaire', 'email' => 'pee.journey@baobaa.local', 'phone' => '+2250700000002',
        'applicant_type' => 'company', 'business_name' => 'Lagune Events SARL',
        'country_code' => 'CI', 'city' => 'Abidjan', 'accept_terms' => '1', ...$password,
    ])->assertRedirect();
    $owner = User::query()->where('email', 'pee.journey@baobaa.local')->firstOrFail();
    auth()->logout();

    $this->post(route('service-provider.register.store'), [
        'name' => 'Moussa Pro', 'email' => 'pro.journey@baobaa.local', 'phone' => '+2250700000003',
        'business_name' => 'Moussa Sound System', 'country_code' => 'CI', 'city' => 'Abidjan',
        'accept_terms' => '1', ...$password,
    ])->assertRedirect(route('service-provider.dashboard'));
    $proUser = User::query()->where('email', 'pro.journey@baobaa.local')->firstOrFail();
    $pro = $proUser->serviceProviderProfile;
    auth()->logout();

    expect($proUser->hasPortal(UserRole::Client))->toBeFalse()
        ->and($owner->hasPortal(UserRole::Owner))->toBeFalse();

    // 2. The SAP validates the owner request and the professional.
    $ownerRequest = PortalAccessRequest::query()->where('user_id', $owner->id)->where('requested_role', UserRole::Owner)->firstOrFail();
    $this->actingAs($sap)->post(route('sap.portal-requests.decide', $ownerRequest), ['decision' => 'approve'])->assertRedirect();
    $this->actingAs($sap)->post(route('sap.providers.verification', $pro), ['decision' => 'approve'])->assertRedirect();

    expect($owner->refresh()->hasPortal(UserRole::Owner))->toBeTrue()
        ->and($pro->refresh()->verification_status->value)->toBe('verified');

    $ownerProfile = $owner->ownerProfile;
    OwnerDepositRule::factory()->create([
        'owner_profile_id' => $ownerProfile->id, 'deposit_type' => 'percentage', 'percentage_rate' => 30,
        'minimum_amount' => 0, 'maximum_amount' => 10000000, 'is_active' => true, 'starts_at' => null, 'ends_at' => null,
    ]);

    // 3. The owner creates two venue options, then a venue offering them, and publishes it.
    $this->actingAs($owner)->post(route('owner.addons.store'), ['name' => 'Traiteur cocktail', 'description' => 'Buffet pour 150 invités', 'price' => 200000])->assertRedirect();
    $this->actingAs($owner)->post(route('owner.addons.store'), ['name' => 'Décoration florale', 'price' => 80000])->assertRedirect();
    $moduleIds = $ownerProfile->moduleTemplates()->pluck('id')->all();

    $this->actingAs($owner)->post(route('owner.venues.draft.store'), [
        'step' => 'base', 'name' => 'Salle Lagune Prestige', 'venue_category_id' => $category->id,
        'country_code' => 'CI', 'city' => 'Abidjan', 'district' => 'Cocody',
        'min_capacity' => 50, 'max_capacity' => 300, 'starting_price' => 500000, 'price_unit' => 'event',
        'booking_mode' => 'request', 'payment_methods' => ['wave', 'orange_money'],
    ])->assertRedirect();
    $venue = Venue::query()->where('name', 'Salle Lagune Prestige')->firstOrFail();

    foreach ([
        ['step' => 'details', 'short_description' => 'Salle lumineuse au bord de la lagune.', 'description' => 'Une salle de réception de 300 places avec terrasse.'],
        ['step' => 'modules', 'module_template_ids' => $moduleIds],
        ['step' => 'localisation', 'address' => 'Boulevard de la lagune, Cocody', 'opens_at' => '08:00', 'closes_at' => '23:00'],
    ] as $stepPayload) {
        $this->actingAs($owner)->post(route('owner.venues.draft.store'), ['venue_id' => $venue->id, ...$stepPayload])->assertSessionHasNoErrors();
    }

    $this->actingAs($owner)->post(route('owner.venues.status', $venue), ['action' => 'activate'])->assertRedirect();

    expect($venue->refresh()->status)->toBe(VenueStatus::Published)
        ->and($venue->addOns()->count())->toBe(2);
    $cateringOption = $venue->addOns()->where('name', 'Traiteur cocktail')->firstOrFail();

    // 4. The professional publishes a service.
    $this->actingAs($proUser)->post(route('service-provider.services.store'), [
        'event_service_type_id' => $serviceType->id, 'name' => 'DJ et sonorisation soirée', 'status' => 'published',
        'country_code' => 'CI', 'city' => 'Abidjan', 'pricing_unit' => 'event',
        'starting_price' => 300000, 'deposit_amount' => 90000,
    ])->assertRedirect();
    $service = EventService::query()->where('name', 'DJ et sonorisation soirée')->firstOrFail();
    auth()->logout();

    $this->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertSeeText('Traiteur cocktail')
        ->assertSeeText('DJ et sonorisation soirée');

    // 5. As a guest, the client fills the form, signs in, and finds the form filled again.
    $bookingForm = [
        'event_start' => $eventDay.'T18:00', 'event_end' => $eventDay.'T23:30', 'event_type' => 'mariage',
        'guests_count' => 150, 'payment_method' => 'wave', 'client_notes' => 'Mariage en soirée avec terrasse.',
        'selected_add_on_ids' => [$cateringOption->id], 'event_service_ids' => [$service->id],
    ];

    $this->post(route('venues.reservation.draft', $venue), [...$bookingForm, 'next' => 'login'])
        ->assertRedirect(route('portal.login', ['portal' => 'client', 'redirect' => route('venues.reservation', $venue->slug)]));

    $this->post(route('portal.login.store', ['portal' => 'client']), ['email' => 'client.journey@baobaa.local', 'password' => 'Baobaa!2026'])
        ->assertRedirect(route('venues.reservation', $venue->slug));

    $this->get(route('venues.reservation', $venue->slug))
        ->assertOk()
        ->assertSee('value="'.$eventDay.'T18:00"', false)
        ->assertSee('value="150"', false)
        ->assertSee('Mariage en soirée avec terrasse.')
        ->assertSee('value="'.$cateringOption->id.'" data-addon-checkbox', false);

    $this->post(route('bookings.store', $venue), [...$bookingForm, 'accept_terms' => '1'])->assertRedirect();

    $booking = Booking::query()->where('client_id', $client->id)->firstOrFail();
    $project = $booking->eventProjectItem->eventProject;

    expect($booking->total_amount)->toBe(700000)
        ->and($booking->reservation_amount)->toBe(210000)
        ->and(substr((string) $booking->starts_at, 0, 5))->toBe('18:00')
        ->and(app(EventProjectService::class)->providerLaneItems($project, $pro->id))->toHaveCount(1);

    // 6. Negotiation with the venue owner: the client proposes, the owner counters, both confirm, the client pays.
    $this->actingAs($client)->post(route('client.reservations.messages.store', $booking), ['message' => 'Pouvez-vous faire 420 000 pour la salle ?', 'proposed_amount' => 420000])->assertRedirect();
    $booking->refresh();
    expect($booking->total_amount)->toBe(620000)
        ->and($booking->reservation_amount)->toBe(186000)
        ->and($booking->proformaInvoice->total_amount)->toBe(620000)
        ->and($booking->eventProjectItem->refresh()->quoted_amount)->toBe(620000);

    $this->actingAs($owner)->get(route('owner.bookings.show', $booking))->assertOk()->assertSeeText('Pouvez-vous faire 420 000 pour la salle ?');
    $this->actingAs($owner)->post(route('owner.bookings.messages.store', $booking), ['message' => 'Je peux descendre à 450 000.', 'proposed_amount' => 450000])->assertRedirect();
    $booking->refresh();
    expect($booking->total_amount)->toBe(650000)
        ->and($booking->reservation_amount)->toBe(195000)
        ->and($booking->payments()->where('status', PaymentStatus::Initiated)->value('amount'))->toBe(195000);

    $this->actingAs($client)->post(route('client.reservations.proforma.confirm', $booking))->assertRedirect();
    $this->actingAs($owner)->post(route('owner.bookings.proforma.confirm', $booking))->assertRedirect();
    expect($booking->proformaInvoice->refresh()->status)->toBe(ProformaInvoiceStatus::Confirmed);

    $this->actingAs($client)->post(route('client.reservations.test-payment', $booking))->assertRedirect();
    $this->actingAs($owner)->post(route('owner.bookings.status', $booking), ['action' => 'confirm'])->assertRedirect();
    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->payments()->where('status', PaymentStatus::Succeeded)->value('amount'))->toBe(195000);

    // 7. Negotiation with the professional: the client proposes, the pro counters, both confirm, the client pays.
    $this->actingAs($client)->post(route('client.projects.messages.store', [$project, $pro]), ['message' => 'Possible à 250 000 ?', 'proposed_amount' => 250000])->assertRedirect();
    $laneItem = app(EventProjectService::class)->providerLaneItems($project, $pro->id)->first();
    expect($laneItem->quoted_amount)->toBe(250000)
        ->and($laneItem->deposit_amount)->toBe(75000)
        ->and($laneItem->status)->toBe(EventProjectItemStatus::Negotiating);

    $this->actingAs($proUser)->get(route('service-provider.reservations.show', $project))->assertOk()->assertSeeText('Possible à 250 000 ?');
    $this->actingAs($proUser)->post(route('service-provider.reservations.messages.store', $project), ['message' => 'Je vous propose 270 000 avec les lumières.', 'proposed_amount' => 270000])->assertRedirect();
    $this->actingAs($proUser)->post(route('service-provider.reservations.proforma.confirm', $project))->assertRedirect();
    $this->actingAs($client)->post(route('client.projects.confirm', [$project, $pro]))->assertRedirect();

    $laneItem->refresh();
    expect($laneItem->quoted_amount)->toBe(270000)
        ->and($laneItem->deposit_amount)->toBe(81000)
        ->and($laneItem->status)->toBe(EventProjectItemStatus::AwaitingPayment);

    $this->actingAs($client)->post(route('client.projects.pay', [$project, $pro]), ['payment_method' => 'orange_money'])->assertRedirect();
    $proPayment = $project->payments()->where('provider_id', $pro->id)->firstOrFail();

    expect($laneItem->refresh()->status)->toBe(EventProjectItemStatus::Confirmed)
        ->and($proPayment->amount)->toBe(81000)
        ->and($proPayment->provider_payload['commission']['amount'])->toBe(27000);

    // 8. Each party sees the outcome in its own space, and nobody else can open it.
    $this->actingAs($client)->get(route('client.reservations.show', $booking))->assertOk()->assertSeeText('650 000');
    $this->actingAs($client)->get(route('client.projects.show', $project))->assertOk()->assertSeeText('Moussa Sound System');
    $this->actingAs($owner)->get(route('owner.bookings'))->assertOk()->assertSeeText($booking->reference);
    $this->actingAs($proUser)->get(route('service-provider.reservations'))->assertOk()->assertSeeText($project->reference);

    $otherClient = User::factory()->create(['role' => UserRole::Client, 'portal_roles' => [UserRole::Client->value]]);
    $this->actingAs($otherClient)->get(route('client.reservations.show', $booking))->assertNotFound();
    $this->actingAs($proUser)->get(route('owner.bookings.show', $booking))->assertForbidden();
    $this->actingAs($owner)->get(route('service-provider.reservations.show', $project))->assertForbidden();
});
