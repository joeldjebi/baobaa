<?php

namespace App\Models;

use Database\Factories\OwnerDepositRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerDepositRule extends Model
{
    /** @use HasFactory<OwnerDepositRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_profile_id',
        'name',
        'deposit_type',
        'percentage_rate',
        'fixed_amount',
        'minimum_amount',
        'maximum_amount',
        'currency',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'percentage_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }
}
