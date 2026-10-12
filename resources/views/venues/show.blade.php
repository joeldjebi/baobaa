@php
    use App\Enums\VerificationStatus;

    $title = $venue->name;
    $category = $venue->category?->name ?? 'Espace événementiel';
    $city = $venue->city;
    $district = $venue->district;
    $locationLabel = collect([$district, $city])->filter()->implode(', ');
    $capacity = $venue->min_capacity.'-'.$venue->max_capacity.' invités';
    $surface = $venue->surface_area ? $venue->surface_area.' m²' : null;
    $price = $venue->priceLabel();
    $minimumDuration = $venue->minimumDurationLabel();
    $openingHours = $venue->openingHoursLabel();
    $mapEmbedUrl = $venue->approximateMapEmbedUrl();
    $videoEmbedUrls = $venue->videoEmbedUrls();
    $virtualTourUrl = $venue->virtualTourEmbedUrl();
    $reservation = $depositAmount !== null ? number_format($depositAmount, 0, ',', ' ').' '.$venue->currency : null;
    $acceptedPaymentMethods = collect($venue->payment_methods ?: ['baobaa_checkout'])
        ->map(fn (string $method): string => \App\Enums\PaymentMethod::labelFor($method))
        ->values();
    $publicReviewsCount = (int) $venue->public_reviews_count;
    $publicRating = $publicReviewsCount > 0 ? number_format((float) $venue->public_rating, 1, ',', ' ') : null;
    $isVerified = $venue->verification_status === VerificationStatus::Verified;
    $description = $venue->description ?: $venue->short_description;
    $images = $venue->publicImageUrls();
    $videos = $venue->media
        ->where('type', 'video')
        ->reject(fn ($media): bool => $media->moderation_status === 'rejected')
        ->sortBy('sort_order')
        ->map(fn ($media) => [
            'url' => $media->signed_url,
            'alt' => $media->alt_text ?: $title,
        ])->values()->all();
    $galleryImages = array_values($images);
    $highlights = $venue->highlights ?: [];
    $includedItems = $venue->included_items ?: [];
    $houseRules = $venue->house_rules ?: [];
    $configurations = $venue->configurations->where('is_active', true)->sortBy('sort_order')->values();
    $policies = $venue->policies->sortBy('sort_order')->values();
    $faqs = $venue->faqs->where('is_active', true)->sortBy('sort_order')->values();
    $amenityDetails = collect($venue->space_details['amenities'] ?? [])
        ->filter(fn ($amenity) => filled($amenity['name'] ?? null))
        ->values();
    $amenities = $amenityDetails->isNotEmpty()
        ? $amenityDetails->take(10)
        : $venue->amenities->take(10)->map(fn ($amenity) => ['name' => $amenity->name, 'detail' => null])->values();
    $locationNote = $venue->location_details['public_note'] ?? null;
    $tabs = collect([
        'apercu' => 'Aperçu',
        'inclusions' => $includedItems !== [] ? 'Ce qui est inclus' : null,
        'commodites' => $configurations->isNotEmpty() || $amenities->isNotEmpty() ? 'Espace et commodités' : null,
        'visite-virtuelle' => $virtualTourUrl ? 'Visite 360°' : null,
        'modules' => count($reservationAddOns) > 0 ? 'Services additionnels' : null,
        'disponibilites' => 'Disponibilités',
        'lieu' => 'Lieu',
        'conditions' => $policies->isNotEmpty() || $faqs->isNotEmpty() ? 'À savoir' : null,
        'avis' => 'Avis clients',
    ])->filter()->all();
@endphp

