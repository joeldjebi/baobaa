<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Database\Factories\OwnerSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerSubscription extends Model
{
    /** @use HasFactory<OwnerSubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_profile_id',
        'subscription_plan_id',
        'status',
        'amount',
        'currency',
        'starts_on',
        'ends_on',
        'auto_renews',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'auto_renews' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
