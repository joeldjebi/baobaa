<?php

namespace App\Models;

use Database\Factories\SponsorshipCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'owner_profile_id',
    'venue_id',
    'sponsorship_plan_id',
    'name',
    'goal',
    'placement',
    'status',
    'starts_on',
    'ends_on',
    'budget_amount',
    'daily_budget',
    'currency',
    'target_cities',
    'impressions_count',
    'clicks_count',
    'approved_by',
    'approved_at',
])]
class SponsorshipCampaign extends Model
{
    /** @use HasFactory<SponsorshipCampaignFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'target_cities' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function sponsorshipPlan(): BelongsTo
    {
        return $this->belongsTo(SponsorshipPlan::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
