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
use App\Models\VenueMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
            'payment_methods' => ['baobaa_checkout', 'wave'],
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
        ->and($venue->status->value)->toBe('draft')
        ->and($venue->payment_methods)->toBe(['baobaa_checkout', 'wave']);

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
        ->assertJsonPath('message', 'Étape enregistrée avec succès. Vous pouvez continuer.')
        ->assertJsonPath('next_step', 'details');

    $venue = $ownerProfile->venues()->first();

    expect($venue)
        ->not->toBeNull()
        ->and($venue->name)->toBe('Espace AJAX premium');
});

test('owner updates an existing venue draft with ajax and receives success feedback', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $category = VenueCategory::factory()->create(['is_active' => true]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'venue_category_id' => $category->id,
        'name' => 'Ancienne salle',
        'city' => 'Abidjan',
        'min_capacity' => 20,
        'max_capacity' => 80,
        'starting_price' => 100000,
        'reservation_amount' => 30000,
    ]);

    $this->actingAs($owner)
        ->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->post(route('owner.venues.draft.store'), [
            'step' => 'base',
            'venue_id' => $venue->id,
            'name' => 'Salle éditée en AJAX',
            'venue_category_id' => $category->id,
            'city' => 'Yamoussoukro',
            'district' => 'Centre-ville',
            'booking_mode' => 'instant',
            'min_capacity' => 50,
            'max_capacity' => 220,
            'surface_area' => 460,
            'starting_price' => 260000,
            'reservation_amount' => 90000,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Étape enregistrée avec succès. Vous pouvez continuer.')
        ->assertJsonPath('next_step', 'details')
        ->assertJsonPath('next_url', route('owner.venues.edit', ['venue' => $venue, 'step' => 'details']));

    expect($venue->refresh())
        ->name->toBe('Salle éditée en AJAX')
        ->city->toBe('Yamoussoukro')
        ->booking_mode->toBe('instant')
        ->max_capacity->toBe(220);
});

test('owner uploads venue images and videos to wasabi while editing details step', function () {
    Storage::fake('wasabi');

    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create([
        'owner_profile_id' => $ownerProfile->id,
        'name' => 'Salle média premium',
        'short_description' => '',
        'description' => '',
    ]);

    $this->actingAs($owner)
        ->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->post(route('owner.venues.draft.store'), [
            'step' => 'details',
            'venue_id' => $venue->id,
            'short_description' => 'Une salle premium avec médias complets.',
            'description' => 'Description détaillée de la salle média premium.',
            'media_images' => [
                UploadedFile::fake()->image('salon.jpg', 1200, 800),
            ],
            'media_videos' => [
                UploadedFile::fake()->create('visite.mp4', 1024, 'video/mp4'),
            ],
        ])
        ->assertOk()
        ->assertJsonPath('next_step', 'inclusions');

    $image = $venue->media()->where('type', 'image')->first();
    $video = $venue->media()->where('type', 'video')->first();

    expect($image)->not->toBeNull()
        ->and($image->disk)->toBe('wasabi')
        ->and($image->is_primary)->toBeTrue()
        ->and($video)->not->toBeNull()
        ->and($video->disk)->toBe('wasabi')
        ->and($video->is_primary)->toBeFalse();

    Storage::disk('wasabi')->assertExists($image->path);
    Storage::disk('wasabi')->assertExists($video->path);
});

test('owner removes a venue media file from wasabi while editing their venue', function () {
    Storage::fake('wasabi');

    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $ownerProfile = OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $venue = Venue::factory()->create(['owner_profile_id' => $ownerProfile->id]);

    Storage::disk('wasabi')->put('venues/'.$venue->id.'/images/primary.jpg', 'primary');
    Storage::disk('wasabi')->put('venues/'.$venue->id.'/images/second.jpg', 'second');

    $primaryMedia = VenueMedia::factory()->create([
        'venue_id' => $venue->id,
        'disk' => 'wasabi',
        'path' => 'venues/'.$venue->id.'/images/primary.jpg',
        'type' => 'image',
        'is_primary' => true,
        'sort_order' => 1,
    ]);
    $secondMedia = VenueMedia::factory()->create([
        'venue_id' => $venue->id,
        'disk' => 'wasabi',
        'path' => 'venues/'.$venue->id.'/images/second.jpg',
        'type' => 'image',
        'is_primary' => false,
        'sort_order' => 2,
    ]);

    $this->actingAs($owner)
        ->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->delete(route('owner.venues.media.destroy', ['venue' => $venue, 'venueMedia' => $primaryMedia]))
        ->assertOk()
        ->assertJsonPath('message', 'Média retiré avec succès.')
        ->assertJsonPath('media_id', $primaryMedia->id);

    Storage::disk('wasabi')->assertMissing('venues/'.$venue->id.'/images/primary.jpg');
    Storage::disk('wasabi')->assertExists('venues/'.$venue->id.'/images/second.jpg');

    $this->assertDatabaseMissing('venue_media', [
        'id' => $primaryMedia->id,
    ]);

    expect($secondMedia->refresh()->is_primary)->toBeTrue();
});

test('owner cannot remove media from another owner venue', function () {
    Storage::fake('wasabi');

    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $otherOwner = User::factory()->create(['role' => UserRole::Owner]);
    OwnerProfile::factory()->create(['user_id' => $owner->id]);
    $otherOwnerProfile = OwnerProfile::factory()->create(['user_id' => $otherOwner->id]);
    $otherVenue = Venue::factory()->create(['owner_profile_id' => $otherOwnerProfile->id]);
    $media = VenueMedia::factory()->create([
        'venue_id' => $otherVenue->id,
        'disk' => 'wasabi',
        'path' => 'venues/'.$otherVenue->id.'/images/private.jpg',
    ]);

    $this->actingAs($owner)
        ->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->delete(route('owner.venues.media.destroy', ['venue' => $otherVenue, 'venueMedia' => $media]))
        ->assertNotFound();

    $this->assertDatabaseHas('venue_media', [
        'id' => $media->id,
    ]);
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
        ->assertSeeText('Acompte client')
        ->assertSeeText('Paiement sécurisé BAOBAA');
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
    Storage::fake('wasabi');

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
            'logo' => UploadedFile::fake()->image('logo.png', 600, 600),
        ])
        ->assertRedirect();

    $ownerProfile->refresh();

    expect($ownerProfile->business_name)->toBe('PEE Reversement Pro')
        ->and($ownerProfile->payout_provider)->toBe('wave')
        ->and($ownerProfile->billing_preference)->toBe('hybrid')
        ->and($ownerProfile->logo_disk)->toBe('wasabi')
        ->and($ownerProfile->logo_path)->not->toBeNull();

    Storage::disk('wasabi')->assertExists($ownerProfile->logo_path);
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
