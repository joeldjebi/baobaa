<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = User::query()->updateOrCreate(
            ['email' => 'client.demo@baobaa.local'],
            [
                'name' => 'Client Démo BAOBAA',
                'phone' => '+2250707070707',
                'role' => UserRole::Client,
                'portal_roles' => [UserRole::Client->value],
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $venue = Venue::query()
            ->where('slug', 'maison-bleue-signature-evenements-prives')
            ->orWhere('slug', 'salle-de-reception-lumineuse-pour-mariage')
            ->first();

        if (! $venue) {
            return;
        }

        $booking = Booking::query()->updateOrCreate(
            ['reference' => 'BAO-CLIENT-DEMO'],
            [
                'client_id' => $client->id,
                'owner_profile_id' => $venue->owner_profile_id,
                'venue_id' => $venue->id,
                'venue_rate_id' => $venue->rates()->where('is_default', true)->value('id'),
                'status' => BookingStatus::Confirmed,
                'booking_mode' => $venue->booking_mode,
                'event_type' => 'cocktail-corporate',
                'event_date' => now()->addDays(18)->toDateString(),
                'starts_at' => '16:00:00',
                'ends_at' => '22:00:00',
                'guests_count' => 140,
                'currency' => $venue->currency,
                'total_amount' => (int) $venue->starting_price,
                'reservation_amount' => (int) $venue->reservation_amount,
                'client_notes' => 'Cocktail de marque avec accueil VIP et installation audiovisuelle.',
                'confirmed_at' => now()->subDay(),
            ],
        );

        Payment::query()->updateOrCreate(
            ['reference' => 'PAY-CLIENT-DEMO'],
            [
                'booking_id' => $booking->id,
                'payer_id' => $client->id,
                'provider' => 'baobaa_pay',
                'provider_reference' => 'BAOBAA-DEMO-CLIENT-001',
                'payment_method' => 'mobile_money',
                'status' => PaymentStatus::Succeeded,
                'amount' => (int) $booking->reservation_amount,
                'currency' => $booking->currency,
                'provider_payload' => ['source' => 'seed'],
                'paid_at' => now()->subDay(),
            ],
        );
    }
}
