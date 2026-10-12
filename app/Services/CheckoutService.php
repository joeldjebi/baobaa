<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\FulfillmentMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductMode;
use App\Models\CartItem;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly ProductInventoryService $productInventoryService,
        private readonly PartnerConditionsService $partnerConditionsService,
    ) {}

    /**
     * Turn the client cart into one paid order per provider (test checkout until the real gateway is connected).
     *
     * @param  array<int, array{method: string, city?: ?string, address?: ?string, date?: ?string}>  $fulfillmentByProvider
     * @return Collection<int, Order>
     */
    public function checkout(User $client, array $fulfillmentByProvider, string $paymentMethod, ?string $clientNotes = null): Collection
    {
        return DB::transaction(function () use ($client, $fulfillmentByProvider, $paymentMethod, $clientNotes): Collection {
            $cartItems = CartItem::query()
                ->where('client_id', $client->id)
                ->with('product.serviceProviderProfile')
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Votre panier est vide.']);
            }

            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->publiclyAvailable()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->ensureCartIsOrderable($cartItems, $products);

            $orders = $cartItems
                ->groupBy(fn (CartItem $item): int => (int) $products->get($item->product_id)->service_provider_profile_id)
                ->map(fn (Collection $items, int $providerId): Order => $this->createOrder(
                    $client,
                    $items,
                    $products,
                    $providerId,
                    $fulfillmentByProvider[$providerId] ?? ['method' => FulfillmentMethod::Pickup->value],
                    $paymentMethod,
                    $clientNotes,
                ))
                ->values();

            CartItem::query()->where('client_id', $client->id)->delete();

            return $orders;
        });
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @param  Collection<int, Product>  $products
     */
    private function ensureCartIsOrderable(Collection $cartItems, Collection $products): void
    {
        $errors = [];
        $requestedSales = [];

        foreach ($cartItems as $cartItem) {
            $product = $products->get($cartItem->product_id);
            $name = $cartItem->product?->name ?? 'Un article';

            if (! $product || ! $product->supportsMode($cartItem->mode)) {
                $errors[] = $name.' n’est plus disponible à la commande.';

                continue;
            }

            if ($cartItem->mode === ProductMode::Sale) {
                $requestedSales[$product->id] = ($requestedSales[$product->id] ?? 0) + $cartItem->quantity;

                continue;
            }

            if (! $cartItem->rental_starts_on || ! $cartItem->rental_ends_on || $cartItem->rental_starts_on->isBefore(today())) {
                $errors[] = 'Les dates de location de '.$name.' doivent être à venir.';

                continue;
            }

            $requestedInCart = $cartItems
                ->filter(fn (CartItem $other): bool => $other->product_id === $cartItem->product_id
                    && $other->mode === ProductMode::Rental
                    && $other->rental_starts_on <= $cartItem->rental_ends_on
                    && $other->rental_ends_on >= $cartItem->rental_starts_on)
                ->sum('quantity');

            if ($requestedInCart > $this->productInventoryService->availableForRental($product, $cartItem->rental_starts_on, $cartItem->rental_ends_on)) {
                $errors[] = 'Stock insuffisant pour louer '.$name.' du '.$cartItem->rental_starts_on->format('d/m/Y').' au '.$cartItem->rental_ends_on->format('d/m/Y').'.';
            }
        }

        foreach ($requestedSales as $productId => $quantity) {
            $product = $products->get($productId);

            if ($quantity > $this->productInventoryService->availableForSale($product)) {
                $errors[] = 'Stock insuffisant pour acheter '.$quantity.' × '.$product->name.'.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['cart' => $errors]);
        }
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @param  Collection<int, Product>  $products
     * @param  array{method: string, city?: ?string, address?: ?string, date?: ?string}  $fulfillment
     */
    private function createOrder(User $client, Collection $items, Collection $products, int $providerId, array $fulfillment, string $paymentMethod, ?string $clientNotes): Order
    {
        $provider = $products->first(fn (Product $product): bool => $product->service_provider_profile_id === $providerId)->serviceProviderProfile;
        $method = FulfillmentMethod::tryFrom($fulfillment['method'] ?? '') ?? FulfillmentMethod::Pickup;
        $deliveryFee = 0;

        if ($method === FulfillmentMethod::Delivery) {
            $zone = DeliveryZone::query()
                ->where('service_provider_profile_id', $providerId)
                ->where('is_active', true)
                ->where('city', $fulfillment['city'] ?? '')
                ->first();

            if (! $zone || blank($fulfillment['address'] ?? null)) {
                throw ValidationException::withMessages([
                    "fulfillment.{$providerId}.city" => $provider->business_name.' ne livre pas dans cette ville ou l’adresse de livraison manque.',
                ]);
            }

            $deliveryFee = (int) $zone->fee;
        }

        $itemsTotal = (int) $items->sum(fn (CartItem $item): int => $item->lineTotal());
        $depositTotal = (int) $items->sum(fn (CartItem $item): int => $item->depositTotal());
        $commission = $this->partnerConditionsService->serviceProviderCommission($provider, $itemsTotal);

        $order = Order::query()->create([
            'reference' => $this->uniqueReference(),
            'client_id' => $client->id,
            'service_provider_profile_id' => $providerId,
            'status' => OrderStatus::Paid,
            'fulfillment_method' => $method,
            'delivery_city' => $method === FulfillmentMethod::Delivery ? $fulfillment['city'] : null,
            'delivery_address' => $method === FulfillmentMethod::Delivery ? $fulfillment['address'] : null,
            'delivery_date' => filled($fulfillment['date'] ?? null) ? CarbonImmutable::parse($fulfillment['date'])->toDateString() : null,
            'items_total' => $itemsTotal,
            'delivery_fee' => $deliveryFee,
            'deposit_total' => $depositTotal,
            'total_amount' => $itemsTotal + $deliveryFee + $depositTotal,
            'commission_amount' => $commission['amount'],
            'currency' => $products->first()->currency,
            'payment_method' => $paymentMethod,
            'deposit_status' => $depositTotal > 0 ? DepositStatus::Held : DepositStatus::None,
            'client_notes' => $clientNotes,
            'paid_at' => now(),
        ]);

        foreach ($items as $cartItem) {
            $product = $products->get($cartItem->product_id);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'mode' => $cartItem->mode,
                'quantity' => $cartItem->quantity,
                'unit_price' => $product->unitPriceFor($cartItem->mode),
                'rental_days' => $cartItem->mode === ProductMode::Rental ? $cartItem->rentalDays() : null,
                'rental_starts_on' => $cartItem->rental_starts_on,
                'rental_ends_on' => $cartItem->rental_ends_on,
                'line_total' => $cartItem->lineTotal(),
                'deposit_amount' => $cartItem->depositTotal(),
            ]);

            if ($cartItem->mode === ProductMode::Sale) {
                $product->decrement('stock_quantity', $cartItem->quantity);
            }
        }

        Payment::query()->create([
            'order_id' => $order->id,
            'provider_type' => 'service_provider_profile',
            'provider_id' => $providerId,
            'payer_id' => $client->id,
            'reference' => $this->uniquePaymentReference(),
            'provider' => $paymentMethod,
            'provider_reference' => 'TEST-'.Str::upper(Str::random(10)),
            'payment_method' => $paymentMethod,
            'status' => PaymentStatus::Succeeded,
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'provider_payload' => [
                'reason' => 'product_order',
                'mode' => 'test',
                'commission' => $commission,
                'deposit_total' => $depositTotal,
            ],
            'paid_at' => now(),
        ]);

        return $order;
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'CMD-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function uniquePaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