<x-layouts.baobaa :title="$title.' - BAOBAA'" :description="$venue->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $description), 160)" :image="$images[0] ?? null">
    @php
        $structuredData = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'EventVenue',
            'name' => $title,
            'description' => $venue->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $description), 300),
            'url' => route('venues.show', $venue->slug),
            'image' => array_slice($galleryImages, 0, 5) ?: null,
            'maximumAttendeeCapacity' => $venue->max_capacity,
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'addressLocality' => $city,
                'addressRegion' => $district,
                'addressCountry' => $venue->country_code,
            ]),
            'openingHours' => $venue->opens_at && $venue->closes_at ? 'Mo-Su '.substr($venue->opens_at, 0, 5).'-'.substr($venue->closes_at, 0, 5) : null,
            'priceRange' => $venue->starting_price ? 'À partir de '.number_format((int) $venue->starting_price, 0, ',', ' ').' '.$venue->currency : null,
            'aggregateRating' => $publicReviewsCount > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $venue->public_rating, 1),
                'reviewCount' => $publicReviewsCount,
                'bestRating' => 5,
                'worstRating' => 1,
            ] : null,
        ], fn ($value): bool => $value !== null && $value !== []);
    @endphp
    <x-slot:head>
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    </x-slot:head>

    <main class="min-h-screen scroll-smooth bg-white pb-24 text-[#151821] lg:pb-0">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
                </a>

                <x-navigation.public-menu active="venues" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('venues.list-venue') }}" class="hidden rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] lg:inline-flex">Devenir partenaire</a>
                    @endguest
                    <x-navigation.account-menu />
                    <x-navigation.mobile-menu active="venues" />
                </div>
            </div>
        </header>

        <section class="mx-auto max-w-7xl px-5 py-6 sm:px-8">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-[#6f7890]" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}" class="hover:text-[#2f6bff]">Accueil</a>
                <span>/</span>
                <a href="{{ route('venues.index') }}" class="hover:text-[#2f6bff]">Espaces</a>
                <span>/</span>
                <a href="{{ route('venues.index', ['city' => $city]) }}" class="hover:text-[#2f6bff]">{{ $city }}</a>
                @if ($venue->category)
                    <span>/</span>
                    <a href="{{ route('venues.index', ['category' => $venue->category->slug]) }}" class="hover:text-[#2f6bff]">{{ $category }}</a>
                @endif
            </nav>

            <div class="mt-4 flex flex-wrap items-start justify-between gap-5">
                <div>
                    <h1 class="max-w-4xl text-3xl font-extrabold leading-tight tracking-[-0.035em] text-[#151821] sm:text-4xl">{{ $title }}</h1>
                    <p class="mt-2 text-sm font-semibold text-[#6f7890]">{{ $locationLabel }} · {{ $category }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="venue-share-button" data-share-title="{{ $title }}" data-share-text="{{ $venue->short_description }}" data-share-url="{{ url()->current() }}" class="rounded-full border border-[#d9e2f6] px-4 py-2 text-sm font-extrabold text-[#2f6bff] transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef4ff]">Partager</button>
                    <button type="button" id="venue-save-button" data-save-key="baobaa_saved_venue_{{ $venue->id }}" data-save-title="{{ $title }}" data-save-url="{{ url()->current() }}" class="rounded-full border border-[#d9e2f6] px-4 py-2 text-sm font-extrabold text-[#2f6bff] transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef4ff]">Sauvegarder</button>
                </div>
            </div>

            <div id="photos" class="mt-5 scroll-mt-28">
                @if (count($galleryImages) === 0)
                    <div class="overflow-hidden rounded-[18px] ring-1 ring-[#dce6f7]">
                        <img src="{{ \App\Models\Venue::placeholderImageUrl() }}" alt="Photos de {{ $title }} bientôt disponibles" class="h-64 w-full object-cover sm:h-[420px]">
                    </div>
                @elseif (count($galleryImages) === 1)
                    <button type="button" data-gallery-index="0" class="block w-full cursor-zoom-in overflow-hidden rounded-[18px]">
                        <img src="{{ $galleryImages[0] }}" alt="{{ $title }}" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-64 w-full object-cover transition duration-500 hover:scale-[1.02] sm:h-[460px]">
                    </button>
                @else
                    @php($secondaryImages = array_slice($galleryImages, 1, 4))
                    <div class="grid gap-2 overflow-hidden rounded-[18px] lg:grid-cols-[1.08fr_.92fr]">
                        <button type="button" data-gallery-index="0" class="relative cursor-zoom-in overflow-hidden text-left">
                            <img src="{{ $galleryImages[0] }}" alt="{{ $title }}" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-64 w-full object-cover transition duration-500 hover:scale-[1.02] sm:h-[420px]">
                        </button>
                        <div class="hidden gap-2 sm:grid {{ count($secondaryImages) > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                            @foreach ($secondaryImages as $imageIndex => $image)
                                <button type="button" data-gallery-index="{{ $imageIndex + 1 }}" class="relative cursor-zoom-in overflow-hidden text-left {{ count($secondaryImages) === 3 && $loop->first ? 'col-span-2' : '' }}">
                                    <img src="{{ $image }}" alt="{{ $title }} - photo {{ $imageIndex + 2 }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-full min-h-[140px] w-full object-cover transition duration-500 hover:scale-[1.04] {{ count($secondaryImages) <= 2 ? 'lg:h-[420px]' : 'lg:h-[206px]' }}">
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" data-gallery-index="0" class="rounded-full border border-[#d9e2f6] bg-white px-4 py-2 text-sm font-extrabold text-[#151821] shadow-sm transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:text-[#2f6bff]">Voir les {{ count($galleryImages) }} photos</button>
                    </div>
                @endif
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    {{ $capacity }}
                </span>
                @if ($surface)
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11h18"/><path d="M5 7h14v10H5z"/><path d="M9 11V7"/><path d="M15 11V7"/></svg>
                        {{ $surface }}
                    </span>
                @endif
                <span class="inline-flex items-center gap-2 rounded-full bg-[#eaf1ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s6-4.35 6-10a6 6 0 1 0-12 0c0 5.65 6 10 6 10Z"/><circle cx="12" cy="11" r="2.5"/></svg>
                    {{ $city }}
                </span>
                @if ($openingHours)
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        Ouvert de {{ $openingHours }}
                    </span>
                @endif
                @if ($minimumDuration)
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $minimumDuration }}</span>
                @endif
                @if ($virtualTourUrl)
                    <a href="#visite-virtuelle" class="inline-flex items-center gap-2 rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Visite virtuelle 360°</a>
                @endif
                @if ($isVerified)
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#ecfdf5] px-3 py-1.5 text-xs font-extrabold text-[#047857]">Espace vérifié</span>
                @endif
                @foreach ($highlights as $highlight)
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $highlight }}</span>
                @endforeach
            </div>

            @if (count($videos) > 0 || count($videoEmbedUrls) > 0)
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($videoEmbedUrls as $videoEmbedUrl)
                        <div class="overflow-hidden rounded-[18px] border border-[#dce6f7] bg-[#07152f] shadow-sm">
                            <iframe src="{{ $videoEmbedUrl }}" title="Vidéo de {{ $title }}" loading="lazy" class="aspect-video w-full" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    @endforeach
                    @foreach ($videos as $video)
                        <div class="overflow-hidden rounded-[18px] border border-[#dce6f7] bg-[#07152f] shadow-sm">
                            <video controls preload="metadata" class="aspect-video w-full bg-[#07152f]" aria-label="{{ $video['alt'] }}">
                                <source src="{{ $video['url'] }}">
                                Votre navigateur ne peut pas lire cette vidéo.
                            </video>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="sticky top-[64px] z-30 border-y border-[#edf2fb] bg-white/95 px-5 py-3 sm:px-8 baobaa-sticky-stable">
            <nav id="venue-tabs" class="mx-auto flex max-w-7xl gap-2 overflow-x-auto rounded-full border border-[#dfe7f8] bg-white p-1 text-sm font-extrabold text-[#6f7890] shadow-[0_16px_50px_rgba(23,62,122,0.08)] baobaa-scrollbar-none">
                @foreach ($tabs as $target => $tab)
                    <a href="#{{ $target }}" data-tab-link aria-current="{{ $loop->first ? 'true' : 'false' }}" class="shrink-0 rounded-full px-4 py-2.5 transition {{ $loop->first ? 'bg-[#2f6bff] text-white shadow-sm' : 'text-[#6f7890] hover:bg-[#f4f7ff] hover:text-[#2f6bff]' }}">{{ $tab }}</a>
                @endforeach
            </nav>
        </div>

        <section class="mx-auto mt-5 grid max-w-7xl gap-8 px-5 pb-14 sm:px-8 lg:grid-cols-[1fr_360px] lg:items-start">
            <article class="space-y-8">
                @if ($venue->ownerProfile)
                    <section class="rounded-2xl border border-[#dce6f7] bg-white p-5 shadow-sm shadow-[#173e7a]/5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-full bg-[#eaf1ff] font-extrabold text-[#2f6bff]">
                                    @if ($venue->ownerProfile->logo_url)
                                        <img src="{{ $venue->ownerProfile->logo_url }}" alt="{{ $venue->ownerProfile->business_name }}" referrerpolicy="no-referrer" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        {{ mb_strtoupper(mb_substr($venue->ownerProfile->business_name, 0, 1)) }}
                                    @endif
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-[#151821]">Proposé par {{ $venue->ownerProfile->business_name }}</p>
                                    <p class="text-xs font-semibold text-[#8a94aa]">Vous échangez avec le propriétaire depuis votre espace client après votre demande.</p>
                                </div>
                            </div>
                            <a href="{{ route('owner-profiles.show', $venue->ownerProfile->public_uuid) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 ring-4 ring-[#eaf1ff] transition hover:-translate-y-0.5 hover:bg-[#2258df]">
                                Découvrir le partenaire
                                <span class="text-base leading-none">→</span>
                            </a>
                        </div>
                    </section>
                @endif

                <section id="apercu" class="scroll-mt-32">
                    <h2 class="text-xl font-extrabold text-[#151821]">Aperçu</h2>
                    @if ($venue->short_description && $venue->description)
                        <p class="mt-3 text-sm font-bold leading-7 text-[#151821]">{{ $venue->short_description }}</p>
                    @endif
                    <p class="mt-3 whitespace-pre-line text-sm font-medium leading-7 text-[#4d5872]">{{ $description ?: 'Le propriétaire complète actuellement la présentation de cet espace.' }}</p>
                    @if ($houseRules !== [])
                        <h3 class="mt-5 text-sm font-extrabold text-[#151821]">Règles du lieu</h3>
                        <ul class="mt-3 space-y-2 text-sm font-medium text-[#4d5872]">
                            @foreach ($houseRules as $rule)
                                <li class="flex gap-2"><span class="text-[#2f6bff]">•</span>{{ $rule }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                @if ($includedItems !== [])
                    <section id="inclusions" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                        <h2 class="text-xl font-extrabold text-[#151821]">Ce qui est inclus dans votre réservation</h2>
                        <div class="mt-4 grid overflow-hidden rounded-2xl border border-[#e4ebf8] bg-white sm:grid-cols-2">
                            @foreach ($includedItems as $item)
                                <div class="flex items-center gap-3 border-b border-[#edf2fb] px-4 py-3 text-sm font-semibold text-[#4d5872] even:bg-[#fbfcff] sm:odd:border-r">
                                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#eaf1ff] text-xs text-[#2f6bff]">✓</span>{{ $item }}
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($configurations->isNotEmpty() || $amenities->isNotEmpty())
                    <section id="commodites" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                        <h2 class="text-xl font-extrabold text-[#151821]">Espace et commodités</h2>
                        @if ($configurations->isNotEmpty())
                            <p class="mt-1 text-xs font-extrabold text-[#8a94aa]">Capacité par configuration</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                @foreach ($configurations as $configuration)
                                    <div class="rounded-2xl bg-[#f7f9fd] p-5 text-center">
                                        <p class="text-sm font-extrabold text-[#151821]">{{ $configuration->name }}</p>
                                        <p class="mt-2 text-2xl font-extrabold text-[#2f6bff]">{{ $configuration->capacity }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($amenities->isNotEmpty())
                            <div class="mt-5 divide-y divide-[#edf0f7] rounded-2xl border border-[#edf0f7]">
                                @foreach ($amenities as $amenity)
                                    @if (filled($amenity['detail'] ?? null))
                                        <details class="group p-4">
                                            <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-extrabold text-[#151821]">
                                                {{ $amenity['name'] }}
                                                <span class="text-[#8a94aa] group-open:rotate-180">⌄</span>
                                            </summary>
                                            <p class="mt-2 text-sm font-medium text-[#6f7890]">{{ $amenity['detail'] }}</p>
                                        </details>
                                    @else
                                        <p class="flex items-center gap-3 p-4 text-sm font-extrabold text-[#151821]"><span class="text-[#2f6bff]">✓</span>{{ $amenity['name'] }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                @if ($virtualTourUrl)
                    <section id="visite-virtuelle" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                        <h2 class="text-xl font-extrabold text-[#151821]">Visite virtuelle 360°</h2>
                        <p class="mt-1 text-sm font-semibold text-[#6f7890]">Déplacez-vous dans l’espace comme si vous y étiez.</p>
                        <div class="mt-4 overflow-hidden rounded-2xl border border-[#dce6f7] bg-[#07152f]">
                            <iframe src="{{ $virtualTourUrl }}" title="Visite virtuelle 360° de {{ $title }}" loading="lazy" class="aspect-video w-full" allow="fullscreen; xr-spatial-tracking; gyroscope; accelerometer" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    </section>
                @endif

                @if (count($reservationAddOns) > 0)
                    <section id="modules" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                        <h2 class="text-xl font-extrabold text-[#151821]">Services additionnels</h2>
                        <p class="mt-1 text-sm font-semibold text-[#6f7890]">Options proposées par le propriétaire, à sélectionner lors de votre demande de réservation.</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($reservationAddOns as $addOn)
                                <div class="rounded-2xl border border-[#dce6f7] bg-white p-4 shadow-sm">
                                    <p class="text-sm font-extrabold text-[#151821]">{{ $addOn['name'] }}</p>
                                    @if (filled($addOn['description']))
                                        <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-[#6f7890]">{{ $addOn['description'] }}</p>
                                    @endif
                                    <p class="mt-2 text-xs font-extrabold text-[#2f6bff]">{{ $addOn['price'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section id="disponibilites" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-extrabold text-[#151821]">Disponibilités</h2>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">Choisissez une date libre pour démarrer votre demande de réservation.</p>
                        </div>
                        <div class="flex flex-wrap gap-3 text-xs font-extrabold text-[#52617b]">
                            <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full bg-white ring-1 ring-[#2f6bff]"></span>Libre</span>
                            <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full bg-[#e5e9f2]"></span>Indisponible</span>
                        </div>
                    </div>
                    <div class="mt-4 rounded-2xl border border-[#dce6f7] bg-white p-4 shadow-sm" data-venue-calendar data-blocked-dates-url="{{ route('venues.blocked-dates', $venue->slug) }}" data-reservation-url="{{ route('venues.reservation', $venue->slug) }}">
                        <div class="flex items-center justify-between gap-3">
                            <button type="button" data-calendar-prev class="grid size-9 place-items-center rounded-full border border-[#dce6f7] text-[#2f6bff] transition hover:bg-[#eef4ff] disabled:opacity-30" aria-label="Mois précédent">‹</button>
                            <p data-calendar-title class="text-sm font-extrabold capitalize text-[#151821]"></p>
                            <button type="button" data-calendar-next class="grid size-9 place-items-center rounded-full border border-[#dce6f7] text-[#2f6bff] transition hover:bg-[#eef4ff] disabled:opacity-30" aria-label="Mois suivant">›</button>
                        </div>
                        <div class="mt-3 grid grid-cols-7 gap-1 text-center text-[11px] font-extrabold uppercase text-[#8a94aa]">
                            @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $weekday)
                                <span>{{ $weekday }}</span>
                            @endforeach
                        </div>
                        <div data-calendar-grid class="mt-1 grid grid-cols-7 gap-1"></div>
                        <p data-calendar-status class="mt-3 text-xs font-semibold text-[#6f7890]">Chargement des disponibilités…</p>
                    </div>
                </section>

                <section id="lieu" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Emplacement</h2>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-[#dce6f7]">
                        @if ($mapEmbedUrl)
                            <div class="relative">
                                <iframe src="{{ $mapEmbedUrl }}" title="Zone approximative de {{ $title }}" loading="lazy" class="h-72 w-full" referrerpolicy="no-referrer"></iframe>
                                <span class="pointer-events-none absolute left-1/2 top-1/2 size-28 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-[#2f6bff]/60 bg-[#2f6bff]/15"></span>
                            </div>
                            <p class="border-t border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-xs font-semibold text-[#4d5872]">{{ $locationLabel }} · Zone approximative. L’adresse exacte vous est communiquée une fois la réservation confirmée.</p>
                        @else
                            <div class="flex h-56 items-center justify-center bg-[linear-gradient(135deg,#bfe9ff,#dce7ff,#f2f7ff)] px-5 text-center">
                                <div>
                                    <p class="text-2xl font-extrabold text-[#2f6bff]">{{ $locationLabel }}</p>
                                    <p class="mt-2 text-sm font-semibold text-[#4d5872]">L’adresse exacte vous est communiquée une fois la réservation confirmée.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    @if ($openingHours)
                        <p class="mt-3 text-sm font-semibold text-[#4d5872]">Horaires d’accueil des événements : {{ $openingHours }}.</p>
                    @endif
                    @if (filled($locationNote))
                        <p class="mt-3 text-sm font-semibold leading-6 text-[#4d5872]">{{ $locationNote }}</p>
                    @endif
                </section>

                @if ($policies->isNotEmpty() || $faqs->isNotEmpty())
                    <section id="conditions" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                        <h2 class="text-xl font-extrabold text-[#151821]">À savoir avant de réserver</h2>
                        @if ($policies->isNotEmpty())
                            <div class="mt-4 divide-y divide-[#edf0f7] rounded-2xl border border-[#edf0f7]">
                                @foreach ($policies as $policy)
                                    <details class="group p-4" @if ($loop->first) open @endif>
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-extrabold text-[#151821]">
                                            <span>{{ $policy->title }}@if ($policy->summary) <span class="font-semibold text-[#6f7890]">· {{ $policy->summary }}</span>@endif</span>
                                            <span class="text-[#8a94aa] group-open:rotate-180">⌄</span>
                                        </summary>
                                        <p class="mt-2 text-sm font-medium text-[#6f7890]">{{ $policy->content }}</p>
                                    </details>
                                @endforeach
                            </div>
                        @endif

                        @if ($faqs->isNotEmpty())
                            <h3 class="mt-7 text-lg font-extrabold text-[#151821]">Questions fréquentes</h3>
                            <div class="mt-2 divide-y divide-[#edf0f7]">
                                @foreach ($faqs as $faq)
                                    <details class="group py-4">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-extrabold text-[#151821]">
                                            {{ $faq->question }}
                                            <span class="text-[#8a94aa] group-open:rotate-180">⌄</span>
                                        </summary>
                                        <p class="mt-2 text-sm font-medium text-[#6f7890]">{{ $faq->answer }}</p>
                                    </details>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif

                <section id="avis" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-extrabold text-[#151821]">Avis clients</h2>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">Seuls les clients ayant réservé cet espace peuvent publier un avis.</p>
                        </div>
                        <span class="rounded-full bg-[#eaf1ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">
                            @if ($publicRating)
                                {{ $publicRating }} ★ ·
                            @endif
                            {{ $approvedReviews->count() }} avis publié{{ $approvedReviews->count() > 1 ? 's' : '' }}
                        </span>
                    </div>

                    @if (session('review_status'))
                        <div class="mt-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">
                            {{ session('review_status') }}
                        </div>
                    @endif

                    @if ($errors->has('review'))
                        <div class="mt-4 rounded-2xl border border-[#ffd0d0] bg-[#fff6f6] px-4 py-3 text-sm font-extrabold text-[#b42318]">
                            {{ $errors->first('review') }}
                        </div>
                    @endif

                    <div class="mt-4 grid gap-4">
                        @forelse ($approvedReviews as $review)
                            <article class="rounded-2xl border border-[#dce6f7] bg-white p-4 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-extrabold text-[#151821]">{{ $review->client?->name ?? 'Client BAOBAA' }}</p>
                                        <p class="mt-1 text-xs font-bold text-[#8a94aa]">{{ $review->approved_at?->format('d/m/Y') }}</p>
                                    </div>
                                    <span class="rounded-full bg-[#edf4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $review->rating }} ★</span>
                                </div>
                                @if ($review->title)
                                    <h3 class="mt-3 text-sm font-extrabold text-[#151821]">{{ $review->title }}</h3>
                                @endif
                                <p class="mt-2 text-sm font-medium leading-6 text-[#4d5872]">{{ $review->comment }}</p>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold leading-6 text-[#6f7890]">
                                Aucun avis publié pour le moment. Les avis des clients apparaîtront ici après leur événement.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5 rounded-2xl border border-[#dce6f7] bg-white p-5 shadow-sm">
                        @guest
                            <p class="text-sm font-extrabold text-[#151821]">Vous avez réservé cet espace ? Connectez-vous pour laisser un avis.</p>
                            <a href="{{ route('portal.login', ['portal' => 'client']) }}" class="mt-3 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Connexion client</a>
                        @else
                            @if ($existingReview)
                                <p class="text-sm font-extrabold text-[#151821]">Votre avis a déjà été envoyé.</p>
                                <p class="mt-1 text-xs font-semibold text-[#6f7890]">Statut : {{ $existingReview->status === 'approved' ? 'publié' : 'en attente de validation' }}</p>
                            @elseif ($canReview)
                                <form method="POST" action="{{ route('venues.reviews.store', $venue) }}" class="grid gap-3">
                                    @csrf
                                    <div class="grid gap-3 sm:grid-cols-[140px_1fr]">
                                        <label class="rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                            <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Note</span>
                                            <select name="rating" class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                                @foreach ([5, 4, 3, 2, 1] as $ratingOption)
                                                    <option value="{{ $ratingOption }}" @selected((int) old('rating', 5) === $ratingOption)>{{ $ratingOption }} ★</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                            <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Titre</span>
                                            <input name="title" value="{{ old('title') }}" maxlength="120" placeholder="Une expérience claire et professionnelle" class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none placeholder:text-[#a0a8b8]">
                                        </label>
                                    </div>
                                    <label class="rounded-xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                        <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Commentaire</span>
                                        <textarea name="comment" rows="4" minlength="10" maxlength="1200" placeholder="Partagez votre retour après votre événement..." class="mt-1 w-full resize-none bg-transparent text-sm font-semibold leading-6 text-[#151821] outline-none placeholder:text-[#a0a8b8]">{{ old('comment') }}</textarea>
                                    </label>
                                    <button class="justify-self-start rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Envoyer mon avis</button>
                                </form>
                            @else
                                <p class="text-sm font-extrabold text-[#151821]">Avis réservé aux clients ayant déjà réservé cet espace.</p>
                                <p class="mt-1 text-xs font-semibold leading-5 text-[#6f7890]">Après une réservation confirmée ou terminée, le formulaire d’avis sera disponible ici.</p>
                            @endif
                        @endguest
                    </div>
                </section>

                @if ($similarVenues->isNotEmpty())
                    <section class="border-t border-[#edf0f7] pt-7">
                        <h2 class="text-xl font-extrabold text-[#151821]">Espaces similaires</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($similarVenues as $similarVenue)
                                <a href="{{ route('venues.show', $similarVenue->slug) }}" class="group overflow-hidden rounded-2xl border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-[#173e7a]/10">
                                    <div class="relative overflow-hidden">
                                        <img src="{{ $similarVenue->cover_image_url }}" alt="{{ $similarVenue->name }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-40 w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                                    </div>
                                    <div class="p-4">
                                        <p class="line-clamp-2 text-sm font-extrabold text-[#151821]">{{ $similarVenue->name }}</p>
                                        <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ collect([$similarVenue->district, $similarVenue->city])->filter()->implode(', ') }} · {{ $similarVenue->min_capacity }}-{{ $similarVenue->max_capacity }} invités</p>
                                        <div class="mt-3 flex items-center justify-between border-t border-[#edf2fb] pt-3">
                                            <p class="text-xs font-extrabold text-[#151821]">Dès {{ number_format((int) $similarVenue->starting_price, 0, ',', ' ') }} {{ $similarVenue->currency }}</p>
                                            <span class="text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">Voir →</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>

            <aside id="reserver" class="scroll-mt-32 lg:sticky lg:top-36 lg:self-start">
                <div class="overflow-hidden rounded-[26px] bg-white shadow-2xl shadow-[#173e7a]/12 ring-1 ring-[#dce6f7]">
                    <div class="border-b border-[#edf2fb] bg-gradient-to-br from-white via-[#f7faff] to-[#eef4ff] p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">À partir de</p>
                                <p class="text-2xl font-extrabold tracking-[-0.035em] text-[#151821]">{{ $price }}</p>
                                @if ($minimumDuration)
                                    <p class="mt-0.5 text-[11px] font-bold text-[#6f7890]">{{ $minimumDuration }}</p>
                                @endif
                            </div>
                            @if ($publicRating)
                                <span class="rounded-full bg-[#eaf1ff] px-2.5 py-0.5 text-[11px] font-extrabold text-[#2f6bff]">{{ $publicRating }} ★ ({{ $publicReviewsCount }})</span>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-[10px] font-extrabold text-[#52617b]">
                            <span class="rounded-full bg-white px-2 py-1.5 ring-1 ring-[#dce6f7]">1. Demande</span>
                            <span class="rounded-full bg-white px-2 py-1.5 ring-1 ring-[#dce6f7]">2. Proforma</span>
                            <span class="rounded-full bg-white px-2 py-1.5 ring-1 ring-[#dce6f7]">3. Acompte</span>
                        </div>
                    </div>

                    @if (session('booking_status'))
                        <div class="mx-4 mt-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-xs font-extrabold leading-5 text-[#2f6bff]">
                            {{ session('booking_status') }}
                        </div>
                    @endif

                    <div class="space-y-4 p-5">
                        <a href="{{ route('venues.reservation', $venue->slug) }}" class="flex w-full items-center justify-center gap-2 rounded-[18px] bg-[#2f6bff] px-4 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">
                            Réserver cet espace
                            <span aria-hidden="true">→</span>
                        </a>

                        <a href="{{ route('event-services.index') }}" class="flex w-full items-center justify-center gap-2 rounded-[18px] border border-[#dce6f7] bg-[#f7faff] px-4 py-3.5 text-sm font-extrabold text-[#2f6bff] transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef4ff]">
                            Réserver uniquement un service événementiel
                        </a>

                        <div class="rounded-[18px] bg-[#f7faff] p-4 ring-1 ring-[#e2eaf8]">
                            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#2f6bff]">Comment se passe le paiement ?</p>
                            <ul class="mt-3 space-y-2 text-xs font-semibold leading-5 text-[#52617b]">
                                <li><strong class="text-[#151821]">Prix affiché :</strong> {{ $price }}, hors options et services additionnels.</li>
                                <li><strong class="text-[#151821]">Acompte :</strong> {{ $depositDescription }}@if ($reservation) Exemple sur le prix de départ : {{ $reservation }}.@endif</li>
                                <li><strong class="text-[#151821]">Prix final :</strong> confirmé sur la proforma après échange avec le propriétaire.</li>
                            </ul>
                        </div>

                        <div class="rounded-[18px] bg-[#f7faff] p-4 ring-1 ring-[#e2eaf8]">
                            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#2f6bff]">Moyens de paiement acceptés</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($acceptedPaymentMethods as $paymentMethod)
                                    <span class="rounded-full bg-white px-3 py-1 text-[11px] font-extrabold text-[#52617b] ring-1 ring-[#dce6f7]">{{ $paymentMethod }}</span>
                                @endforeach
                            </div>
                        </div>

                        @guest
                            <a href="{{ route('portal.login', ['portal' => 'client', 'redirect' => url()->current()]) }}" class="flex w-full justify-center rounded-[16px] border border-[#dce6f7] bg-white px-4 py-3 text-xs font-extrabold text-[#151821] transition hover:border-[#2f6bff] hover:text-[#2f6bff]">Me connecter pour démarrer</a>
                            <p class="text-center text-[11px] font-bold leading-5 text-[#6f7890]">Connexion client obligatoire avant la proforma et le paiement de l’acompte.</p>
                        @endguest
                    </div>
                </div>
            </aside>
        </section>

        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#dce6f7] bg-white/95 px-4 py-3 shadow-[0_-12px_40px_rgba(23,62,122,0.12)] backdrop-blur lg:hidden" data-mobile-booking-bar>
            <div class="mx-auto flex max-w-xl items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">À partir de</p>
                    <p class="truncate text-base font-extrabold text-[#151821]">{{ $price }}</p>
                </div>
                <a href="{{ route('venues.reservation', $venue->slug) }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-[#2f6bff] px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:bg-[#2258df]">
                    Réserver
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>

        <x-navigation.public-footer />

        <div id="venue-gallery-modal" class="fixed inset-0 z-[90] hidden bg-[#071225]/95 p-4 text-white" aria-hidden="true">
            <button type="button" id="gallery-close" class="absolute right-5 top-5 z-20 grid size-11 place-items-center rounded-full bg-white/10 text-2xl font-bold backdrop-blur transition hover:bg-white/20" aria-label="Fermer la galerie">&times;</button>
            <button type="button" id="gallery-prev" class="absolute left-3 top-1/2 z-20 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-3xl font-bold backdrop-blur transition hover:bg-white/20 sm:left-5 sm:size-12" aria-label="Image précédente">‹</button>
            <button type="button" id="gallery-next" class="absolute right-3 top-1/2 z-20 grid size-11 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-3xl font-bold backdrop-blur transition hover:bg-white/20 sm:right-5 sm:size-12" aria-label="Image suivante">›</button>
            <div id="gallery-counter" class="absolute left-5 top-5 z-20 rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold text-white backdrop-blur"></div>

            <div class="mx-auto flex h-full max-w-6xl flex-col justify-center gap-4">
                <div class="flex min-h-0 flex-1 items-center justify-center">
                    <img id="gallery-active-image" src="{{ $galleryImages[0] ?? \App\Models\Venue::placeholderImageUrl() }}" alt="{{ $title }}" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="max-h-[76vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl">
                </div>
                <div class="mx-auto flex max-w-full gap-3 overflow-x-auto rounded-2xl bg-white/8 p-3 baobaa-scrollbar-none">
                    @foreach ($galleryImages as $imageIndex => $image)
                        <button type="button" data-gallery-thumb="{{ $imageIndex }}" class="h-20 w-28 shrink-0 overflow-hidden rounded-xl ring-2 ring-transparent transition">
                            <img src="{{ $image }}" alt="{{ $title }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="venue-action-toast" class="fixed bottom-5 left-1/2 z-[60] hidden -translate-x-1/2 rounded-full bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white shadow-2xl shadow-[#07152f]/25" role="status" aria-live="polite"></div>

        <script>
            (() => {
                const images = @json($galleryImages);
                const modal = document.getElementById('venue-gallery-modal');
                const activeImage = document.getElementById('gallery-active-image');
                const shareButton = document.getElementById('venue-share-button');
                const saveButton = document.getElementById('venue-save-button');
                const actionToast = document.getElementById('venue-action-toast');
                const thumbButtons = document.querySelectorAll('[data-gallery-thumb]');
                const tabLinks = [...document.querySelectorAll('[data-tab-link]')];
                const sections = tabLinks.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
                const galleryPrevBtn = document.getElementById('gallery-prev');
                const galleryNextBtn = document.getElementById('gallery-next');
                const galleryCounter = document.getElementById('gallery-counter');
                let activeIndex = 0;
                let toastTimer;

                const showToast = (message) => {
                    clearTimeout(toastTimer);
                    actionToast.textContent = message;
                    actionToast.classList.remove('hidden');
                    toastTimer = setTimeout(() => actionToast.classList.add('hidden'), 2600);
                };

                const setActiveImage = (index) => {
                    if (! activeImage || images.length === 0) {
                        return;
                    }

                    activeIndex = (index + images.length) % images.length;
                    activeImage.src = images[activeIndex];
                    activeImage.alt = `${@json($title)} - photo ${activeIndex + 1}`;

                    if (galleryCounter) {
                        galleryCounter.textContent = `${activeIndex + 1} / ${images.length}`;
                    }

                    thumbButtons.forEach((button) => {
                        const selected = Number(button.dataset.galleryThumb) === activeIndex;
                        button.classList.toggle('ring-white', selected);
                        button.classList.toggle('opacity-60', ! selected);

                        if (selected) {
                            button.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                        }
                    });

                    const showControls = images.length > 1;
                    [galleryPrevBtn, galleryNextBtn].forEach((button) => {
                        button?.classList.toggle('invisible', ! showControls);
                        button?.classList.toggle('pointer-events-none', ! showControls);
                    });
                };

                const openGallery = (index) => {
                    if (! modal || images.length === 0) {
                        return;
                    }

                    setActiveImage(index);
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                };

                const closeGallery = () => {
                    if (! modal) {
                        return;
                    }

                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                };

                const savedVenues = () => {
                    try {
                        return JSON.parse(localStorage.getItem('baobaa_saved_venues') || '[]');
                    } catch (error) {
                        return [];
                    }
                };

                const writeSavedVenues = (venues) => {
                    localStorage.setItem('baobaa_saved_venues', JSON.stringify(venues));
                };

                const refreshSaveButton = () => {
                    if (! saveButton) {
                        return;
                    }

                    const saved = savedVenues().some((venue) => venue.key === saveButton.dataset.saveKey);
                    saveButton.textContent = saved ? 'Sauvegardé' : 'Sauvegarder';
                    saveButton.classList.toggle('bg-[#2f6bff]', saved);
                    saveButton.classList.toggle('text-white', saved);
                    saveButton.classList.toggle('border-[#2f6bff]', saved);
                };

                shareButton?.addEventListener('click', async () => {
                    const sharePayload = {
                        title: shareButton.dataset.shareTitle,
                        text: shareButton.dataset.shareText?.slice(0, 140),
                        url: shareButton.dataset.shareUrl,
                    };

                    try {
                        if (navigator.share) {
                            await navigator.share(sharePayload);
                            showToast('Lien partagé.');

                            return;
                        }

                        await navigator.clipboard.writeText(sharePayload.url);
                        showToast('Lien copié dans le presse-papiers.');
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showToast('Impossible de partager pour le moment.');
                        }
                    }
                });

                saveButton?.addEventListener('click', () => {
                    const saved = savedVenues();
                    const alreadySaved = saved.some((venue) => venue.key === saveButton.dataset.saveKey);

                    if (alreadySaved) {
                        writeSavedVenues(saved.filter((venue) => venue.key !== saveButton.dataset.saveKey));
                        refreshSaveButton();
                        showToast('Espace retiré des sauvegardes.');

                        return;
                    }

                    saved.push({
                        key: saveButton.dataset.saveKey,
                        title: saveButton.dataset.saveTitle,
                        url: saveButton.dataset.saveUrl,
                        savedAt: new Date().toISOString(),
                    });
                    writeSavedVenues(saved);
                    refreshSaveButton();
                    showToast('Espace sauvegardé.');
                });

                document.addEventListener('click', (event) => {
                    const galleryToggle = event.target.closest('[data-gallery-index]');

                    if (galleryToggle) {
                        event.preventDefault();
                        openGallery(Number(galleryToggle.dataset.galleryIndex));

                        return;
                    }

                    const thumb = event.target.closest('[data-gallery-thumb]');

                    if (thumb) {
                        event.preventDefault();
                        setActiveImage(Number(thumb.dataset.galleryThumb));
                    }
                });

                document.getElementById('gallery-close')?.addEventListener('click', closeGallery);
                galleryPrevBtn?.addEventListener('click', () => setActiveImage(activeIndex - 1));
                galleryNextBtn?.addEventListener('click', () => setActiveImage(activeIndex + 1));
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeGallery();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (! modal || modal.classList.contains('hidden')) {
                        return;
                    }

                    if (event.key === 'Escape') closeGallery();
                    if (event.key === 'ArrowLeft') setActiveImage(activeIndex - 1);
                    if (event.key === 'ArrowRight') setActiveImage(activeIndex + 1);
                });

                const activateTab = (activeSection, shouldScrollTab = false) => {
                    if (! activeSection) {
                        return;
                    }

                    tabLinks.forEach((link) => {
                        const active = link.getAttribute('href') === `#${activeSection.id}`;

                        link.classList.toggle('bg-[#2f6bff]', active);
                        link.classList.toggle('text-white', active);
                        link.classList.toggle('shadow-sm', active);
                        link.classList.toggle('text-[#6f7890]', ! active);
                        link.setAttribute('aria-current', active ? 'true' : 'false');

                        if (active && shouldScrollTab) {
                            link.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                        }
                    });
                };

                const setActiveTabFromScroll = () => {
                    const activationLine = window.innerHeight * 0.34;
                    const current = sections.reduce((activeSection, section) => (
                        section.getBoundingClientRect().top <= activationLine ? section : activeSection
                    ), sections[0]);

                    activateTab(current);
                };

                tabLinks.forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();

                        const target = document.querySelector(link.getAttribute('href'));

                        if (! target) {
                            return;
                        }

                        const top = target.getBoundingClientRect().top + window.scrollY - 128;

                        window.scrollTo({ top, behavior: 'smooth' });
                        history.replaceState(null, '', link.getAttribute('href'));
                        activateTab(target, true);
                    });
                });

                const calendarRoot = document.querySelector('[data-venue-calendar]');

                if (calendarRoot) {
                    const grid = calendarRoot.querySelector('[data-calendar-grid]');
                    const titleElement = calendarRoot.querySelector('[data-calendar-title]');
                    const statusElement = calendarRoot.querySelector('[data-calendar-status]');
                    const previousMonthButton = calendarRoot.querySelector('[data-calendar-prev]');
                    const nextMonthButton = calendarRoot.querySelector('[data-calendar-next]');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const firstMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() + 5, 1);
                    const monthFormatter = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' });
                    let displayedMonth = new Date(firstMonth);
                    let blockedDates = new Set();

                    const isoDate = (date) => [
                        date.getFullYear(),
                        String(date.getMonth() + 1).padStart(2, '0'),
                        String(date.getDate()).padStart(2, '0'),
                    ].join('-');

                    const renderCalendar = () => {
                        grid.innerHTML = '';
                        titleElement.textContent = monthFormatter.format(displayedMonth);
                        previousMonthButton.disabled = displayedMonth <= firstMonth;
                        nextMonthButton.disabled = displayedMonth >= lastMonth;

                        const leadingDays = (displayedMonth.getDay() + 6) % 7;
                        const daysInMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 0).getDate();

                        for (let index = 0; index < leadingDays; index++) {
                            grid.appendChild(document.createElement('span'));
                        }

                        for (let day = 1; day <= daysInMonth; day++) {
                            const date = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth(), day);
                            const value = isoDate(date);
                            const isUnavailable = date < today || blockedDates.has(value);
                            const cell = document.createElement(isUnavailable ? 'span' : 'a');

                            cell.textContent = String(day);
                            cell.className = isUnavailable
                                ? 'grid h-10 place-items-center rounded-xl bg-[#f1f3f8] text-sm font-bold text-[#a3abbd] line-through'
                                : 'grid h-10 place-items-center rounded-xl bg-white text-sm font-extrabold text-[#151821] ring-1 ring-[#cfdcf7] transition hover:bg-[#2f6bff] hover:text-white';

                            if (! isUnavailable) {
                                cell.href = `${calendarRoot.dataset.reservationUrl}?start_date=${value}`;
                                cell.setAttribute('aria-label', `Réserver le ${date.toLocaleDateString('fr-FR', { dateStyle: 'full' })}`);
                            }

                            grid.appendChild(cell);
                        }
                    };

                    previousMonthButton.addEventListener('click', () => {
                        displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() - 1, 1);
                        renderCalendar();
                    });
                    nextMonthButton.addEventListener('click', () => {
                        displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 1);
                        renderCalendar();
                    });

                    renderCalendar();

                    const lastDay = new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0);
                    fetch(`${calendarRoot.dataset.blockedDatesUrl}?from=${isoDate(today)}&to=${isoDate(lastDay)}`, { headers: { Accept: 'application/json' } })
                        .then((response) => response.ok ? response.json() : Promise.reject())
                        .then((payload) => {
                            blockedDates = new Set(payload.blocked_dates ?? []);
                            statusElement.textContent = 'Les dates grisées sont déjà réservées ou bloquées par le propriétaire.';
                            renderCalendar();
                        })
                        .catch(() => {
                            statusElement.textContent = 'Impossible de charger les disponibilités. Le propriétaire confirmera la date lors de votre demande.';
                        });
                }

                setActiveTabFromScroll();
                refreshSaveButton();
                window.addEventListener('scroll', setActiveTabFromScroll, { passive: true });
                window.addEventListener('resize', setActiveTabFromScroll, { passive: true });
            })();
        </script>
    </main>
</x-layouts.baobaa>
