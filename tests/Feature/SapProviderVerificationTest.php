<?php

use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\EventService;
use App\Models\PortalAccessRequest;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array{sap: User, providerUser: User, provider: ServiceProviderProfile, service: EventService}
 */
function pendingProviderWithService(): array
{
    $sap = User::factory()->create(['role' => UserRole::Sap, 'portal_roles' => [UserRole::Sap->value]]);
    $providerUser = User::factory()->create(['role' => UserRole::Client, 'portal_roles' => [UserRole::Client->value]]);
    $provider = ServiceProviderProfile::factory()->create([
        'user_id' => $providerUser->id,
        'business_name' => 'Studio Lumière Plateau',
        'verification_status' => VerificationStatus::Pending,
    ]);
    $service = EventService::factory()->create([
        'service_provider_profile_id' => $provider->id,
        'name' => 'Éclairage scène premium',
        'status' => VenueStatus::Published,
    ]);
    PortalAccessRequest::query()->create([
        'user_id' => $providerUser->id,
        'requested_role' => UserRole::ServiceProvider,
        'status' => 'pending',
        'business_name' => 'Studio Lumière Plateau',
    ]);

    return compact('sap', 'providerUser', 'provider', 'service');
}

test('sap sees a validate action for a pending provider', function () {
    ['sap' => $sap, 'provider' => $provider] = pendingProviderWithService();

    $this->actingAs($sap)
        ->get(route('sap.providers'))
        ->assertOk()
        ->assertSeeText('En attente')
        ->assertSee(route('sap.providers.verification', $provider), false);

    $this->actingAs($sap)
        ->get(route('sap.providers.show', $provider))
        ->assertOk()
        ->assertSeeText('Valider le prestataire');
});

test('sap validates a provider who becomes public and gets the provider portal', function () {
    ['sap' => $sap, 'providerUser' => $providerUser, 'provider' => $provider] = pendingProviderWithService();

    $this->get(route('service-provider-profiles.index'))->assertDontSeeText('Studio Lumière Plateau');

    $this->actingAs($sap)
        ->post(route('sap.providers.verification', $provider), ['decision' => 'approve'])
        ->assertRedirect()
        ->assertSessionHas('sap_status');

    $provider->refresh();

    expect($provider->verification_status)->toBe(VerificationStatus::Verified)
        ->and($provider->verified_by)->toBe($sap->id)
        ->and($providerUser->refresh()->hasPortal(UserRole::ServiceProvider))->toBeTrue()
        ->and(PortalAccessRequest::query()->where('user_id', $providerUser->id)->value('status'))->toBe('approved');

    auth()->logout();

    $this->get(route('service-provider-profiles.index'))->assertSeeText('Studio Lumière Plateau');
});

test('sap rejects a provider with a note and its services stay hidden', function () {
    ['sap' => $sap, 'providerUser' => $providerUser, 'provider' => $provider] = pendingProviderWithService();

    $this->actingAs($sap)
        ->post(route('sap.providers.verification', $provider), ['decision' => 'reject', 'decision_note' => 'Documents manquants'])
        ->assertRedirect();

    $request = PortalAccessRequest::query()->where('user_id', $providerUser->id)->firstOrFail();

    expect($provider->refresh()->verification_status)->toBe(VerificationStatus::Rejected)
        ->and($request->status)->toBe('rejected')
        ->and($request->decision_note)->toBe('Documents manquants')
        ->and(EventService::query()->publiclyAvailable()->count())->toBe(0);
});

test('only the sap can decide on a provider', function () {
    ['providerUser' => $providerUser, 'provider' => $provider] = pendingProviderWithService();

    $this->actingAs($providerUser)
        ->post(route('sap.providers.verification', $provider), ['decision' => 'approve'])
        ->assertForbidden();

    expect($provider->refresh()->verification_status)->toBe(VerificationStatus::Pending);
});
