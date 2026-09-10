<?php

use App\Enums\UserRole;
use App\Models\ManagedLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sap can manage active public locations', function () {
    $sap = User::factory()->create([
        'role' => UserRole::Sap,
        'portal_roles' => [UserRole::Sap->value],
    ]);
    $country = ManagedLocation::factory()->country()->create([
        'name' => 'Côte d’Ivoire',
        'slug' => 'cote-divoire',
        'code' => 'CI',
        'country_code' => 'CI',
    ]);

    $this->actingAs($sap)
        ->get(route('sap.locations'))
        ->assertOk()
        ->assertSeeText('Pays, villes et communes')
        ->assertSeeText('Côte d’Ivoire');

    $this->actingAs($sap)
        ->post(route('sap.locations.store'), [
            'type' => 'city',
            'name' => 'Abidjan',
            'country_id' => $country->id,
            'sort_order' => 10,
        ])
        ->assertRedirect()
        ->assertSessionHas('sap_status');

    $city = ManagedLocation::query()->where('type', ManagedLocation::TypeCity)->where('name', 'Abidjan')->firstOrFail();

    expect($city->parent_id)->toBe($country->id)
        ->and($city->country_code)->toBe('CI')
        ->and($city->is_active)->toBeTrue();

    $this->actingAs($sap)
        ->post(route('sap.locations.toggle', $city))
        ->assertRedirect()
        ->assertSessionHas('sap_status');

    expect($city->refresh()->is_active)->toBeFalse();
});
