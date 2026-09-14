<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
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
            ->where('verification_status', VerificationStatus::Verified)
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
            'fallbackProfiles' => $this->fallbackProfiles(),
            'filters' => $filters,
            'serviceTypes' => EventServiceType::query()
                ->where('is_active', true)
                ->whereHas('services', fn (Builder $query) => $query->where('status', VenueStatus::Published))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'cities' => EventService::query()
                ->where('status', VenueStatus::Published)
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
            ->where('public_uuid', $serviceProviderProfile)
            ->orWhere('slug', $serviceProviderProfile)
            ->first();

        if (! $profile) {
            return view('service-provider-profiles.show', [
                'serviceProviderProfile' => $this->fallbackProfile($serviceProviderProfile),
                'services' => collect($this->fallbackServices()),
                'filters' => $filters,
                'serviceTypes' => collect(),
            ]);
        }

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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackProfiles(): array
    {
        return [
            [
                'name' => 'Studio Son Signature',
                'public_uuid' => '4f6d39c0-6309-4f2f-9dd5-14d86dd82001',
                'slug' => 'studio-son-signature',
                'city' => 'Abidjan',
                'district' => 'Cocody',
                'country' => 'CI',
                'services_count' => 4,
                'starting_price' => 180000,
                'description' => 'Sonorisation, régie et installation audio pour événements professionnels.',
            ],
            [
                'name' => 'Lumière Prestige Events',
                'public_uuid' => '4f6d39c0-6309-4f2f-9dd5-14d86dd82002',
                'slug' => 'lumiere-prestige-events',
                'city' => 'Dakar',
                'district' => 'Almadies',
                'country' => 'SN',
                'services_count' => 3,
                'starting_price' => 220000,
                'description' => 'Scénographie lumineuse, écrans et ambiance visuelle premium.',
            ],
            [
                'name' => 'Capture Émotion',
                'public_uuid' => '4f6d39c0-6309-4f2f-9dd5-14d86dd82003',
                'slug' => 'capture-emotion',
                'city' => 'Cotonou',
                'district' => 'Haie Vive',
                'country' => 'BJ',
                'services_count' => 5,
                'starting_price' => 250000,
                'description' => 'Photo, vidéo et captation multicaméra pour moments d’exception.',
            ],
        ];
    }

    private function fallbackProfile(string $slug): object
    {
        $profile = collect($this->fallbackProfiles())->firstWhere('public_uuid', $slug)
            ?? collect($this->fallbackProfiles())->firstWhere('slug', $slug)
            ?? $this->fallbackProfiles()[0];

        return (object) [
            'business_name' => $profile['name'],
            'public_uuid' => $profile['public_uuid'],
            'slug' => $profile['slug'],
            'logo_url' => null,
            'logo_alt_text' => $profile['name'],
            'city' => $profile['city'],
            'district' => $profile['district'],
            'country_code' => $profile['country'],
            'service_area' => $profile['city'].' et communes proches',
            'description' => $profile['description'],
            'published_services_count' => $profile['services_count'],
            'verification_status' => VerificationStatus::Verified,
        ];
    }

    /**
     * @return array<int, object>
     */
    private function fallbackServices(): array
    {
        return [
            (object) [
                'name' => 'Sonorisation premium avec technicien',
                'short_description' => 'Un dispositif audio clair et puissant pour conférences, mariages et concerts.',
                'starting_price' => 180000,
                'currency' => 'XOF',
                'pricing_unit' => 'event',
                'service_area' => 'Grand Abidjan',
                'type' => (object) ['name' => 'Sonorisation'],
            ],
            (object) [
                'name' => 'Lumière d’ambiance et scénographie',
                'short_description' => 'Mise en lumière élégante de la scène, des tables et des espaces clés.',
                'starting_price' => 220000,
                'currency' => 'XOF',
                'pricing_unit' => 'event',
                'service_area' => 'Grand Abidjan',
                'type' => (object) ['name' => 'Lumière et scénographie'],
            ],
            (object) [
                'name' => 'Captation photo et vidéo événementielle',
                'short_description' => 'Une équipe créative pour documenter les moments forts de votre événement.',
                'starting_price' => 250000,
                'currency' => 'XOF',
                'pricing_unit' => 'event',
                'service_area' => 'Grand Abidjan',
                'type' => (object) ['name' => 'Photo et vidéo'],
            ],
        ];
    }
}
