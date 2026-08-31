<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\VenueStatus;
use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicVenueController extends Controller
{
    public function index(Request $request): View
    {
        $venues = Venue::query()
            ->with(['category', 'media', 'ownerProfile'])
            ->where('status', VenueStatus::Published)
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->string('city')->toString()))
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', fn ($query) => $query->where('slug', $request->string('category')->toString()));
            })
            ->when($request->filled('capacity'), fn ($query) => $query->where('max_capacity', '>=', $request->integer('capacity')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('starting_price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('starting_price', '<=', $request->integer('max_price')))
            ->when($request->filled('start_date') || $request->filled('end_date'), function ($query) use ($request): void {
                $query->whereHas('availabilities', function ($query) use ($request): void {
                    $query->where('status', 'available');

                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $query->whereBetween('available_date', [
                            $request->date('start_date')->toDateString(),
                            $request->date('end_date')->toDateString(),
                        ]);

                        return;
                    }

                    if ($request->filled('start_date')) {
                        $query->whereDate('available_date', '>=', $request->date('start_date')->toDateString());
                    }

                    if ($request->filled('end_date')) {
                        $query->whereDate('available_date', '<=', $request->date('end_date')->toDateString());
                    }
                });
            })
            ->orderByDesc('published_at')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $viewData = [
            'venues' => $venues,
            'categories' => VenueCategory::query()
                ->where('is_active', true)
                ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'slug']),
            'cities' => Venue::query()
                ->where('status', VenueStatus::Published)
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
            'searchSuggestions' => Venue::query()
                ->where('status', VenueStatus::Published)
                ->orderBy('name')
                ->limit(20)
                ->get(['name'])
                ->map(fn (Venue $venue): array => ['label' => $venue->name, 'type' => 'Espace'])
                ->merge(
                    VenueCategory::query()
                        ->where('is_active', true)
                        ->whereHas('venues', fn ($query) => $query->where('status', VenueStatus::Published))
                        ->orderBy('name')
                        ->get(['name'])
                        ->map(fn (VenueCategory $category): array => ['label' => $category->name, 'type' => 'Catégorie'])
                )
                ->values(),
        ];

        if ($request->ajax()) {
            return view('venues.partials.results', [
                'venues' => $venues,
            ]);
        }

        return view('venues.index', $viewData);
    }

    public function show(string $slug): View
    {
        $venue = Venue::query()
            ->with([
                'category',
                'ownerProfile',
                'amenities',
                'media',
                'rates',
                'configurations',
                'addOns',
                'policies',
                'faqs',
                'reviews.client',
            ])
            ->where('slug', $slug)
            ->first();

        $eligibleBooking = null;
        $existingReview = null;

        if ($venue && Auth::check()) {
            $eligibleBooking = Auth::user()->bookings()
                ->where('venue_id', $venue->id)
                ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
                ->latest('event_date')
                ->first();

            $existingReview = $venue->reviews()
                ->where('client_id', Auth::id())
                ->first();
        }

        return view('venues.show', [
            'venue' => $venue,
            'fallback' => $this->fallbackVenue($slug),
            'similarVenues' => $this->fallbackSimilarVenues($slug),
            'approvedReviews' => $venue?->reviews
                ?->where('status', 'approved')
                ->sortByDesc('approved_at')
                ->values() ?? collect(),
            'canReview' => $venue && $eligibleBooking && ! $existingReview,
            'existingReview' => $existingReview,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackVenue(string $slug): array
    {
        $venues = [
            'auditorium-premium-avec-scene-et-regie' => [
                'title' => 'Auditorium premium avec scene et regie',
                'category' => 'Salle de spectacle',
                'city' => 'Abidjan',
                'district' => 'Plateau',
                'capacity' => '80-450 invites',
                'surface' => '650 m2',
                'price' => '350 000 XOF',
                'reservation' => '100 000 XOF',
                'rating' => '4.9',
                'reviews' => 38,
                'description' => 'Un auditorium central pense pour conferences, lancements de produits, concerts intimistes et ceremonies premium.',
                'images' => [
                    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            'salle-de-reception-lumineuse-pour-mariage' => [
                'title' => 'Salle de reception lumineuse pour mariage',
                'category' => 'Salle de mariage',
                'city' => 'Abidjan',
                'district' => 'Cocody',
                'capacity' => '120-700 invites',
                'surface' => '1 100 m2',
                'price' => '500 000 XOF',
                'reservation' => '150 000 XOF',
                'rating' => '5.0',
                'reviews' => 52,
                'description' => 'Une grande salle lumineuse avec terrasse, parking et espace traiteur pour receptions, mariages et grands diners.',
                'images' => [
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            'conference-room-equipee-avec-terrasse' => [
                'title' => 'Conference room equipee avec terrasse',
                'category' => 'Salle de conference',
                'city' => 'Dakar',
                'district' => 'Almadies',
                'capacity' => '12-90 invites',
                'surface' => '240 m2',
                'price' => '85 000 XOF',
                'reservation' => '35 000 XOF',
                'rating' => '4.8',
                'reviews' => 24,
                'description' => 'Un espace professionnel lumineux avec terrasse, Wi-Fi haut debit, mobilier modulable et equipements de presentation.',
                'images' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=900&q=85',
                ],
            ],
            'jardin-evenementiel-privatisable' => [
                'title' => 'Jardin evenementiel privatisable',
                'category' => 'Jardin evenementiel',
                'city' => 'Cotonou',
                'district' => 'Fidjrosse',
                'capacity' => '60-400 invites',
                'surface' => '1 500 m2',
                'price' => '220 000 XOF',
                'reservation' => '80 000 XOF',
                'rating' => '4.9',
                'reviews' => 31,
                'description' => 'Un jardin privatisable pour receptions, cocktails, ceremonies en plein air et evenements de marque.',
                'images' => [
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1400&q=85',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=900&q=85',
                    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=85',
                ],
            ],
        ];

        return $venues[$slug] ?? $venues['auditorium-premium-avec-scene-et-regie'];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fallbackSimilarVenues(string $currentSlug): array
    {
        return collect([
            [
                'title' => 'Auditorium premium avec scene et regie',
                'slug' => 'auditorium-premium-avec-scene-et-regie',
                'city' => 'Abidjan, Plateau',
                'capacity' => '80-450 invites',
                'price' => 'A partir de 350 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Salle de reception lumineuse pour mariage',
                'slug' => 'salle-de-reception-lumineuse-pour-mariage',
                'city' => 'Abidjan, Cocody',
                'capacity' => '120-700 invites',
                'price' => 'A partir de 500 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Conference room equipee avec terrasse',
                'slug' => 'conference-room-equipee-avec-terrasse',
                'city' => 'Dakar, Almadies',
                'capacity' => '12-90 invites',
                'price' => 'A partir de 85 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=85',
            ],
            [
                'title' => 'Jardin evenementiel privatisable',
                'slug' => 'jardin-evenementiel-privatisable',
                'city' => 'Cotonou, Fidjrosse',
                'capacity' => '60-400 invites',
                'price' => 'A partir de 220 000 XOF',
                'image' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=900&q=85',
            ],
        ])
            ->reject(fn (array $venue): bool => $venue['slug'] === $currentSlug)
            ->take(3)
            ->values()
            ->all();
    }
}
