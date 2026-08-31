<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\OwnerModuleTemplate;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('owner saves venue creation as a draft step by step', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $category = VenueCategory::factory()->create(['is_active' => true]);

    $this->actingAs($owner)
        ->post(route('owner.venues.draft.store'), [
            'step' => 'base',
            'name' => 'Salle étape premium',
            'venue_category_id' => $category->id,
            'city' => 'Abidjan',
            'district' => 'Cocody',
            'booking_mode' => 'request',
            'min_capacity' => 40,
            'max_capacity' => 240,
            'surface_area' => 420,
            'starting_price' => 250000,
            'reservation_amount' => 75000,
        ])
        ->assertRedirect();

    $venue = $ownerProfile->venues()->first();
    $module = OwnerModuleTemplate::query()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Sonorisation premium',
        'description' => 'Microphones et régie',
        'price' => 150000,
        'currency' => 'XOF',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect($venue)
        ->not->toBeNull()
        ->and($venue->name)->toBe('Salle étape premium')
        ->and($venue->status->value)->toBe('draft');

    $this->actingAs($owner)
        ->post(route('owner.venues.draft.store'), [
            'step' => 'modules',
            'venue_id' => $venue->id,
            'module_template_ids' => [$module->id],
        ])
        ->assertRedirect(route('owner.venues.edit', ['venue' => $venue, 'step' => 'localisation']));

    $this->assertDatabaseHas('venue_add_ons', [
        'venue_id' => $venue->id,
        'name' => 'Sonorisation premium',
        'price' => 150000,
    ]);
});

test('owner saves venue draft with ajax without full page redirect', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $category = VenueCategory::factory()->create(['is_active' => true]);

    $this->actingAs($owner)
        ->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->post(route('owner.venues.draft.store'), [
            'step' => 'base',
            'name' => 'Espace AJAX premium',
            'venue_category_id' => $category->id,
            'city' => 'Abidjan',
            'district' => 'Marcory',
            'booking_mode' => 'request',
            'min_capacity' => 30,
            'max_capacity' => 160,
            'surface_area' => 380,
            'starting_price' => 220000,
            'reservation_amount' => 80000,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Brouillon enregistré.')
        ->assertJsonPath('next_step', 'details');

    $venue = $ownerProfile->venues()->first();

    expect($venue)
        ->not->toBeNull()
        ->and($venue->name)->toBe('Espace AJAX premium');
});

test('owner can activate and disable a saved venue', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'status' => VenueStatus::Draft,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.venues.status', $venue), ['action' => 'activate'])
        ->assertRedirect();

    expect($venue->refresh()->status->value)->toBe('published');

    $this->actingAs($owner)
        ->post(route('owner.venues.status', $venue), ['action' => 'disable'])
        ->assertRedirect();

    expect($venue->refresh()->status->value)->toBe('suspended');
});

test('owner edit page shows saved venue data', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    VenueCategory::factory()->create(['is_active' => true]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salle confidentielle Plateau',
        'city' => 'Abidjan',
        'district' => 'Plateau',
        'min_capacity' => 25,
        'max_capacity' => 110,
        'starting_price' => 180000,
        'reservation_amount' => 60000,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.venues.edit', $venue))
        ->assertOk()
        ->assertSee('Salle confidentielle Plateau')
        ->assertSee('Abidjan')
        ->assertSee('Plateau')
        ->assertSee('180000')
        ->assertSee('60000');
});

test('owner venue draft form uses silent ajax submission', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    VenueCategory::factory()->create(['is_active' => true]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
    ]);

    $this->actingAs($owner)
        ->get(route('owner.venues.edit', ['venue' => $venue, 'step' => 'details']))
        ->assertOk()
        ->assertSee('data-owner-venue-draft-form', false)
        ->assertSee('data-no-global-loader', false)
        ->assertSeeText('Continuer');
});

test('owner can manage module library', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->post(route('owner.addons.store'), [
            'name' => 'Accueil VIP',
            'description' => 'Hôtesses et orientation des invités',
            'price' => 85000,
        ])
        ->assertRedirect();

    $module = $ownerProfile->moduleTemplates()->first();

    expect($module->name)->toBe('Accueil VIP')
        ->and($module->is_active)->toBeTrue();

    $this->actingAs($owner)
        ->post(route('owner.addons.toggle', $module))
        ->assertRedirect();

    expect($module->refresh()->is_active)->toBeFalse();

    $this->actingAs($owner)
        ->patch(route('owner.addons.update', $module), [
            'name' => 'Accueil protocolaire VIP',
            'description' => 'Accueil et orientation premium',
            'price' => 95000,
        ])
        ->assertRedirect();

    expect($module->refresh()->name)->toBe('Accueil protocolaire VIP')
        ->and($module->price)->toBe(95000);

    $this->actingAs($owner)
        ->delete(route('owner.addons.delete', $module))
        ->assertRedirect();

    $this->assertDatabaseMissing('owner_module_templates', [
        'id' => $module->id,
    ]);
});

test('owner configures payout account and billing preference', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->post(route('owner.settings.update'), [
            'business_name' => 'PEE Reversement Pro',
            'city' => 'Dakar',
            'country_code' => 'SN',
            'whatsapp_phone' => '+221770000000',
            'payout_provider' => 'wave',
            'payout_account_reference' => '+221770000000',
            'billing_preference' => 'hybrid',
        ])
        ->assertRedirect();

    $ownerProfile->refresh();

    expect($ownerProfile->business_name)->toBe('PEE Reversement Pro')
        ->and($ownerProfile->payout_provider)->toBe('wave')
        ->and($ownerProfile->billing_preference)->toBe('hybrid');
});

test('owner requests payout only after completed paid booking is older than forty eight hours', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $client = User::factory()->create(['role' => UserRole::Client]);
    $ownerProfile = OwnerProfile::factory()->create([
        'user_id' => $owner->id,
        'payout_provider' => 'mobile_money',
        'payout_account_reference' => '+2250101010101',
    ]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $ownerProfile->id,
        'status' => BookingStatus::Completed,
        'event_date' => now()->subDays(3)->toDateString(),
        'currency' => 'XOF',
    ]);
    Payment::factory()->create([
        'booking_id' => $booking->id,
        'payer_id' => $client->id,
        'status' => PaymentStatus::Succeeded,
        'amount' => 300000,
        'paid_at' => now()->subDays(3),
    ]);

    $this->actingAs($owner)
        ->post(route('owner.payouts.store', $booking))
        ->assertRedirect();

    $this->assertDatabaseHas('payouts', [
        'booking_id' => $booking->id,
        'gross_amount' => 300000,
        'commission_amount' => 30000,
        'net_amount' => 270000,
    ]);
});

test('owner manages booking status from reservation dashboard', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $client = User::factory()->create(['role' => UserRole::Client]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $booking = Booking::factory()->create([
        'client_id' => $client->id,
        'owner_profile_id' => $ownerProfile->id,
        'status' => BookingStatus::PendingOwner,
    ]);

    $this->actingAs($owner)
        ->post(route('owner.bookings.status', $booking), ['action' => 'confirm'])
        ->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed)
        ->and($booking->confirmed_at)->not->toBeNull();
});
