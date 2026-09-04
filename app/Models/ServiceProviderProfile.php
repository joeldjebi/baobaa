<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Services\PartnerLogoService;
use Database\Factories\ServiceProviderProfileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceProviderProfile extends Model
{
    /** @use HasFactory<ServiceProviderProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_uuid',
        'business_name',
        'slug',
        'logo_disk',
        'logo_path',
        'logo_alt_text',
        'legal_name',
        'tax_identifier',
        'verification_status',
        'country_code',
        'city',
        'district',
        'whatsapp_phone',
        'service_area',
        'description',
        'billing_preference',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function services(): HasMany
    {
        return $this->hasMany(EventService::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_uuid';
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => app(PartnerLogoService::class)->temporaryUrl($this));
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceProviderProfile $profile): void {
            if (! $profile->public_uuid) {
                $profile->public_uuid = (string) Str::uuid();
            }

            if (! $profile->slug) {
                $baseSlug = Str::slug($profile->business_name);
                $slug = $baseSlug;
                $counter = 2;

                while (static::query()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $profile->slug = $slug;
            }
        });
    }
}
