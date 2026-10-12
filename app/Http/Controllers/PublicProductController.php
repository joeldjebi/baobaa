<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductInventoryService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Throwable;

class PublicProductController extends Controller
{
    public function __construct(private readonly ProductInventoryService $productInventoryService) {}

    public function index(Request $request): View
    {
        $mode = in_array($request->input('mode'), ['rental', 'sale'], true) ? $request->input('mode') : null;
        [$startsOn, $endsOn] = $this->rentalPeriod($request);

        $products = Product::query()
            ->publiclyAvailable()
            ->with(['category', 'media', 'serviceProviderProfile'])
            ->when($request->filled('q'), fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', '%'.$request->string('q')->trim().'%')
                ->orWhere('short_description', 'like', '%'.$request->string('q')->trim().'%')))
            ->when($request->filled('category'), fn (Builder $query) => $query->whereHas('category', fn (Builder $query) => $query->where('slug', $request->string('category'))))
            ->when($request->filled('city'), fn (Builder $query) => $query->where('city', 'like', '%'.$request->string('city')->trim().'%'))
            ->when($mode === 'rental', fn (Builder $query) => $query->where('is_rentable', true))
            ->when($mode === 'sale', fn (Builder $query) => $query->where('is_sellable', true))
            ->when($request->filled('max_price'), fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query->where('is_rentable', true)->where('rental_price_per_day', '<=', $request->integer('max_price')))
                ->orWhere(fn (Builder $query) => $query->where('is_sellable', true)->where('sale_price', '<=', $request->integer('max_price')))))
            ->where('stock_quantity', '>', 0)
            ->latest('published_at')
            ->get();

        if ($startsOn && $endsOn) {
            $products = $products->filter(fn (Product $product): bool => $product->is_rentable
                && $this->productInventoryService->availableForRental($product, $startsOn, $endsOn) > 0);
        }

        $page = max(1, $request->integer('page', 1));
        $perPage = 12;

        return view('products.index', [
            'products' => new LengthAwarePaginator(
                $products->forPage($page, $perPage)->values(),
                $products->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()],
            ),
            'categories' => ProductCategory::query()
                ->where('is_active', true)
                ->whereHas('products', fn (Builder $query) => $query->publiclyAvailable())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'cities' => Product::query()->publiclyAvailable()->whereNotNull('city')->distinct()->orderBy('city')->pluck('city'),
            'filters' => [
                'mode' => $mode,
                'start_date' => $startsOn?->toDateString(),
                'end_date' => $endsOn?->toDateString(),
            ],
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless(Product::query()->publiclyAvailable()->whereKey($product->id)->exists(), 404);

        $product->load(['category', 'media', 'serviceProviderProfile.deliveryZones' => fn ($query) => $query->where('is_active', true)->orderBy('city')]);

        return view('products.show', [
            'product' => $product,
            'availableForSale' => $this->productInventoryService->availableForSale($product),
            'similarProducts' => Product::query()
                ->publiclyAvailable()
                ->with('media')
                ->where('product_category_id', $product->product_category_id)
                ->whereKeyNot($product->id)
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function rentalPeriod(Request $request): array
    {
        try {
            $startsOn = $request->filled('start_date') ? CarbonImmutable::createFromFormat('!Y-m-d', (string) $request->input('start_date')) : null;
            $endsOn = $request->filled('end_date') ? CarbonImmutable::createFromFormat('!Y-m-d', (string) $request->input('end_date')) : $startsOn;
        } catch (Throwable) {
            return [null, null];
        }

        if (! $startsOn || ! $endsOn || $endsOn->lessThan($startsOn) || $endsOn->isBefore(today())) {
            return [null, null];
        }

        return [$startsOn->max(CarbonImmutable::today()), $endsOn];
    }
}
