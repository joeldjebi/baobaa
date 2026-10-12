<?php

namespace App\Models;

use App\Enums\PriceUnit;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Services\MediaEmbedService;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_profile_id',
        'venue_category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'highlights',
        'included_items',
        'space_details',
        'house_rules',
        'status',
        'verification_status',
        'booking_mode',
        'country_code',
        'city',
        'district',
        'address',
        'latitude',
        'longitude',
        'location_details',
        'min_capacity',
        'max_capacity',
        'surface_area',
        'currency',
        'starting_price',
        'price_unit',
        'minimum_duration_hours',
        'reservation_amount',
        'payment_methods',
        'availability_notes',
        'opens_at',
        'closes_at',
        'video_urls',
        'virtual_tour_url',
        'average_rating',
        'reviews_count',
        'published_at',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'verification_status' => VerificationStatus::class,
            'highlights' => 'array',
            'included_items' => 'array',
            'space_details' => 'array',
            'house_rules' => 'array',
            'location_details' => 'array',
            'payment_methods' => 'array',
            'availability_notes' => 'array',
            'price_unit' => PriceUnit::class,
            'video_urls' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'average_rating' => 'decimal:2',
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VenueCategory::class, 'venue_category_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class)->withTimestamps();
    }

    public function media(): HasMany
    {
        return $this->hasMany(VenueMedia::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(VenueAvailability::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(VenueRate::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(VenueConfiguration::class);
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(VenueAddOn::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(VenuePolicy::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(VenueFaq::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(VenueReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(VenueReview::class)->where('status', 'approved');
    }

    public function sponsorshipCampaigns(): HasMany
    {
        return $this->hasMany(SponsorshipCampaign::class);
    }

    /**
     * Limit the query to venues visible on the public catalog.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', VenueStatus::Published);
    }

    /**
     * Load the public rating computed only from approved client reviews.
     */
    public function scopeWithPublicRating(Builder $query): void
    {
        $query->withCount(['approvedReviews as public_reviews_count'])
            ->withAvg('approvedReviews as public_rating', 'rating');
    }

    /**
     * Public images ordered with the primary image first, excluding videos and rejected media.
     *
     * @return Collection<int, VenueMedia>
     */
    public function publicImages(): Collection
    {
        return $this->media
            ->where('type', 'image')
            ->reject(fn (VenueMedia $media): bool => $media->moderation_status === 'rejected')
            ->sortBy([
                fn (VenueMedia $first, VenueMedia $second): int => (int) $second->is_primary <=> (int) $first->is_primary,
                fn (VenueMedia $first, VenueMedia $second): int => $first->sort_order <=> $second->sort_order,
            ])
            ->values();
    }

    /**
     * @return array<int, string>
     */
    public function publicImageUrls(): array
    {
        return $this->publicImages()
            ->map(fn (VenueMedia $media): string => $media->signed_url)
            ->filter()
            ->values()
            ->all();
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->publicImages()->first()?->signed_url ?? self::placeholderImageUrl());
    }

    /**
     * Public starting price with its billing unit, e.g. "350 000 XOF par journée".
     */
    public function priceLabel(): string
    {
        if (! $this->starting_price) {
            return 'Prix sur demande';
        }

        return number_format((int) $this->starting_price, 0, ',', ' ').' '.$this->currency.' '.($this->price_unit ?? PriceUnit::Event)->label();
    }

    public function minimumDurationLabel(): ?string
    {
        if (! $this->minimum_duration_hours) {
            return null;
        }

        return 'Location minimum : '.$this->minimum_duration_hours.' h';
    }

    public function openingHoursLabel(): ?string
    {
        if (! $this->opens_at || ! $this->closes_at) {
            return null;
        }

        return str_replace(':', ' h ', substr((string) $this->opens_at, 0, 5)).' – '.str_replace(':', ' h ', substr((string) $this->closes_at, 0, 5));
    }

    /**
     * OpenStreetMap embed centred on a rounded position (about 1 km) so the exact address stays private.
     */
    public function approximateMapEmbedUrl(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $latitude = round((float) $this->latitude, 2);
        $longitude = round((float) $this->longitude, 2);
        $delta = 0.012;

        return 'https://www.openstreetmap.org/export/embed.html?'.http_build_query([
            'bbox' => implode(',', [$longitude - $delta, $latitude - $delta, $longitude + $delta, $latitude + $delta]),
            'layer' => 'mapnik',
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function videoEmbedUrls(): array
    {
        $mediaEmbedService = app(MediaEmbedService::class);

        return collect($this->video_urls ?? [])
            ->map(fn (?string $url): ?string => $mediaEmbedService->videoEmbedUrl($url))
            ->filter()
            ->values()
            ->all();
    }

    public function virtualTourEmbedUrl(): ?string
    {
        return app(MediaEmbedService::class)->virtualTourEmbedUrl($this->virtual_tour_url);
    }

    public static function placeholderImageUrl(): string
    {
        return asset('images/venue-placeholder.svg');
    }

    public function availabilityHolds(): MorphMany
    {
        return $this->morphMany(AvailabilityHold::class, 'holdable');
    }
}
