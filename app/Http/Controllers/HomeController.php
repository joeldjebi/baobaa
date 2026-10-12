<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\VenueCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = VenueCategory::query()
            ->where('is_active', true)
            ->whereHas('venues', fn (Builder $query) => $query->published())
            ->withCount(['venues as published_venues_count' => fn (Builder $query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon']);

        $featuredVenues = Venue::query()
            ->with(['category', 'media'])
            ->withCount(['bookings', 'reviews'])
            ->published()
            ->orderByDesc('bookings_count')
            ->orderByDesc('reviews_count')
            ->orderByDesc('published_at')
            ->limit(12)
            ->get();

        $discoveryVenues = Venue::query()
            ->with(['category', 'media'])
            ->published()
            ->inRandomOrder()
            ->limit(24)
            ->get();

        return view('welcome', [
            'categories' => $categories,
            'publishedVenuesCount' => Venue::query()->published()->count(),
            'publishedCitiesCount' => Venue::query()->published()->distinct()->count('city'),
            'heroThumbnails' => $featuredVenues->take(4)->map(fn (Venue $venue): string => $venue->cover_image_url)->values(),
            'featuredVenues' => $featuredVenues,
            'categoryCarouselVenues' => $discoveryVenues->filter(fn (Venue $venue): bool => $venue->category !== null)->take(12)->values(),
            'cityCarouselVenues' => $discoveryVenues->shuffle()->take(12)->values(),
            'districtCarouselVenues' => $discoveryVenues->filter(fn (Venue $venue): bool => filled($venue->district))->shuffle()->take(12)->values(),
            'cityHighlights' => Venue::query()
                ->published()
                ->select('city', DB::raw('count(*) as venues_count'), DB::raw('min(starting_price) as starting_price'))
                ->groupBy('city')
                ->orderByDesc('venues_count')
                ->limit(6)
                ->get(),
            'categoryHighlights' => $categories->sortByDesc('published_venues_count')->take(6)->values(),
            'searchSuggestions' => $featuredVenues
                ->merge($discoveryVenues)
                ->unique('id')
                ->sortBy('name')
                ->map(fn (Venue $venue): array => ['label' => $venue->name, 'type' => 'Espace'])
                ->merge($categories->map(fn (VenueCategory $category): array => ['label' => $category->name, 'type' => 'Catégorie']))
                ->values(),
            'citySuggestions' => Venue::query()
                ->published()
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
                ->values(),
        ]);
    }
}
