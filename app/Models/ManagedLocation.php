<?php

namespace App\Models;

use Database\Factories\ManagedLocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagedLocation extends Model
{
    /** @use HasFactory<ManagedLocationFactory> */
    use HasFactory;

    public const TypeCountry = 'country';

    public const TypeCity = 'city';

    public const TypeDistrict = 'district';

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'slug',
        'code',
        'country_code',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCountries(Builder $query): Builder
    {
        return $query->where('type', self::TypeCountry);
    }

    public function scopeCities(Builder $query): Builder
    {
        return $query->where('type', self::TypeCity);
    }

    public function scopeDistricts(Builder $query): Builder
    {
        return $query->where('type', self::TypeDistrict);
    }
}
