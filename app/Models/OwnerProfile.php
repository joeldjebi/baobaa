<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Services\PartnerLogoService;
use Database\Factories\OwnerProfileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OwnerProfile extends Model
{
    /** @use HasFactory<OwnerProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_uuid',
        'owner_type',
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
        'whatsapp_phone',
        'payout_provider',
        'payout_account_reference',
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

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_uuid';
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => app(PartnerLogoService::class)->temporaryUrl($this));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OwnerSubscription::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function moduleTemplates(): HasMany
    {
        return $this->hasMany(OwnerModuleTemplate::class);
    }

    public function sponsorshipCampaigns(): HasMany
    {
        return $this->hasMany(SponsorshipCampaign::class);
    }

    protected static function booted(): void
    {
        static::creating(function (OwnerProfile $profile): void {
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
