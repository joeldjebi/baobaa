<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\EventService;
use App\Models\EventServiceType;
use App\Models\ServiceProviderProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicServiceProviderProfileController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'city' => $request->string('city')->trim()->toString(),
            'type_id' => $request->integer('type_id') ?: null,
        ];

        $publicProfilesQuery = ServiceProviderProfile::query()
            ->publiclyListed()
            ->whereHas('services', fn (Builder $query) => $query->where('status', VenueStatus::Published));

        $profilesQuery = ServiceProviderProfile::query()
            ->with(['services' => fn ($query) => $query
                ->with('type')
                ->where('status', VenueStatus::Published)
                ->when($filters['type_id'], fn (Builder $query) => $query->where('event_service_type_id', $filters['type_id']))
                ->orderBy('starting_price')
                ->orderBy('name')
                ->limit(3),
            ])
            ->withCount([
                'services as published_services_count' => fn (Builder $query) => $query->where('status', VenueStatus::Published),
            ])
            ->publiclyListed()
            ->whereHas('services', fn (Builder $query) => $query
                ->where('status', VenueStatus::Published)
                ->when($filters['type_id'], fn (Builder $query) => $query->where('event_service_type_id', $filters['type_id'])))
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('business_name', 'like', $search)
                        ->orWhere('city', 'like', $search)
                        ->orWhere('district', 'like', $search)
                        ->orWhere('service_area', 'like', $search)
                        ->orWhereHas('services', fn (Builder $query) => $query
                            ->where('status', VenueStatus::Published)
                            ->where(function (Builder $query) use ($search): void {
                                $query->where('name', 'like', $search)
                                    ->orWhere('short_description', 'like', $search)
                                    ->orWhereHas('type', fn (Builder $query) => $query->where('name', 'like', $search));
                            }));
                });
            })
            ->when($filters['city'] !== '', fn (Builder $query) => $query->where('city', $filters['city']))
            ->orderByDesc('published_services_count')
            ->orderBy('business_name');

        $profiles = $profilesQuery
            ->paginate(12)
            ->withQueryString();

        return view('service-provider-profiles.index', [
            'profiles' => $profiles,
            'providersCount' => (clone $publicProfilesQuery)->count(),
            'publishedServicesCount' => EventService::query()
                ->where('status', VenueStatus::Published)
                ->whereHas('serviceProviderProfile', fn (Builder $query) => $query->publiclyListed())
                ->count(),
            'filters' => $filters,
            'serviceTypes' => EventServiceType::query()
                ->where('is_active', true)
                ->whereHas('services', fn (Builder $query) => $query->where('status', VenueStatus::Published))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'cities' => (clone $publicProfilesQuery)
                ->whereNotNull('city')
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
                ->filter()
                ->values(),
        ]);
    }

    public function show(Request $request, string $serviceProviderProfile): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'type_id' => $request->integer('type_id') ?: null,
        ];
        $profile = ServiceProviderProfile::query()
            ->publiclyListed()
            ->where(fn (Builder $query) => $query
                ->where('public_uuid', $serviceProviderProfile)
                ->orWhere('slug', $serviceProviderProfile))
            ->firstOrFail();

        $profile->loadCount([
            'services as published_services_count' => fn ($query) => $query->where('status', VenueStatus::Published),
        ]);

        $services = $profile->services()
            ->with('type')
            ->where('status', VenueStatus::Published)
            ->when($filters['type_id'], fn (Builder $query) => $query->where('event_service_type_id', $filters['type_id']))
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $search = '%'.$filters['q'].'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('short_description', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('type', fn (Builder $query) => $query->where('name', 'like', $search));
                });
            })
            ->orderBy('starting_price')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('service-provider-profiles.show', [
            'serviceProviderProfile' => $profile,
            'services' => $services,
            'filters' => $filters,
            'serviceTypes' => EventServiceType::query()
                ->where('is_active', true)
                ->whereHas('services', fn (Builder $query) => $query
                    ->where('status', VenueStatus::Published)
                    ->where('service_provider_profile_id', $profile->id))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
