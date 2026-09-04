@php
    $categoryIcon = function (?string $icon): string {
        $icons = [
            'sparkles' => '<path d="M12 3l1.4 4.4L18 9l-4.6 1.6L12 15l-1.4-4.4L6 9l4.6-1.6L12 3Z"/><path d="M5 15l.8 2.2L8 18l-2.2.8L5 21l-.8-2.2L2 18l2.2-.8L5 15Z"/>',
            'building' => '<path d="M4 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/><path d="M8 7h2M12 7h2M8 11h2M12 11h2M8 15h2M12 15h2M20 9v12M2 21h20"/>',
            'presentation' => '<path d="M3 4h18v12H3z"/><path d="M8 20h8M12 16v4M8 10l2 2 4-5 2 3"/>',
            'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
            'music' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
            'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
            'leaf' => '<path d="M4 20c7-1 13-7 16-16-9 2-15 8-16 16Z"/><path d="M4 20c4-5 8-8 13-10"/>',
            'hotel' => '<path d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16"/><path d="M8 21v-5h8v5M8 7h.01M12 7h.01M16 7h.01M8 11h.01M12 11h.01M16 11h.01"/>',
            'utensils' => '<path d="M4 3v8a3 3 0 0 0 6 0V3M7 3v18M15 3v18M15 3c3 2 5 5 5 9h-5"/>',
            'palette' => '<path d="M12 3a9 9 0 0 0 0 18h1.5a2 2 0 0 0 0-4H12a2 2 0 0 1 0-4h1a8 8 0 0 0 8-8c0-1.1-.9-2-2-2h-7Z"/><circle cx="7.5" cy="10.5" r=".5"/><circle cx="10.5" cy="7.5" r=".5"/><circle cx="14.5" cy="7.5" r=".5"/>',
        ];

        return $icons[$icon ?? ''] ?? $icons['building'];
    };

    $categoryColors = ['#2f6bff', '#8b6f47', '#6f7d95', '#f06292', '#2f3348', '#f2a900', '#7c99c8', '#9b5de5', '#00a896', '#ff8fab'];

    $mapVenueCards = fn ($venues) => $venues->map(fn ($venue) => [
        'title' => $venue->name,
        'slug' => $venue->slug,
        'category' => $venue->category?->name ?? 'Espace événementiel',
        'city' => trim(($venue->district ? $venue->district.', ' : '').$venue->city, ', '),
        'district' => $venue->district,
        'guests' => $venue->min_capacity.'-'.$venue->max_capacity.' invités',
        'price' => 'dès '.number_format($venue->starting_price, 0, ',', ' ').' '.$venue->currency,
        'image' => $venue->media->sortBy('sort_order')->first()?->signed_url
            ?? 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80',
    ])->values();

    $popularListings = $mapVenueCards($featuredVenues);
    $categoryListings = $mapVenueCards($categoryCarouselVenues);
    $cityListings = $mapVenueCards($cityCarouselVenues);
    $districtListings = $mapVenueCards($districtCarouselVenues);
@endphp

