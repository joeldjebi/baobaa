<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\OwnerProfile;
use Illuminate\View\View;

class PublicOwnerProfileController extends Controller
{
    public function index(): View
    {
        $profiles = OwnerProfile::query()
            ->withCount([
                'venues as published_venues_count' => fn ($query) => $query->where('status', VenueStatus::Published),
            ])
            ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
            ->orderByDesc('published_venues_count')
            ->orderBy('business_name')
            ->paginate(12);

        return view('owner-profiles.index', [
            'profiles' => $profiles,
            'fallbackProfiles' => $this->fallbackProfiles(),
        ]);
    }

    public function show(string $ownerProfile): View
    {
        $profile = OwnerProfile::query()
            ->where('public_uuid', $ownerProfile)
            ->orWhere('slug', $ownerProfile)
            ->first();

        if (! $profile) {
            return view('owner-profiles.show', [
                'ownerProfile' => $this->fallbackProfile($ownerProfile),
                'venues' => collect($this->fallbackVenues()),
            ]);
        }

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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fallbackProfiles(): array
    {
        return [
            [
                'name' => 'BAOBAA Signature Events',
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b101',
                'slug' => 'baobaa-signature-events',
                'city' => 'Abidjan',
                'country' => 'CI',
                'venues_count' => 4,
            ],
            [
                'name' => 'Kora Prestige Venues',
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b102',
                'slug' => 'kora-prestige-venues',
                'city' => 'Dakar',
                'country' => 'SN',
                'venues_count' => 3,
            ],
            [
                'name' => 'Azalai Garden Collection',
                'public_uuid' => '2d08df2a-8d0f-42a5-a22e-47a617a7b103',
                'slug' => 'azalai-garden-collection',
                'city' => 'Cotonou',
                'country' => 'BJ',
                'venues_count' => 5,
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
            'country_code' => $profile['country'],
            'published_venues_count' => $profile['venues_count'],
            'verification_status' => (object) ['value' => 'verified'],
        ];
    }

    /**
     * @return array<int, object>
     */
    private function fallbackVenues(): array
    {
        return [
            (object) [
                'name' => 'Auditorium premium avec scene et regie',
                'slug' => 'auditorium-premium-avec-scene-et-regie',
                'district' => 'Plateau',
                'city' => 'Abidjan',
                'max_capacity' => 450,
                'starting_price' => 350000,
                'currency' => 'XOF',
                'category' => (object) ['name' => 'Salle de spectacle'],
                'media' => collect(),
            ],
            (object) [
                'name' => 'Salle de reception lumineuse pour mariage',
                'slug' => 'salle-de-reception-lumineuse-pour-mariage',
                'district' => 'Cocody',
                'city' => 'Abidjan',
                'max_capacity' => 700,
                'starting_price' => 500000,
                'currency' => 'XOF',
                'category' => (object) ['name' => 'Salle de mariage'],
                'media' => collect(),
            ],
            (object) [
                'name' => 'Jardin evenementiel privatisable',
                'slug' => 'jardin-evenementiel-privatisable',
                'district' => 'Fidjrosse',
                'city' => 'Cotonou',
                'max_capacity' => 400,
                'starting_price' => 220000,
                'currency' => 'XOF',
                'category' => (object) ['name' => 'Jardin evenementiel'],
                'media' => collect(),
            ],
        ];
    }
}
