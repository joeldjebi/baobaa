<?php

namespace App\Http\Controllers;

use App\Enums\FulfillmentMethod;
use App\Enums\PaymentMethod;
use App\Enums\ProductMode;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\ProductInventoryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly ProductInventoryService $productInventoryService,
        private readonly CheckoutService $checkoutService,
    ) {}

    public function show(Request $request): View
    {
        $cartItems = $this->cartItems($request);

        return view('cart.show', [
            'providerGroups' => $cartItems
                ->groupBy(fn (CartItem $item): int => (int) $item->product->service_provider_profile_id)
                ->map(fn (Collection $items): array => [
                    'provider' => $items->first()->product->serviceProviderProfile,
                    'items' => $items,
                    'items_total' => (int) $items->sum(fn (CartItem $item): int => $item->lineTotal()),
                    'deposit_total' => (int) $items->sum(fn (CartItem $item): int => $item->depositTotal()),
                    'delivery_zones' => $items->first()->product->serviceProviderProfile->deliveryZones->where('is_active', true)->sortBy('city')->values(),
                ])
                ->values(),
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless(Product::query()->publiclyAvailable()->whereKey($product->id)->exists(), 404);

        $validated = $request->validate([
            'mode' => ['required', Rule::enum(ProductMode::class)],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'rental_starts_on' => ['nullable', 'required_if:mode,rental', 'date_format:Y-m-d', 'after_or_equal:today'],
            'rental_ends_on' => ['nullable', 'required_if:mode,rental', 'date_format:Y-m-d', 'after_or_equal:rental_starts_on'],
        ]);

        $mode = ProductMode::from($validated['mode']);

        if (! $product->supportsMode($mode)) {
            throw ValidationException::withMessages(['mode' => 'Cet article n’est pas proposé en '.mb_strtolower($mode->label()).'.']);
        }

        $available = $mode === ProductMode::Rental
            ? $this->productInventoryService->availableForRental($product, CarbonImmutable::parse($validated['rental_starts_on']), CarbonImmutable::parse($validated['rental_ends_on']))
            : $this->productInventoryService->availableForSale($product);

        if ($validated['quantity'] > $available) {
            throw ValidationException::withMessages(['quantity' => $available > 0
                ? 'Seulement '.$available.' unité'.($available > 1 ? 's' : '').' disponible'.($available > 1 ? 's' : '').' pour cette demande.'
                : 'Cet article n’est plus disponible pour cette période.']);
        }

        $request->user()->cartItems()->create([
            'product_id' => $product->id,
            'mode' => $mode,
            'quantity' => $validated['quantity'],
            'rental_starts_on' => $mode === ProductMode::Rental ? $validated['rental_starts_on'] : null,
            'rental_ends_on' => $mode === ProductMode::Rental ? $validated['rental_ends_on'] : null,
        ]);

        return redirect()->route('cart.show')->with('cart_status', $product->name.' a été ajouté au panier.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $cartItem->update($validated);

        return back()->with('cart_status', 'Quantité mise à jour.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $cartItem->delete();

        return back()->with('cart_status', 'Article retiré du panier.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'fulfillment' => ['nullable', 'array'],
            'fulfillment.*.method' => ['required', Rule::enum(FulfillmentMethod::class)],
            'fulfillment.*.city' => ['nullable', 'string', 'max:255'],
            'fulfillment.*.address' => ['nullable', 'string', 'max:255'],
            'fulfillment.*.date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
            'accept_terms' => ['accepted'],
        ]);

        $orders = $this->checkoutService->checkout(
            $request->user(),
            $validated['fulfillment'] ?? [],
            $validated['payment_method'],
            $validated['client_notes'] ?? null,
        );

        return redirect()
            ->route('client.orders')
            ->with('order_status', $orders->count() > 1
                ? 'Paiement test validé : '.$orders->count().' commandes ont été transmises aux prestataires.'
                : 'Paiement test validé : votre commande '.$orders->first()->reference.' a été transmise au prestataire.');
    }

    /**
     * @return Collection<int, CartItem>
     */
    private function cartItems(Request $request): Collection
    {
        return $request->user()->cartItems()
            ->with(['product.media', 'product.serviceProviderProfile.deliveryZones'])
            ->latest()
            ->get()
            ->filter(fn (CartItem $item): bool => $item->product !== null);
    }

    private function authorizeCartItem(Request $request, CartItem $cartItem): void
    {
        abort_unless((int) $cartItem->client_id === (int) $request->user()->id, 404);
    }
}
