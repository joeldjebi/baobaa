<?php

namespace App\Services;

use App\Models\ManagedLocation;
use Illuminate\Database\Eloquent\Builder;

/**
 * Countries, cities and districts managed by the SAP, used by public filters and owner forms.
 */
class LocationOptionsService
{
    /**
     * @return array<string, string>
     */
    public function countries(): array
    {
        return ManagedLocation::query()
            ->active()
            ->countries()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'code'])
            ->filter(fn (ManagedLocation $country): bool => filled($country->code))
            ->mapWithKeys(fn (ManagedLocation $country): array => [$country->code => $country->name])
            ->all();
    }

    /**
     * @return array<int, array{name: string, country_code: string}>
     */
    public function cities(): array
    {
        return ManagedLocation::query()
            ->active()
            ->cities()
            ->whereHas('parent', fn (Builder $query) => $query->active()->countries())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'country_code'])
            ->map(fn (ManagedLocation $city): array => [
                'name' => $city->name,
                'country_code' => (string) $city->country_code,
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, city: string, country_code: string}>
     */
    public function districts(): array
    {
        return ManagedLocation::query()
            ->active()
            ->districts()
            ->with('parent:id,name,country_code')
            ->whereHas('parent', fn (Builder $query) => $query
                ->active()
                ->cities()
                ->whereHas('parent', fn (Builder $query) => $query->active()->countries()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['parent_id', 'name', 'country_code'])
            ->map(fn (ManagedLocation $district): array => [
                'name' => $district->name,
                'city' => (string) $district->parent?->name,
                'country_code' => (string) $district->country_code,
            ])
            ->all();
    }
}
