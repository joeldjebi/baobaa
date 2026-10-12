<?php

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductMode;
use App\Enums\UserRole;
use App\Enums\VenueStatus;
use App\Models\CartItem;
use App\Models\CommissionRule;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function marketplaceClient(): User
{
    return User::factory()->create(['role' => UserRole::Client, 'portal_roles' => [UserRole::Client->value]]);
}

/**
 * @return array{user: User, profile: ServiceProviderProfile}
 */
function marketplaceProvider(string $name = 'Location Prestige'): array
{
    $user = User::factory()->create(['role' => UserRole::ServiceProvider, 'portal_roles' => [UserRole::ServiceProvider->value]]);

    return ['user' => $user, 'profile' => ServiceProviderProfile::factory()->verified()->create(['user_id' => $user->id, 'business_name' => $name, 'city' => 'Abidjan'])];
}

function rentalDates(int $fromDays = 10, int $toDays = 12): array
{
    return [now()->addDays($fromDays)->toDateString(), now()->addDays($toDays)->toDateString()];
}

function checkoutPayload(array $fulfillment = []): array
{
    return ['payment_method' => 'wave', 'accept_terms' => '1', 'fulfillment' => $fulfillment];
}

test('provider publishes a product with photos, rental and sale prices', function () {
    Storage::fake('wasabi');
    ['user' => $providerUser, 'profile' => $profile] = marketplaceProvider();
    $category = ProductCategory::factory()->create(['name' => 'Mobilier']);

    $this->actingAs($providerUser)
        ->post(route('service-provider.products.store'), [
            'product_category_id' => $category->id,
            'name' => 'Chaise Napoléon dorée',
            'status' => 'published',
            'is_rentable' => '1',
            'is_sellable' => '1',
            'rental_price_per_day' => 1500,
            'sale_price' => 35000,
            'deposit_amount' => 5000,
            'stock_quantity' => 200,
            'city' => 'Abidjan',
            'images' => [UploadedFile::fake()->image('chaise.jpg')],
        ])
        ->assertRedirect();

    $product = Product::query()->firstOrFail();

    expect($product->service_provider_profile_id)->toBe($profile->id)
        ->and($product->status)->toBe(VenueStatus::Published)
        ->and($product->sale_price)->toBe(35000)
        ->and($product->media)->toHaveCount(1);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSeeText('Chaise Napoléon dorée')
        ->assertSeeText('Location 1 500 XOF/jour')
        ->assertSeeText('Achat 35 000 XOF');

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSeeText('Caution remboursable : 5 000 XOF par unité');
});

test('provider must offer the product for rent or for sale', function () {
    ['user' => $providerUser] = marketplaceProvider();
    $category = ProductCategory::factory()->create();

    $this->actingAs($providerUser)
        ->post(route('service-provider.products.store'), [
            'product_category_id' => $category->id,
            'name' => 'Article sans mode',
            'status' => 'draft',
            'is_rentable' => '0',
            'is_sellable' => '0',
            'stock_quantity' => 1,
            'city' => 'Abidjan',
        ])
        ->assertSessionHasErrors('is_rentable');

    $this->actingAs($providerUser)
        ->post(route('service-provider.products.store'), [
            'product_category_id' => $category->id,
            'name' => 'Location sans prix',
            'status' => 'draft',
            'is_rentable' => '1',
            'is_sellable' => '0',
            'stock_quantity' => 1,
            'city' => 'Abidjan',
        ])
        ->assertSessionHasErrors('rental_price_per_day');

    expect(Product::query()->count())->toBe(0);
});

