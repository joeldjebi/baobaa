<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\OwnerProfile;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicOwnerProfileController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'city' => $request->string('city')->trim()->toString(),
        ];

        $publicProfilesQuery = OwnerProfile::query()
            ->whereHas('venues', fn (Builder $query) => $query->where('status', VenueStatus::Published));

        $profiles = (clone $publicProfilesQuery)
            ->withCount([
                'venues as published_venues_count' => fn (Builder $query) => $query->where('status', VenueStatus::Published),
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $search = '%'.$filters['q'].'%';

                $query->where(fn (Builder $query) => $query
                    ->where('business_name', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhereHas('venues', fn (Builder $query) => $query
                        ->where('status', VenueStatus::Published)
                        ->where(fn (Builder $query) => $query
                            ->where('name', 'like', $search)
                            ->orWhere('city', 'like', $search))));
            })
            ->when($filters['city'] !== '', fn (Builder $query) => $query->whereHas('venues', fn (Builder $query) => $query
                ->where('status', VenueStatus::Published)
                ->where('city', $filters['city'])))
            ->orderByDesc('published_venues_count')
            ->orderBy('business_name')
            ->paginate(12)
            ->withQueryString();

        return view('owner-profiles.index', [
            'profiles' => $profiles,
            'filters' => $filters,
            'partnersCount' => (clone $publicProfilesQuery)->count(),
            'publishedVenuesCount' => Venue::query()->published()->count(),
            'cities' => Venue::query()
                ->published()
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
        ]);
    }

    public function show(string $ownerProfile): View
    {
        $profile = OwnerProfile::query()
            ->where(fn (Builder $query) => $query
                ->where('public_uuid', $ownerProfile)
                ->orWhere('slug', $ownerProfile))
            ->firstOrFail();

        $profile->loadCount([
            'venues as published_venues_count' => fn ($query) => $query->where('status', VenueStatus::Published),
        ]);

        $venues = $profile->venues()
            ->with(['category', 'media'])
            ->where('status', VenueStatus::Published)
            ->orderByDesc('published_at')
            ->orderBy('name')
            ->paginate(9);

        return view('owner-profiles.show', [
            'ownerProfile' => $profile,
            'venues' => $venues,
        ]);
    }
}
