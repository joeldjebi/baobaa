<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\PortalAccessRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'requested_role',
    'status',
    'applicant_type',
    'business_name',
    'legal_name',
    'tax_identifier',
    'country_code',
    'city',
    'whatsapp_phone',
    'motivation',
    'decision_note',
    'reviewed_by',
    'reviewed_at',
])]
class PortalAccessRequest extends Model
{
    /** @use HasFactory<PortalAccessRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requested_role' => UserRole::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
