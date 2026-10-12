<?php

namespace App\Http\Controllers;

use App\Enums\VenueStatus;
use App\Models\OwnerProfile;
use App\Models\Product;
use App\Models\ServiceProviderProfile;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * List every public page that search engines should index.
     */
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => url('/'), 'lastmod' => null, 'priority' => '1.0'],
            ['loc' => route('venues.index'), 'lastmod' => null, 'priority' => '0.9'],
            ['loc' => route('owner-profiles.index'), 'lastmod' => null, 'priority' => '0.6'],
            ['loc' => route('service-provider-profiles.index'), 'lastmod' => null, 'priority' => '0.7'],
            ['loc' => route('event-services.index'), 'lastmod' => null, 'priority' => '0.6'],
            ['loc' => route('products.index'), 'lastmod' => null, 'priority' => '0.7'],
            ['loc' => route('owner.register'), 'lastmod' => null, 'priority' => '0.5'],
            ['loc' => route('pages.contact'), 'lastmod' => null, 'priority' => '0.3'],
            ['loc' => route('pages.terms'), 'lastmod' => null, 'priority' => '0.2'],
            ['loc' => route('pages.privacy'), 'lastmod' => null, 'priority' => '0.2'],
            ['loc' => route('pages.legal-notice'), 'lastmod' => null, 'priority' => '0.2'],
        ]);

        Venue::query()
            ->published()
            ->select(['slug', 'updated_at'])
            ->orderByDesc('updated_at')
            ->each(fn (Venue $venue) => $urls->push([
                'loc' => route('venues.show', $venue->slug),
                'lastmod' => $venue->updated_at?->toAtomString(),
                'priority' => '0.8',
            ]));

        OwnerProfile::query()
            ->whereHas('venues', fn (Builder $query) => $query->where('status', VenueStatus::Published))
            ->select(['id', 'public_uuid', 'updated_at'])
            ->each(fn (OwnerProfile $profile) => $urls->push([
                'loc' => route('owner-profiles.show', $profile->public_uuid),
                'lastmod' => $profile->updated_at?->toAtomString(),
                'priority' => '0.5',
            ]));

        ServiceProviderProfile::query()
            ->publiclyListed()
            ->whereHas('services', fn (Builder $query) => $query->where('status', VenueStatus::Published))
            ->select(['id', 'public_uuid', 'updated_at'])
            ->each(fn (ServiceProviderProfile $profile) => $urls->push([
                'loc' => route('service-provider-profiles.show', $profile->public_uuid),
                'lastmod' => $profile->updated_at?->toAtomString(),
                'priority' => '0.5',
            ]));

        Product::query()
            ->publiclyAvailable()
            ->select(['id', 'slug', 'updated_at'])
            ->each(fn (Product $product) => $urls->push([
                'loc' => route('products.show', $product),
                'lastmod' => $product->updated_at?->toAtomString(),
                'priority' => '0.6',
            ]));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
