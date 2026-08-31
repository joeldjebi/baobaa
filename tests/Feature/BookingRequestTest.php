<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\Booking;
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

test('client can save a booking request before payment is created', function () {
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
        ])
        ->assertRedirect();

    $booking = Booking::query()->first();

    expect($booking)
        ->not->toBeNull()
        ->and($booking->client_id)->toBe($client->id)
        ->and($booking->venue_id)->toBe($venue->id)
        ->and($booking->status)->toBe(BookingStatus::PendingPayment)
        ->and($booking->reservation_amount)->toBe(90000);

    $this->assertDatabaseCount('payments', 0);
});
