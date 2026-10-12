<?php

namespace App\Models;

use App\Enums\VenueStatus;
use Database\Factories\EventServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class EventService extends Model
{
    /** @use HasFactory<EventServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'service_provider_profile_id',
        'event_service_type_id',
        'name',
        'slug',
        'short_description',
        'description',
        'status',
        'country_code',
        'city',
        'district',
        'service_area',
        'pricing_unit',
        'currency',
        'starting_price',
        'deposit_amount',
        'attributes',
        'availability_notes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'attributes' => 'array',
            'availability_notes' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function serviceProviderProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderProfile::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventServiceType::class, 'event_service_type_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(EventServiceMedia::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->media->first()?->url ?? Venue::placeholderImageUrl());
    }

    public function availabilityHolds(): MorphMany
    {
        return $this->morphMany(AvailabilityHold::class, 'holdable');
    }

    /**
     * Published services from a verified and active provider: the only ones clients may see or book.
     */
    public function scopePubliclyAvailable(Builder $query): void
    {
        $query->where('status', VenueStatus::Published)
            ->whereHas('serviceProviderProfile', fn (Builder $query) => $query->publiclyListed());
    }

    protected static function booted(): void
    {
        static::creating(function (EventService $service): void {
            if (! $service->slug) {
                $baseSlug = Str::slug($service->name);
                $slug = $baseSlug;
                $counter = 2;

                while (static::query()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $service->slug = $slug;
            }
        });
    }
}
