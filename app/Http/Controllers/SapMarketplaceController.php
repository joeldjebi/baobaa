<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Enums\VenueStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SapMarketplaceController extends Controller
{
    public function categories(): View
    {
        return view('dashboards.sap.product-categories', [
            ...app(SapDashboardController::class)->sharedMetrics(),
            'categories' => ProductCategory::query()->withCount('products')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:product_categories,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        ProductCategory::query()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('sap_status', 'Catégorie d’articles ajoutée.');
    }

    public function toggleCategory(ProductCategory $productCategory): RedirectResponse
    {
        $productCategory->update(['is_active' => ! $productCategory->is_active]);

        return back()->with('sap_status', 'Catégorie mise à jour.');
    }

    public function orders(Request $request): View
    {
        return view('dashboards.sap.orders', [
            ...app(SapDashboardController::class)->sharedMetrics(),
            'orders' => Order::query()
                ->with(['client', 'serviceProviderProfile'])
                ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
                ->when($request->boolean('retained_deposits'), fn (Builder $query) => $query->where('deposit_status', DepositStatus::Retained->value))
                ->when($request->filled('q'), fn (Builder $query) => $query->where(fn (Builder $query) => $query
                    ->where('reference', 'like', '%'.$request->string('q')->trim().'%')
                    ->orWhereHas('client', fn (Builder $query) => $query->where('name', 'like', '%'.$request->string('q')->trim().'%'))
                    ->orWhereHas('serviceProviderProfile', fn (Builder $query) => $query->where('business_name', 'like', '%'.$request->string('q')->trim().'%'))))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'products' => Product::query()
                ->with(['serviceProviderProfile', 'category'])
                ->latest()
                ->limit(30)
                ->get(),
        ]);
    }

    /**
     * Hide or restore a product published by a provider.
     */
    public function toggleProductSuspension(Product $product): RedirectResponse
    {
        $product->update([
            'status' => $product->status === VenueStatus::Suspended ? VenueStatus::Draft : VenueStatus::Suspended,
        ]);

        return back()->with('sap_status', $product->status === VenueStatus::Suspended ? 'Article masqué du catalogue.' : 'Article réactivé (en brouillon chez le prestataire).');
    }
}
