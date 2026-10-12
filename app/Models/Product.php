<?php

namespace App\Models;

use App\Enums\ProductMode;
use App\Enums\VenueStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'service_provider_profile_id',
        'product_category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'status',
        'is_rentable',
        'is_sellable',
        'rental_price_per_day',
        'sale_price',
        'deposit_amount',
        'stock_quantity',
        'currency',
        'city',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'is_rentable' => 'boolean',
            'is_sellable' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function serviceProviderProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Published items of a verified and active provider: the only ones clients may see or order.
     */
    public function scopePubliclyAvailable(Builder $query): void
    {
        $query->where('status', VenueStatus::Published)
            ->whereHas('serviceProviderProfile', fn (Builder $query) => $query->publiclyListed());
    }

    public function supportsMode(ProductMode $mode): bool
    {
        return $mode === ProductMode::Rental
            ? $this->is_rentable && $this->rental_price_per_day !== null
            : $this->is_sellable && $this->sale_price !== null;
    }

    public function unitPriceFor(ProductMode $mode): int
    {
        return (int) ($mode === ProductMode::Rental ? $this->rental_price_per_day : $this->sale_price);
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->media->first()?->url ?? Venue::placeholderImageUrl());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if ($product->slug) {
                return;
            }

            $baseSlug = Str::slug($product->name) ?: 'article';
            $slug = $baseSlug;
            $counter = 2;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }

            $product->slug = $slug;
        });
    }
}
