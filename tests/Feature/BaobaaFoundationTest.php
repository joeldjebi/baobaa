<?php

use App\Enums\UserRole;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\CommissionRule;
use App\Models\OwnerProfile;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('baobaa foundation seeds roles catalogs and monetization settings', function () {
    $this->seed();

    expect(User::query()->where('role', UserRole::Sap)->exists())->toBeTrue()
        ->and(VenueCategory::query()->count())->toBeGreaterThanOrEqual(8)
        ->and(Amenity::query()->count())->toBeGreaterThanOrEqual(10)
        ->and(SubscriptionPlan::query()->where('slug', 'premium')->exists())->toBeTrue()
        ->and(CommissionRule::query()->where('scope', 'global')->where('is_active', true)->exists())->toBeTrue()
        ->and(PlatformSetting::query()->where('key', 'security.require_owner_verification')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'pee.demo@baobaa.local')->where('role', UserRole::Owner)->exists())->toBeTrue()
        ->and(OwnerProfile::query()->where('slug', 'pee-demo-prestige')->exists())->toBeTrue();
});

test('venue booking relationships can be persisted', function () {
    $booking = Booking::factory()->create();

    expect($booking->venue)->toBeInstanceOf(Venue::class)
        ->and($booking->client)->toBeInstanceOf(User::class)
        ->and($booking->ownerProfile->venues)->not->toBeNull();
});
