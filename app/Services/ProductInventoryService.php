<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductMode;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class ProductInventoryService
{
    /**
     * Units already rented out on at least one day of the period.
     */
    public function rentedQuantity(Product $product, CarbonInterface $startsOn, CarbonInterface $endsOn): int
    {
        return (int) OrderItem::query()
            ->where('product_id', $product->id)
            ->where('mode', ProductMode::Rental->value)
            ->whereDate('rental_starts_on', '<=', $endsOn->toDateString())
            ->whereDate('rental_ends_on', '>=', $startsOn->toDateString())
            ->whereHas('order', fn (Builder $query) => $query->whereIn('status', OrderStatus::reservingStock()))
            ->sum('quantity');
    }

    public function availableForRental(Product $product, CarbonInterface $startsOn, CarbonInterface $endsOn): int
    {
        return max(0, (int) $product->stock_quantity - $this->rentedQuantity($product, $startsOn, $endsOn));
    }

    /**
     * Units that can be sold without breaking current or upcoming rentals.
     */
    public function availableForSale(Product $product): int
    {
        $committedRentals = (int) OrderItem::query()
            ->where('product_id', $product->id)
            ->where('mode', ProductMode::Rental->value)
            ->whereDate('rental_ends_on', '>=', now()->toDateString())
            ->whereHas('order', fn (Builder $query) => $query->whereIn('status', OrderStatus::reservingStock()))
            ->sum('quantity');

        return max(0, (int) $product->stock_quantity - $committedRentals);
    }
}
