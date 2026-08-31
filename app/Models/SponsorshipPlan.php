<?php

namespace App\Models;

use Database\Factories\SponsorshipPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'placement',
    'price',
    'currency',
    'duration_days',
    'description',
    'features',
    'is_active',
    'sort_order',
])]
class SponsorshipPlan extends Model
{
    /** @use HasFactory<SponsorshipPlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(SponsorshipCampaign::class);
    }

    protected static function booted(): void
    {
        static::saving(function (SponsorshipPlan $plan): void {
            if (! $plan->slug) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }
}