test('catalog hides drafts, suspended items and items of unverified providers', function () {
    Product::factory()->create(['name' => 'Enceinte visible']);
    Product::factory()->create(['name' => 'Enceinte brouillon', 'status' => VenueStatus::Draft]);
    Product::factory()->create(['name' => 'Enceinte suspendue', 'status' => VenueStatus::Suspended]);
    Product::factory()->create([
        'name' => 'Enceinte non vérifiée',
        'service_provider_profile_id' => ServiceProviderProfile::factory()->create()->id,
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSeeText('Enceinte visible')
        ->assertDontSeeText('Enceinte brouillon')
        ->assertDontSeeText('Enceinte suspendue')
        ->assertDontSeeText('Enceinte non vérifiée');

    $this->get(route('products.show', Product::query()->where('name', 'Enceinte brouillon')->first()))->assertNotFound();
});

test('catalog date filter hides items fully rented on the requested period', function () {
    $fullyRented = Product::factory()->create(['name' => 'Tente déjà louée', 'stock_quantity' => 2]);
    Product::factory()->create(['name' => 'Tente disponible', 'stock_quantity' => 2]);
    [$startsOn, $endsOn] = rentalDates();
    $order = Order::factory()->create(['service_provider_profile_id' => $fullyRented->service_provider_profile_id]);
    $order->items()->create([
        'product_id' => $fullyRented->id,
        'product_name' => $fullyRented->name,
        'mode' => ProductMode::Rental,
        'quantity' => 2,
        'unit_price' => 1000,
        'rental_days' => 3,
        'rental_starts_on' => $startsOn,
        'rental_ends_on' => $endsOn,
        'line_total' => 6000,
    ]);

    $this->get(route('products.index', ['mode' => 'rental', 'start_date' => $startsOn, 'end_date' => $endsOn]))
        ->assertOk()
        ->assertSeeText('Tente disponible')
        ->assertDontSeeText('Tente déjà louée');

    $this->get(route('products.index', ['mode' => 'rental', 'start_date' => now()->addDays(20)->toDateString()]))
        ->assertSeeText('Tente déjà louée');
});

test('client cannot add more units than available for the rental period', function () {
    $client = marketplaceClient();
    $product = Product::factory()->create(['stock_quantity' => 3]);
    [$startsOn, $endsOn] = rentalDates();

    $this->actingAs($client)
        ->post(route('cart.store', $product), ['mode' => 'rental', 'quantity' => 4, 'rental_starts_on' => $startsOn, 'rental_ends_on' => $endsOn])
        ->assertSessionHasErrors(['quantity' => 'Seulement 3 unités disponibles pour cette demande.']);

    $this->actingAs($client)
        ->post(route('cart.store', $product), ['mode' => 'rental', 'quantity' => 1, 'rental_starts_on' => now()->subDay()->toDateString(), 'rental_ends_on' => $endsOn])
        ->assertSessionHasErrors('rental_starts_on');

    $this->actingAs($client)
        ->post(route('cart.store', $product), ['mode' => 'sale', 'quantity' => 1])
        ->assertSessionHasErrors('mode');

    expect(CartItem::query()->count())->toBe(0);
});

test('checkout creates one paid order per provider with delivery, deposit and commission', function () {
    $client = marketplaceClient();
    ['profile' => $firstProvider] = marketplaceProvider('Mobilier Royal');
    ['profile' => $secondProvider] = marketplaceProvider('Son Lumière Pro');
    CommissionRule::factory()->create(['scope' => 'service_provider', 'percentage_rate' => 10]);
    DeliveryZone::factory()->create(['service_provider_profile_id' => $firstProvider->id, 'city' => 'Abidjan', 'fee' => 15000]);
    $chairs = Product::factory()->create(['service_provider_profile_id' => $firstProvider->id, 'rental_price_per_day' => 1000, 'deposit_amount' => 2000, 'stock_quantity' => 100]);
    $speaker = Product::factory()->create(['service_provider_profile_id' => $secondProvider->id, 'is_rentable' => false, 'rental_price_per_day' => null, 'is_sellable' => true, 'sale_price' => 80000, 'stock_quantity' => 5]);
    [$startsOn, $endsOn] = rentalDates(10, 11);

    $this->actingAs($client)->post(route('cart.store', $chairs), ['mode' => 'rental', 'quantity' => 50, 'rental_starts_on' => $startsOn, 'rental_ends_on' => $endsOn])->assertRedirect(route('cart.show'));
    $this->actingAs($client)->post(route('cart.store', $speaker), ['mode' => 'sale', 'quantity' => 2])->assertRedirect(route('cart.show'));

    $this->actingAs($client)
        ->get(route('cart.show'))
        ->assertOk()
        ->assertSeeText('Mobilier Royal')
        ->assertSeeText('Son Lumière Pro')
        ->assertSeeText('Livraison');

    $this->actingAs($client)
        ->post(route('cart.checkout'), checkoutPayload([
            $firstProvider->id => ['method' => 'delivery', 'city' => 'Abidjan', 'address' => 'Cocody, rue des Jardins', 'date' => $startsOn],
            $secondProvider->id => ['method' => 'pickup'],
        ]))
        ->assertRedirect(route('client.orders'));

    $chairsOrder = Order::query()->where('service_provider_profile_id', $firstProvider->id)->firstOrFail();
    $speakerOrder = Order::query()->where('service_provider_profile_id', $secondProvider->id)->firstOrFail();

    expect($chairsOrder->items_total)->toBe(100000)
        ->and($chairsOrder->delivery_fee)->toBe(15000)
        ->and($chairsOrder->deposit_total)->toBe(100000)
        ->and($chairsOrder->total_amount)->toBe(215000)
        ->and($chairsOrder->commission_amount)->toBe(10000)
        ->and($chairsOrder->deposit_status)->toBe(DepositStatus::Held)
        ->and($chairsOrder->status)->toBe(OrderStatus::Paid)
        ->and($chairsOrder->payments()->first()->status)->toBe(PaymentStatus::Succeeded)
        ->and($chairsOrder->payments()->first()->amount)->toBe(215000)
        ->and($speakerOrder->total_amount)->toBe(160000)
        ->and($speakerOrder->deposit_status)->toBe(DepositStatus::None)
        ->and($speaker->refresh()->stock_quantity)->toBe(3)
        ->and(CartItem::query()->count())->toBe(0);

    $this->actingAs($client)
        ->get(route('client.orders.show', $chairsOrder))
        ->assertOk()
        ->assertSeeText('Caution (caution bloquée)')
        ->assertSeeText('Cocody, rue des Jardins');
});

test('checkout refuses a city the provider does not deliver and requires the terms', function () {
    $client = marketplaceClient();
    $product = Product::factory()->create();
    [$startsOn, $endsOn] = rentalDates();
    CartItem::factory()->create(['client_id' => $client->id, 'product_id' => $product->id, 'rental_starts_on' => $startsOn, 'rental_ends_on' => $endsOn]);

    $this->actingAs($client)
        ->post(route('cart.checkout'), ['payment_method' => 'wave', 'fulfillment' => []])
        ->assertSessionHasErrors('accept_terms');

    $this->actingAs($client)
        ->post(route('cart.checkout'), checkoutPayload([
            $product->service_provider_profile_id => ['method' => 'delivery', 'city' => 'Bouaké', 'address' => 'Centre'],
        ]))
        ->assertSessionHasErrors("fulfillment.{$product->service_provider_profile_id}.city");

    expect(Order::query()->count())->toBe(0)
        ->and(CartItem::query()->count())->toBe(1);
});

test('checkout rejects a cart when another client rented the last units in the meantime', function () {
    $firstClient = marketplaceClient();
    $secondClient = marketplaceClient();
    $product = Product::factory()->create(['stock_quantity' => 1]);
    [$startsOn, $endsOn] = rentalDates();

    foreach ([$firstClient, $secondClient] as $client) {
        $this->actingAs($client)
            ->post(route('cart.store', $product), ['mode' => 'rental', 'quantity' => 1, 'rental_starts_on' => $startsOn, 'rental_ends_on' => $endsOn])
            ->assertRedirect(route('cart.show'));
    }

    $this->actingAs($firstClient)->post(route('cart.checkout'), checkoutPayload())->assertRedirect(route('client.orders'));

    $this->actingAs($secondClient)
        ->post(route('cart.checkout'), checkoutPayload())
        ->assertSessionHasErrors('cart');

    expect(Order::query()->count())->toBe(1);
});

test('provider moves a rental order to completion after settling the deposit', function () {
    ['user' => $providerUser, 'profile' => $profile] = marketplaceProvider();
    $order = Order::factory()->create([
        'service_provider_profile_id' => $profile->id,
        'deposit_total' => 50000,
        'deposit_status' => DepositStatus::Held,
    ]);
    $order->items()->create(['product_name' => 'Tente 10x10', 'mode' => ProductMode::Rental, 'quantity' => 1, 'unit_price' => 20000, 'rental_days' => 1, 'rental_starts_on' => now()->addDays(3), 'rental_ends_on' => now()->addDays(3), 'line_total' => 20000, 'deposit_amount' => 50000]);

    $this->actingAs($providerUser)->get(route('service-provider.orders.show', $order))->assertOk()->assertSeeText('Passer à : En préparation');

    foreach (['preparing', 'delivered', 'returned'] as $status) {
        $this->actingAs($providerUser)->post(route('service-provider.orders.status', $order), ['status' => $status])->assertSessionHasNoErrors();
    }

    $this->actingAs($providerUser)
        ->post(route('service-provider.orders.status', $order), ['status' => 'completed'])
        ->assertSessionHasErrors('status');

    $this->actingAs($providerUser)
        ->post(route('service-provider.orders.deposit', $order), ['retained_amount' => 10000])
        ->assertSessionHasErrors('reason');

    $this->actingAs($providerUser)
        ->post(route('service-provider.orders.deposit', $order), ['retained_amount' => 10000, 'reason' => 'Toile déchirée sur 1 m'])
        ->assertSessionHasNoErrors();

    $this->actingAs($providerUser)->post(route('service-provider.orders.status', $order), ['status' => 'completed'])->assertSessionHasNoErrors();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->deposit_status)->toBe(DepositStatus::Retained)
        ->and($order->deposit_retained_amount)->toBe(10000);

    $sap = User::factory()->create(['role' => UserRole::Sap, 'portal_roles' => [UserRole::Sap->value]]);

    $this->actingAs($sap)
        ->get(route('sap.orders', ['retained_deposits' => 1]))
        ->assertOk()
        ->assertSeeText($order->reference)
        ->assertSeeText('Toile déchirée sur 1 m');
});

test('cancelling a sale order puts the units back in stock and releases the deposit', function () {
    ['user' => $providerUser, 'profile' => $profile] = marketplaceProvider();
    $product = Product::factory()->create(['service_provider_profile_id' => $profile->id, 'is_sellable' => true, 'sale_price' => 5000, 'stock_quantity' => 8]);
    $order = Order::factory()->create(['service_provider_profile_id' => $profile->id]);
    $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'mode' => ProductMode::Sale, 'quantity' => 2, 'unit_price' => 5000, 'line_total' => 10000]);

    $this->actingAs($providerUser)->post(route('service-provider.orders.status', $order), ['status' => 'cancelled'])->assertSessionHasNoErrors();

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($product->refresh()->stock_quantity)->toBe(10);

    $this->actingAs($providerUser)->post(route('service-provider.orders.status', $order), ['status' => 'cancelled'])->assertSessionHasErrors('status');
});

