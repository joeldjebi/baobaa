<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProformaInvoiceStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\BookingMessage;
use App\Models\OwnerDepositRule;
use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceItem;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest sees client login before saving a booking request', function () {
    $venue = Venue::factory()->create([
        'name' => 'Salle réservation sécurisée',
        'slug' => 'salle-reservation-securisee',
        'status' => VenueStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('venues.show', $venue->slug))
        ->assertOk()
        ->assertSeeText('Enregistrer ma réservation')
        ->assertSee(route('portal.login', ['portal' => 'client', 'redirect' => route('venues.show', $venue->slug)]))
        ->assertSeeText("Connexion client obligatoire avant l'enregistrement et le paiement.");

    $this->assertDatabaseCount('bookings', 0);
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
