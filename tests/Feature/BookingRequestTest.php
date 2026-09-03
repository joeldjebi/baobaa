<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\Booking;
use App\Models\OwnerDepositRule;
use App\Models\Payment;
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
