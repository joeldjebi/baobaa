<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductMedia;
use App\Models\ServiceProviderProfile;
use App\Services\ProductImageService;
use App\Services\ProductInventoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceProviderProductController extends Controller
{
    private const MaxImages = 8;

    public function __construct(private readonly ProductImageService $productImageService) {}

    public function index(Request $request): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.products', [
            ...app(ServiceProviderDashboardController::class)->metricsFor($profile),
            'profile' => $profile,
            'products' => $profile->products()
                ->with(['category', 'media'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), fn (Builder $query) => $query->where('name', 'like', '%'.$request->string('q')->toString().'%'))
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Product $product): Product => $product->setAttribute('available_for_sale', app(ProductInventoryService::class)->availableForSale($product))),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, null);
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeProduct($request, $product);

        return $this->form($request, $product->load('media'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $validated = $this->validated($request);

        $product = $profile->products()->create([
            ...$this->attributes($validated),
            'currency' => 'XOF',
            'published_at' => $validated['status'] === VenueStatus::Published->value ? now() : null,
        ]);

        $this->storeImages($request, $product);

        return redirect()->route('service-provider.products.edit', $product)->with('pse_status', 'Article enregistré.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        abort_if($product->status === VenueStatus::Suspended, 403, 'Cet article a été suspendu par BAOBAA.');

        $validated = $this->validated($request, $product);

        $product->update([
            ...$this->attributes($validated),
            'published_at' => $validated['status'] === VenueStatus::Published->value ? ($product->published_at ?? now()) : null,
        ]);

        $this->storeImages($request, $product);

        return back()->with('pse_status', 'Article mis à jour.');
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        abort_if($product->status === VenueStatus::Suspended, 403, 'Cet article a été suspendu par BAOBAA.');

        $publishing = $product->status !== VenueStatus::Published;

        $product->update([
            'status' => $publishing ? VenueStatus::Published : VenueStatus::Draft,
            'published_at' => $publishing ? now() : null,
        ]);

        return back()->with('pse_status', $publishing ? 'Article publié.' : 'Article retiré du catalogue.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        $product->media->each(fn (ProductMedia $media) => $this->productImageService->delete($media));
        $product->delete();

        return redirect()->route('service-provider.products')->with('pse_status', 'Article supprimé.');
    }

    public function destroyMedia(Request $request, Product $product, ProductMedia $productMedia): RedirectResponse
    {
        $this->authorizeProduct($request, $product);
        abort_unless((int) $productMedia->product_id === (int) $product->id, 404);

        $this->productImageService->delete($productMedia);

        return back()->with('pse_status', 'Photo retirée.');
    }

    private function form(Request $request, ?Product $product): View
    {
        $profile = $this->profile($request);

        return view('dashboards.service-provider.product-form', [
            ...app(ServiceProviderDashboardController::class)->metricsFor($profile),
            'profile' => $profile,
            'product' => $product,
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $remainingImageSlots = max(0, self::MaxImages - ($product?->media()->count() ?? 0));

        $validated = $request->validate([
            'product_category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:4000'],
            'status' => ['required', Rule::in([VenueStatus::Draft->value, VenueStatus::Published->value])],
            'is_rentable' => ['nullable', 'boolean'],
            'is_sellable' => ['nullable', 'boolean'],
            'rental_price_per_day' => ['nullable', 'integer', 'min:1', 'required_if_accepted:is_rentable'],
            'sale_price' => ['nullable', 'integer', 'min:1', 'required_if_accepted:is_sellable'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:100000'],
            'city' => ['required', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:'.$remainingImageSlots],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        if (! $request->boolean('is_rentable') && ! $request->boolean('is_sellable')) {
            throw ValidationException::withMessages(['is_rentable' => 'Choisissez au moins la location ou la vente.']);
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributes(array $validated): array
    {
        $isRentable = (bool) ($validated['is_rentable'] ?? false);
        $isSellable = (bool) ($validated['is_sellable'] ?? false);

        return [
            ...Arr::except($validated, ['images', 'is_rentable', 'is_sellable', 'rental_price_per_day', 'sale_price', 'deposit_amount']),
            'is_rentable' => $isRentable,
            'is_sellable' => $isSellable,
            'rental_price_per_day' => $isRentable ? $validated['rental_price_per_day'] : null,
            'sale_price' => $isSellable ? $validated['sale_price'] : null,
            'deposit_amount' => $isRentable ? (int) ($validated['deposit_amount'] ?? 0) : 0,
        ];
    }

    private function storeImages(Request $request, Product $product): void
    {
        foreach ($request->file('images', []) as $image) {
            $this->productImageService->store($product, $image);
        }
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        abort_unless((int) $product->service_provider_profile_id === (int) $this->profile($request)->id, 404);
    }

    private function profile(Request $request): ServiceProviderProfile
    {
        return $request->user()->serviceProviderProfile()->firstOrFail();
    }
}
