<?php

namespace App\Models;

use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'reservation_amount',
        'availability_notes',
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
            'availability_notes' => 'array',
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

    public function sponsorshipCampaigns(): HasMany
    {
        return $this->hasMany(SponsorshipCampaign::class);
    }
}