test('orders, cart items and products are protected from other users', function () {
    $client = marketplaceClient();
    $otherClient = marketplaceClient();
    ['user' => $providerUser] = marketplaceProvider();
    ['user' => $otherProviderUser, 'profile' => $otherProfile] = marketplaceProvider('Autre prestataire');
    $order = Order::factory()->create(['client_id' => $client->id, 'service_provider_profile_id' => $otherProfile->id]);
    $cartItem = CartItem::factory()->create(['client_id' => $client->id]);
    $otherProduct = Product::factory()->create(['service_provider_profile_id' => $otherProfile->id]);

    $this->actingAs($otherClient)->get(route('client.orders.show', $order))->assertNotFound();
    $this->actingAs($otherClient)->delete(route('cart.destroy', $cartItem))->assertNotFound();
    $this->actingAs($providerUser)->get(route('service-provider.orders.show', $order))->assertNotFound();
    $this->actingAs($providerUser)->get(route('service-provider.products.edit', $otherProduct))->assertNotFound();
    $this->actingAs($providerUser)->post(route('service-provider.products.toggle', $otherProduct))->assertNotFound();
    $this->actingAs($otherProviderUser)->get(route('service-provider.orders.show', $order))->assertOk();

    $this->assertModelExists($cartItem);
});

test('sap manages product categories and can hide a product from the catalog', function () {
    $sap = User::factory()->create(['role' => UserRole::Sap, 'portal_roles' => [UserRole::Sap->value]]);
    $product = Product::factory()->create(['name' => 'Projecteur signalé']);

    $this->actingAs($sap)
        ->post(route('sap.product-categories.store'), ['name' => 'Groupes électrogènes'])
        ->assertRedirect();

    expect(ProductCategory::query()->where('slug', 'groupes-electrogenes')->exists())->toBeTrue();

    $this->actingAs($sap)->post(route('sap.products.toggle', $product))->assertRedirect();

    expect($product->refresh()->status)->toBe(VenueStatus::Suspended);
    $this->get(route('products.index'))->assertDontSeeText('Projecteur signalé');
});