<x-layouts.baobaa title="BAOBAA - Réservez un espace événementiel">
    <style>
        .baobaa-venue-carousel-grid {
            grid-auto-flow: column;
            grid-auto-columns: min(76vw, 292px);
        }

        @media (min-width: 768px) {
            .baobaa-venue-carousel-grid {
                grid-auto-columns: calc((100% - 80px) / 3.35);
            }
        }

        @media (min-width: 1024px) {
            .baobaa-venue-carousel-grid {
                grid-auto-columns: calc((100% - 80px) / 4.35);
            }
        }
    </style>

    <main class="min-h-screen overflow-hidden bg-[#eef3ff]">
        <section class="relative overflow-hidden px-5 pb-8 pt-5 sm:px-8 lg:px-14">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f4f7ff_0%,#fbfdff_43%,#eef4ff_68%,#f4f0ff_100%)]"></div>
            <div class="absolute inset-0 opacity-[0.38] [background-image:linear-gradient(rgba(47,107,255,.13)_1px,transparent_1px),linear-gradient(90deg,rgba(47,107,255,.13)_1px,transparent_1px)] [background-size:72px_72px]"></div>
            <div class="absolute inset-x-0 top-[82px] h-px bg-gradient-to-r from-transparent via-[#2f6bff]/18 to-transparent"></div>
            <div class="absolute left-0 right-0 top-[34%] h-[260px] -skew-y-6 bg-gradient-to-r from-[#2f6bff]/0 via-[#2f6bff]/8 to-[#9b5de5]/0 blur-2xl"></div>
            <div class="animate-baobaa-hero-drift absolute -left-[12%] top-[45%] h-[360px] w-[72%] rounded-[50%] bg-[#dbe6ff]/70 blur-3xl"></div>
            <div class="animate-baobaa-hero-drift absolute -right-[12%] top-[38%] h-[380px] w-[74%] rounded-[50%] bg-[#e7e4ff]/70 blur-3xl" style="animation-delay: -7s"></div>

            <header class="relative z-[120] mx-auto flex max-w-[1680px] items-center justify-between">
                <a href="{{ url('/') }}" class="flex shrink-0 items-center text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-12 w-auto max-w-[180px] rounded-2xl bg-white/80 object-contain p-1 shadow-sm ring-1 ring-[#dbe3f8] sm:h-14" loading="eager">
                </a>

                <x-navigation.public-menu active="explore" class="border-white/80 bg-white/72 shadow-sm backdrop-blur" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('owner.register') }}" class="hidden rounded-full bg-[#17191f] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#17191f]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] md:inline-flex">Devenir partenaire</a>
                    @endguest
                    <x-navigation.account-menu class="bg-white/82 text-[#17191f] ring-white/80 backdrop-blur" />
                    <a href="#" aria-label="Panier" class="grid size-11 place-items-center rounded-full bg-white/82 text-[#17191f] shadow-sm ring-1 ring-white/80 backdrop-blur transition hover:bg-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h15l-2 9H8L6 3H3"/><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/></svg>
                    </a>
                </div>
            </header>

            <div class="relative z-[70] mx-auto flex max-w-[1450px] flex-col items-center pt-10 text-center sm:pt-14 lg:pt-16">
                <div class="pointer-events-none absolute left-0 top-28 hidden w-[210px] text-left xl:block">
                    <div class="animate-baobaa-float rounded-[1.65rem] border border-white/75 bg-white/78 p-4 shadow-[0_20px_65px_rgba(23,62,122,.12)] ring-1 ring-[#dce6f7]/70 backdrop-blur-xl">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Réservation</p>
                        <p class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">sécurisée</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[#dbe7ff]">
                            <span class="animate-baobaa-line-flow block h-full w-2/3 rounded-full bg-[#2f6bff]"></span>
                        </div>
                    </div>
                </div>

                <div class="pointer-events-none absolute right-0 top-36 hidden w-[230px] text-left xl:block">
                    <div class="animate-baobaa-float rounded-[1.65rem] border border-white/75 bg-white/78 p-4 shadow-[0_20px_65px_rgba(23,62,122,.12)] ring-1 ring-[#dce6f7]/70 backdrop-blur-xl" style="animation-delay: -2.5s">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d8aa7]">Disponibilités</p>
                        <p class="mt-2 text-sm font-bold leading-5 text-[#07152f]">Comparez les meilleurs créneaux avant de réserver.</p>
                        <div class="mt-4 grid grid-cols-4 gap-1.5">
                            @foreach ([65, 88, 54, 78] as $height)
                                <span class="block rounded-full bg-[#edf4ff] p-1">
                                    <span class="block rounded-full bg-[#2f6bff]" style="height: {{ $height / 4 }}px"></span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="animate-baobaa-fade-up relative inline-flex max-w-full items-center gap-2 overflow-hidden rounded-full border border-white/80 bg-white/86 px-4 py-2 text-xs font-extrabold text-[#2f6bff] shadow-[0_10px_30px_rgba(25,64,150,.12)] ring-1 ring-[#cfdaf5] backdrop-blur sm:text-sm">
                    <span class="animate-baobaa-shimmer absolute inset-y-0 w-16 -skew-x-12 bg-white/70"></span>
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>2 000+ espaces vérifiés</span>
                    <span class="text-[#9eb4ed]">&middot;</span>
                    <span>40+ villes servies</span>
                    <span class="text-[#9eb4ed]">&middot;</span>
                    <span>5.0 ★</span>
                </div>

                <h1 class="animate-baobaa-fade-up mt-5 max-w-[920px] text-[36px] font-extrabold leading-[1.02] tracking-[-0.05em] text-[#202027] sm:text-[58px] lg:text-[72px]" style="animation-delay: .08s">
                    Réservez le lieu parfait pour votre
                    <span class="relative inline-block text-[#2f6bff]">
                        événement
                        <span class="absolute -bottom-1 left-1/2 h-1.5 w-[94%] -translate-x-1/2 rounded-full bg-[#9ab7ff]"></span>
                        <span class="animate-baobaa-line-flow absolute -bottom-1 left-[29%] h-1.5 w-[42%] rounded-full bg-white/85"></span>
                    </span>
                </h1>

                <p class="animate-baobaa-fade-up mt-4 max-w-3xl text-base font-semibold leading-6 text-[#626b7e] sm:text-lg sm:leading-7" style="animation-delay: .16s">
                    Des salles vérifiées, des prix clairs et une réservation sécurisée pour transformer chaque idée en événement mémorable.
                </p>

                <div class="animate-baobaa-fade-up mt-5 flex flex-wrap items-center justify-center gap-2 text-xs font-extrabold text-[#506079]" style="animation-delay: .2s">
                    <span class="rounded-full bg-white/75 px-3 py-1.5 shadow-sm ring-1 ring-white/80">Devis instantané</span>
                    <span class="rounded-full bg-white/75 px-3 py-1.5 shadow-sm ring-1 ring-white/80">Acompte protégé</span>
                    <span class="rounded-full bg-white/75 px-3 py-1.5 shadow-sm ring-1 ring-white/80">Partenaires vérifiés</span>
                </div>

                <form method="GET" action="{{ route('venues.index') }}" class="animate-baobaa-fade-up animate-baobaa-soft-breathe relative z-[80] mt-5 w-full max-w-[1220px] rounded-[2rem] border border-white/80 bg-white/94 px-3 py-3 shadow-[0_18px_55px_rgba(18,30,75,.15)] ring-1 ring-[#cfd3df] backdrop-blur-xl" style="animation-delay: .24s" data-home-search-form>
                    <div class="grid items-center gap-0 lg:grid-cols-[1.1fr_.85fr_.95fr_.9fr_auto]">
                        <label class="group border-b border-[#dedede] px-2 py-2.5 text-left transition lg:border-b-0 lg:border-r lg:px-5">
                            <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Quoi</span>
                            <input type="text" name="q" autocomplete="off" data-search-input placeholder="Essayez &quot;salle mariage&quot;" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:text-[#7d7f86] sm:text-lg">
                        </label>
                        <label class="relative border-b border-[#dedede] px-2 py-2.5 text-left lg:border-b-0 lg:border-r lg:px-5" data-city-autocomplete>
                            <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Où</span>
                            <input type="text" name="city" autocomplete="off" data-city-input placeholder="Abidjan" class="mt-1 w-full bg-transparent text-base font-extrabold text-[#25262b] outline-none placeholder:text-[#25262b] sm:text-lg">
                            <div data-city-list class="absolute left-2 right-2 top-[calc(100%+8px)] z-[90] hidden rounded-2xl border border-[#dce6f7] bg-white p-2 text-left shadow-2xl shadow-[#173e7a]/16 lg:left-4 lg:right-4"></div>
                        </label>
                        <div class="relative border-b border-[#dedede] px-2 py-2.5 text-left lg:border-b-0 lg:border-r lg:px-5" data-date-range>
                            <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Quand</span>
                            <button type="button" data-date-toggle class="mt-1 w-full bg-transparent text-left text-base font-semibold text-[#8a8c93] outline-none sm:text-lg">Début - fin</button>
                            <div data-date-panel class="absolute left-2 right-2 top-[calc(100%+8px)] z-[90] hidden rounded-3xl border border-[#dce6f7] bg-white p-4 shadow-2xl shadow-[#173e7a]/16 lg:left-4 lg:right-4 lg:min-w-[360px]">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="rounded-2xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                        <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Date début</span>
                                        <input type="date" name="start_date" data-start-date class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                    </label>
                                    <label class="rounded-2xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                        <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Date fin</span>
                                        <input type="date" name="end_date" data-end-date class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                    </label>
                                </div>
                                <button type="button" data-date-apply class="mt-3 w-full rounded-full bg-[#2f6bff] px-4 py-2.5 text-sm font-extrabold text-white">Valider les dates</button>
                            </div>
                        </div>
                        <div class="border-b border-[#dedede] px-2 py-2.5 text-left lg:border-b-0 lg:border-r lg:px-5">
                            <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Budget</span>
                            <div class="mt-1 grid grid-cols-2 gap-2">
                                <input type="number" min="1" name="min_price" placeholder="Min" class="w-full min-w-0 bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:text-[#8a8c93] sm:text-lg">
                                <input type="number" min="1" name="max_price" placeholder="Max" class="w-full min-w-0 bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:text-[#8a8c93] sm:text-lg">
                            </div>
                        </div>
                        <button class="mt-3 inline-flex items-center justify-center gap-3 overflow-hidden rounded-[1.6rem] bg-[#2f6bff] px-7 py-3.5 text-base font-extrabold text-white shadow-[0_12px_28px_rgba(47,107,255,.26)] transition hover:-translate-y-0.5 hover:bg-[#2258df] lg:mt-0 sm:px-8">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            Trouver un espace
                        </button>
                    </div>
                    <div data-search-results class="relative z-[85] mt-3 hidden rounded-3xl border border-[#dce6f7] bg-[#f8fbff] p-3 text-left shadow-2xl shadow-[#173e7a]/12"></div>
                </form>
            </div>

            <div id="categories" class="relative z-10 mx-auto mt-9 max-w-[1800px] overflow-hidden">
                <button type="button" data-category-prev class="absolute left-0 top-7 z-20 hidden size-10 place-items-center rounded-full bg-white text-[#2f6bff] shadow-lg ring-1 ring-[#d8dce9] transition hover:bg-[#eef3ff] md:grid" aria-label="Catégories précédentes">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" data-category-next class="absolute right-0 top-7 z-20 hidden size-10 place-items-center rounded-full bg-white text-[#2f6bff] shadow-lg ring-1 ring-[#d8dce9] transition hover:bg-[#eef3ff] md:grid" aria-label="Catégories suivantes">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>

                <div data-category-scroll class="baobaa-scrollbar-none overflow-x-auto scroll-smooth px-10 pb-3">
                    <div class="animate-baobaa-marquee flex w-max gap-7 hover:[animation-play-state:paused]">
                        @foreach ($categories->concat($categories) as $index => $category)
                            <a href="{{ route('venues.index', ['category' => $category->slug]) }}" class="group flex w-[92px] shrink-0 flex-col items-center gap-2 text-center">
                                <span class="grid size-14 place-items-center rounded-full bg-white text-[#2f6bff] shadow-[0_2px_10px_rgba(38,48,75,.12)] ring-1 ring-[#d8dce9] transition group-hover:-translate-y-1 group-hover:shadow-[0_8px_22px_rgba(47,107,255,.2)]">
                                    <span class="grid size-9 place-items-center rounded-xl" style="background-color: {{ $categoryColors[$index % count($categoryColors)] }}22; color: {{ $categoryColors[$index % count($categoryColors)] }}">
                                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $categoryIcon($category->icon ?? null) !!}</svg>
                                    </span>
                                </span>
                                <span class="line-clamp-2 min-h-9 text-[13px] font-extrabold leading-[1.15] text-[#333b4f] transition group-hover:text-[#2f6bff]">{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

        </section>

        <section id="espaces" class="mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:py-18" data-carousel-section>
            <div class="mb-8 flex flex-wrap items-end justify-between gap-5">
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#2f6bff]">Tendances actuelles</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.03em] text-[#07152f] sm:text-4xl">Annonces les plus populaires</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-carousel-prev class="grid size-10 place-items-center rounded-full border border-[#d8dce9] bg-white text-[#2f6bff] shadow-sm transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef3ff]" aria-label="Annonces précédentes">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" data-carousel-next class="grid size-10 place-items-center rounded-full border border-[#d8dce9] bg-white text-[#2f6bff] shadow-sm transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef3ff]" aria-label="Annonces suivantes">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                    <a href="{{ route('venues.index') }}" class="rounded-full border border-[#c9d8ef] bg-white px-5 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm transition hover:border-[#2f6bff]">Voir tous les espaces</a>
                </div>
            </div>

            <div class="relative">
                <div data-carousel class="baobaa-scrollbar-none overflow-x-auto scroll-smooth px-1 pb-4">
                    <div class="baobaa-venue-carousel-grid grid grid-rows-2 gap-5">
                        @forelse ($popularListings as $listing)
                            <x-venues.carousel-card :listing="$listing" :badge="$listing['category']" />
                        @empty
                            <div class="rounded-[1.5rem] bg-white p-6 text-sm font-semibold text-[#6b7b99] ring-1 ring-[#dce6f7]">Aucun espace publié pour le moment.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 pb-16 sm:px-8">
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#2f6bff]">Explorer par besoin</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.03em] text-[#07152f] sm:text-4xl">Catégories qui réservent vite</h2>
                </div>
                <a href="{{ route('venues.index') }}" class="rounded-full border border-[#c9d8ef] bg-white px-5 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm transition hover:border-[#2f6bff]">Tout explorer</a>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($categoryHighlights as $index => $category)
                    <a href="{{ route('venues.index', ['category' => $category->slug]) }}" class="group flex items-center gap-4 rounded-[1.5rem] border border-[#dce6f7] bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#2f6bff]/12">
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl" style="background-color: {{ $categoryColors[$index % count($categoryColors)] }}20; color: {{ $categoryColors[$index % count($categoryColors)] }}">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $categoryIcon($category->icon ?? null) !!}</svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-lg font-extrabold text-[#081225]">{{ $category->name }}</span>
                            <span class="mt-1 block text-sm font-semibold text-[#6b7b99]">{{ $category->published_venues_count }} espace{{ $category->published_venues_count > 1 ? 's' : '' }} publié{{ $category->published_venues_count > 1 ? 's' : '' }}</span>
                        </span>
                        <span class="text-lg font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">→</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 pb-18 sm:px-8">
            <div class="overflow-hidden rounded-[2rem] border border-[#dce6f7] bg-white shadow-2xl shadow-[#173e7a]/8">
                <div class="grid lg:grid-cols-[1fr_1.05fr]">
                    <div class="bg-[#07152f] p-7 text-white sm:p-9">
                        <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#8dc1ff]">Où organiser ?</p>
                        <h2 class="mt-3 text-4xl font-extrabold leading-tight tracking-[-0.04em]">Les villes les plus demandées sur BAOBAA.</h2>
                        <p class="mt-4 text-sm font-semibold leading-7 text-white/68">Retrouvez rapidement les espaces disponibles dans les marchés événementiels actifs de la plateforme.</p>
                    </div>

                    <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-7">
                        @foreach ($cityHighlights as $city)
                            <a href="{{ route('venues.index', ['city' => $city->city]) }}" class="rounded-[1.25rem] border border-[#edf2fb] bg-[#f7faff] p-4 transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-white">
                                <p class="text-xl font-extrabold tracking-[-0.03em] text-[#151821]">{{ $city->city }}</p>
                                <p class="mt-2 text-sm font-semibold text-[#6b7b99]">{{ $city->venues_count }} espace{{ $city->venues_count > 1 ? 's' : '' }} · dès {{ number_format($city->starting_price, 0, ',', ' ') }} XOF</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl space-y-12 px-5 pb-18 sm:px-8 ">
            @foreach ([
                ['eyebrow' => 'Populaire dans cette catégorie', 'title' => 'Des espaces à découvrir par type d’événement', 'items' => $categoryListings, 'badge' => 'category', 'url' => route('venues.index')],
                ['eyebrow' => 'Populaire dans cette ville', 'title' => 'Les lieux qui montent dans les villes actives', 'items' => $cityListings, 'badge' => 'city', 'url' => route('venues.index')],
                ['eyebrow' => 'Populaire dans cette commune', 'title' => 'Les adresses locales qui attirent les réservations', 'items' => $districtListings, 'badge' => 'district', 'url' => route('venues.index')],
            ] as $rail)
                <div data-carousel-section>
                    <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#2f6bff]">{{ $rail['eyebrow'] }}</p>
                            <h2 class="mt-2 max-w-3xl text-2xl font-extrabold tracking-[-0.03em] text-[#07152f] sm:text-3xl">{{ $rail['title'] }}</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" data-carousel-prev class="grid size-10 place-items-center rounded-full border border-[#d8dce9] bg-white text-[#2f6bff] shadow-sm transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef3ff]" aria-label="Précédent">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button type="button" data-carousel-next class="grid size-10 place-items-center rounded-full border border-[#d8dce9] bg-white text-[#2f6bff] shadow-sm transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef3ff]" aria-label="Suivant">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            <a href="{{ $rail['url'] }}" class="rounded-full border border-[#c9d8ef] bg-white px-5 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm transition hover:border-[#2f6bff]">Explorer</a>
                        </div>
                    </div>

                    <div class="relative">
                        <div data-carousel class="baobaa-scrollbar-none overflow-x-auto scroll-smooth px-1 pb-4">
                            <div class="baobaa-venue-carousel-grid grid grid-rows-1 gap-5">
                                @forelse ($rail['items'] as $listing)
                                    @php($badge = $listing[$rail['badge']] ?: $listing['category'])
                                    <x-venues.carousel-card :listing="$listing" :badge="$badge" />
                                @empty
                                    <div class="rounded-[1.5rem] bg-white p-6 text-sm font-semibold text-[#6b7b99] ring-1 ring-[#dce6f7]">Aucun espace disponible dans cette sélection.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <footer class="border-t border-white/80 bg-[#07152f] px-5 py-10 text-white sm:px-8">
            <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1fr_auto_auto]">
                <div>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-white">
                        <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
                    </a>
                    <p class="mt-5 max-w-xl text-sm font-semibold leading-7 text-white/62">Réservez des salles, jardins, rooftops, auditoriums et espaces professionnels vérifiés en Afrique francophone.</p>
                </div>

                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Explorer</p>
                    <div class="mt-4 grid gap-2 text-sm font-bold text-white/70">
                        <a href="{{ route('venues.index') }}" class="hover:text-white">Espaces</a>
                        <a href="{{ route('owner-profiles.index') }}" class="hover:text-white">Partenaires</a>
                        <a href="{{ route('owner.register') }}" class="hover:text-white">Devenir partenaire</a>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Accès</p>
                    <div class="mt-4 grid gap-2 text-sm font-bold text-white/70">
                        <a href="{{ route('portal.login', ['portal' => 'client']) }}" class="hover:text-white">Connexion client</a>
                        <a href="{{ route('client.register') }}" class="hover:text-white">Créer un compte</a>
                        <a href="{{ route('owner.register') }}" class="hover:text-white">Publier un espace</a>
                    </div>
                </div>
            </div>

            <div class="mx-auto mt-8 flex max-w-7xl flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-5 text-xs font-semibold text-white/45">
                <p>© {{ now()->year }} BAOBAA. Tous droits réservés.</p>
                <p>Réservation sécurisée · Partenaires vérifiés · Expérience premium</p>
            </div>
        </footer>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scroller = document.querySelector('[data-category-scroll]');
            const previous = document.querySelector('[data-category-prev]');
            const next = document.querySelector('[data-category-next]');
            const suggestions = @js($searchSuggestions);
            const citySuggestions = @js($citySuggestions);
            const searchForm = document.querySelector('[data-home-search-form]');
            const searchInput = document.querySelector('[data-search-input]');
            const searchResults = document.querySelector('[data-search-results]');
            const cityRoot = document.querySelector('[data-city-autocomplete]');
            const cityInput = document.querySelector('[data-city-input]');
            const cityList = document.querySelector('[data-city-list]');
            const dateRoot = document.querySelector('[data-date-range]');
            const dateToggle = document.querySelector('[data-date-toggle]');
            const datePanel = document.querySelector('[data-date-panel]');
            const startDate = document.querySelector('[data-start-date]');
            const endDate = document.querySelector('[data-end-date]');
            const dateApply = document.querySelector('[data-date-apply]');

            if (scroller && previous && next) {
                const scrollCategories = (direction) => {
                    scroller.scrollBy({
                        left: direction * Math.min(420, scroller.clientWidth * 0.72),
                        behavior: 'smooth',
                    });
                };

                previous.addEventListener('click', () => scrollCategories(-1));
                next.addEventListener('click', () => scrollCategories(1));
            }

            document.querySelectorAll('[data-carousel-section]').forEach((section) => {
                const carousel = section.querySelector('[data-carousel]');
                const carouselPrevious = section.querySelector('[data-carousel-prev]');
                const carouselNext = section.querySelector('[data-carousel-next]');

                if (!carousel) {
                    return;
                }

                const scrollCarousel = (direction) => {
                    carousel.scrollBy({
                        left: direction * Math.min(620, carousel.clientWidth * 0.86),
                        behavior: 'smooth',
                    });
                };

                carouselPrevious?.addEventListener('click', () => scrollCarousel(-1));
                carouselNext?.addEventListener('click', () => scrollCarousel(1));
            });

            const hideSearchResults = () => {
                searchResults?.classList.add('hidden');
                if (searchResults) {
                    searchResults.innerHTML = '';
                }
            };

            const appendSuggestionButton = (container, suggestion, onSelect) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'flex w-full items-center justify-between gap-3 rounded-2xl px-4 py-3 text-left transition hover:bg-white';

                const label = document.createElement('span');
                label.className = 'text-sm font-extrabold text-[#151821]';
                label.textContent = suggestion.label;

                const type = document.createElement('span');
                type.className = 'rounded-full bg-[#edf4ff] px-2 py-0.5 text-[10px] font-extrabold uppercase text-[#2f6bff]';
                type.textContent = suggestion.type;

                button.append(label, type);
                button.addEventListener('click', onSelect);
                container.appendChild(button);
            };

            const renderSearchResults = () => {
                const value = searchInput?.value.trim().toLowerCase() ?? '';

                if (!searchResults || !searchInput || value.length < 3) {
                    hideSearchResults();
                    return;
                }

                const matches = suggestions
                    .filter((suggestion) => suggestion.label.toLowerCase().includes(value))
                    .slice(0, 8);

                searchResults.innerHTML = '';

                if (!matches.length) {
                    hideSearchResults();
                    return;
                }

                const title = document.createElement('p');
                title.className = 'mb-2 px-2 text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]';
                title.textContent = 'Résultats suggérés';
                searchResults.appendChild(title);

                const grid = document.createElement('div');
                grid.className = 'grid gap-2 sm:grid-cols-2';
                searchResults.appendChild(grid);

                matches.forEach((suggestion) => {
                    appendSuggestionButton(grid, suggestion, () => {
                        searchInput.value = suggestion.label;
                        hideSearchResults();
                    });
                });

                searchResults.classList.remove('hidden');
            };

            const hideCityList = () => {
                cityList?.classList.add('hidden');
                if (cityList) {
                    cityList.innerHTML = '';
                }
            };

            const renderCityList = () => {
                if (!cityInput || !cityList) {
                    return;
                }

                const value = cityInput.value.trim().toLowerCase();
                const matches = citySuggestions
                    .filter((city) => city.toLowerCase().includes(value))
                    .slice(0, 8);

                cityList.innerHTML = '';

                if (!matches.length) {
                    hideCityList();
                    return;
                }

                matches.forEach((city) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'block w-full rounded-xl px-3 py-2 text-left text-sm font-extrabold text-[#151821] transition hover:bg-[#eef4ff]';
                    button.textContent = city;
                    button.addEventListener('click', () => {
                        cityInput.value = city;
                        hideCityList();
                    });
                    cityList.appendChild(button);
                });

                cityList.classList.remove('hidden');
            };

            const updateDateLabel = () => {
                if (!dateToggle) {
                    return;
                }

                if (startDate?.value && endDate?.value) {
                    dateToggle.textContent = `${startDate.value} - ${endDate.value}`;
                    dateToggle.classList.remove('text-[#8a8c93]');
                    dateToggle.classList.add('text-[#25262b]');
                    return;
                }

                if (startDate?.value) {
                    dateToggle.textContent = `Dès ${startDate.value}`;
                    dateToggle.classList.remove('text-[#8a8c93]');
                    dateToggle.classList.add('text-[#25262b]');
                    return;
                }

                dateToggle.textContent = 'Début - fin';
                dateToggle.classList.add('text-[#8a8c93]');
                dateToggle.classList.remove('text-[#25262b]');
            };

            searchInput?.addEventListener('input', renderSearchResults);
            searchInput?.addEventListener('focus', renderSearchResults);
            cityInput?.addEventListener('input', renderCityList);
            cityInput?.addEventListener('focus', renderCityList);
            dateToggle?.addEventListener('click', () => datePanel?.classList.toggle('hidden'));
            startDate?.addEventListener('change', updateDateLabel);
            endDate?.addEventListener('change', updateDateLabel);
            dateApply?.addEventListener('click', () => {
                updateDateLabel();
                datePanel?.classList.add('hidden');
            });

            document.addEventListener('click', (event) => {
                if (searchForm && !searchForm.contains(event.target)) {
                    hideSearchResults();
                    hideCityList();
                    datePanel?.classList.add('hidden');
                }

                if (cityRoot && !cityRoot.contains(event.target)) {
                    hideCityList();
                }
            });
        });
    </script>
</x-layouts.baobaa>
