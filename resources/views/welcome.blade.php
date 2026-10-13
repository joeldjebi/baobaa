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
        'price' => number_format($venue->starting_price, 0, ',', ' ').' '.$venue->currency,
        'price_unit' => ($venue->price_unit ?? \App\Enums\PriceUnit::Event)->label(),
        'image' => $venue->cover_image_url,
        'verified' => $venue->verification_status === \App\Enums\VerificationStatus::Verified,
    ])->values();

    $popularListings = $mapVenueCards($featuredVenues);
    $categoryListings = $mapVenueCards($categoryCarouselVenues);
    $cityListings = $mapVenueCards($cityCarouselVenues);
    $districtListings = $mapVenueCards($districtCarouselVenues);
@endphp

<x-layouts.baobaa title="BAOBAA - Réservez une salle ou un espace pour votre événement" description="Trouvez et réservez la salle idéale pour votre mariage, séminaire, conférence ou soirée en Afrique francophone. Espaces vérifiés, prix clairs et paiement sécurisé.">
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
            <div class="baobaa-aurora pointer-events-none absolute inset-0" data-aurora="-36" aria-hidden="true">
                <div class="animate-baobaa-hero-drift absolute -left-[12%] top-[45%] h-[360px] w-[72%] rounded-[50%] bg-[#dbe6ff]/70 blur-3xl"></div>
                <div class="animate-baobaa-hero-drift absolute left-[30%] top-[8%] h-[260px] w-[40%] rounded-[50%] bg-[#cfe2ff]/60 blur-3xl" style="animation-delay: -11s"></div>
            </div>
            <div class="baobaa-aurora pointer-events-none absolute inset-0" data-aurora="44" aria-hidden="true">
                <div class="animate-baobaa-hero-drift absolute -right-[12%] top-[38%] h-[380px] w-[74%] rounded-[50%] bg-[#e7e4ff]/70 blur-3xl" style="animation-delay: -7s"></div>
                <div class="animate-baobaa-hero-drift absolute right-[8%] top-[4%] size-[220px] rounded-full bg-[#ffe3f1]/50 blur-3xl" style="animation-delay: -3s"></div>
            </div>

            <header class="relative z-[120] mx-auto flex max-w-[1680px] items-center justify-between">
                <a href="{{ url('/') }}" class="flex shrink-0 items-center text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-12 w-auto max-w-[180px] rounded-2xl bg-white/80 object-contain p-1 shadow-sm ring-1 ring-[#dbe3f8] sm:h-14" loading="eager">
                </a>

                <x-navigation.public-menu active="explore" class="border-white/80 bg-white/72 shadow-sm backdrop-blur" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('owner.register') }}" class="hidden rounded-full bg-[#17191f] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#17191f]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] md:inline-flex">Devenir partenaire</a>
                    @endguest
                    <x-navigation.cart-link />
                    <x-navigation.account-menu class="bg-white/82 text-[#17191f] ring-white/80 backdrop-blur" />
                    <x-navigation.mobile-menu active="explore" />
                </div>
            </header>

            <div class="relative z-[70] mx-auto flex max-w-[1240px] flex-col items-center pt-8 text-center sm:pt-14 lg:pt-16">
                <div class="animate-baobaa-fade-up inline-flex max-w-full items-center gap-2 rounded-full border border-white/80 bg-white/90 py-1.5 pl-1.5 pr-4 text-xs font-extrabold text-[#2f6bff] shadow-[0_10px_30px_rgba(25,64,150,.12)] ring-1 ring-[#cfdaf5] backdrop-blur sm:text-sm">
                    @if ($heroThumbnails->isNotEmpty())
                        <span class="flex -space-x-2">
                            @foreach ($heroThumbnails as $thumbnail)
                                <img src="{{ $thumbnail }}" alt="" width="28" height="28" class="size-7 rounded-full object-cover ring-2 ring-white" loading="eager" decoding="async">
                            @endforeach
                        </span>
                    @endif
                    <span class="whitespace-nowrap"><span data-count-to="{{ $publishedVenuesCount }}">{{ number_format($publishedVenuesCount, 0, ',', ' ') }}</span> espace{{ $publishedVenuesCount > 1 ? 's' : '' }} dans {{ $publishedCitiesCount }} ville{{ $publishedCitiesCount > 1 ? 's' : '' }}</span>
                    <span class="hidden text-[#9eb4ed] sm:inline">&middot;</span>
                    <span class="hidden whitespace-nowrap sm:inline">Partenaires vérifiés</span>
                </div>

                @php
                    $headlineWords = explode(' ', 'Le lieu, les professionnels et le matériel de votre');
                @endphp
                <h1 class="mt-5 max-w-[900px] text-[34px] font-extrabold leading-[1.08] tracking-[-0.045em] text-[#202027] sm:text-[56px] lg:text-[68px]">
                    <span class="sr-only">Le lieu, les professionnels et le matériel de votre événement</span>
                    <span aria-hidden="true">
                        @foreach ($headlineWords as $wordIndex => $word)
                            <span class="motion-word" style="--word-index: {{ $wordIndex }}">{{ $word }}</span>
                        @endforeach
                        <span class="motion-word relative" style="--word-index: {{ count($headlineWords) }}">
                            <span class="baobaa-rotating-word pb-[.12em]" data-rotating-words="2400">@foreach (['événement', 'mariage', 'séminaire', 'gala', 'anniversaire', 'lancement'] as $rotatingWord)<span class="baobaa-gradient-text">{{ $rotatingWord }}</span>@endforeach</span>
                            <span class="absolute -bottom-1 left-0 h-1.5 w-full rounded-full bg-[linear-gradient(90deg,#9ab7ff,#c4b5fd,#9ab7ff)] opacity-80"></span>
                        </span>
                    </span>
                </h1>

                <p class="animate-baobaa-fade-up mt-4 max-w-2xl text-base font-semibold leading-7 text-[#626b7e] sm:text-lg" style="animation-delay: .16s">
                    Comparez des espaces vérifiés, réservez en ligne avec un acompte sécurisé et complétez votre événement en quelques clics.
                </p>

                <div class="animate-baobaa-fade-up relative z-[80] mt-7 w-full max-w-[1120px]" style="animation-delay: .24s" data-hero-search>
                    <div class="mx-auto flex w-max max-w-full gap-1 overflow-x-auto rounded-full bg-white/80 p-1 text-sm font-extrabold shadow-sm ring-1 ring-[#dce6f7] backdrop-blur baobaa-scrollbar-none" role="tablist" aria-label="Que recherchez-vous ?">
                        @foreach (['venues' => 'Espaces', 'professionals' => 'Professionnels', 'products' => 'Matériel'] as $tab => $label)
                            <button type="button" role="tab" data-hero-tab="{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="shrink-0 rounded-full px-5 py-2 transition aria-selected:bg-[#2f6bff] aria-selected:text-white aria-selected:shadow-lg aria-selected:shadow-[#2f6bff]/25 text-[#52617b] hover:text-[#2f6bff]">{{ $label }}</button>
                        @endforeach
                    </div>

                    <form method="GET" action="{{ route('venues.index') }}" data-hero-panel="venues" class="relative mt-3 rounded-[2rem] border border-white/80 bg-white/95 p-3 text-left shadow-[0_18px_55px_rgba(18,30,75,.15)] ring-1 ring-[#cfd3df] backdrop-blur-xl" data-home-search-form>
                        <div class="grid items-center gap-0 lg:grid-cols-[1.1fr_.85fr_.95fr_.9fr_auto]">
                            <label class="border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5">
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Quoi</span>
                                <input type="text" name="q" autocomplete="off" data-search-input placeholder="Salle de mariage, rooftop…" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:font-semibold placeholder:text-[#9a9ca3]">
                            </label>
                            <label class="relative border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5" data-city-autocomplete>
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Où</span>
                                <input type="text" name="city" autocomplete="off" data-city-input placeholder="Ville ou quartier" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:font-semibold placeholder:text-[#9a9ca3]">
                                <div data-city-list class="absolute left-2 right-2 top-[calc(100%+8px)] z-[90] hidden rounded-2xl border border-[#dce6f7] bg-white p-2 text-left shadow-2xl shadow-[#173e7a]/16 lg:left-4 lg:right-4"></div>
                            </label>
                            <div class="relative border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5" data-date-range>
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Quand</span>
                                <button type="button" data-date-toggle class="mt-1 w-full bg-transparent text-left text-base font-semibold text-[#9a9ca3] outline-none">Choisir une date</button>
                                <div data-date-panel class="absolute left-2 right-2 top-[calc(100%+8px)] z-[90] hidden rounded-3xl border border-[#dce6f7] bg-white p-4 shadow-2xl shadow-[#173e7a]/16 lg:left-4 lg:right-4 lg:min-w-[360px]">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="rounded-2xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                            <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Date début</span>
                                            <input type="date" name="start_date" min="{{ now()->toDateString() }}" data-start-date class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                        </label>
                                        <label class="rounded-2xl bg-[#f7faff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                            <span class="block text-[11px] font-extrabold uppercase text-[#8a94aa]">Date fin</span>
                                            <input type="date" name="end_date" min="{{ now()->toDateString() }}" data-end-date class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                        </label>
                                    </div>
                                    <button type="button" data-date-apply class="mt-3 w-full rounded-full bg-[#2f6bff] px-4 py-2.5 text-sm font-extrabold text-white">Valider les dates</button>
                                </div>
                            </div>
                            <label class="border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5">
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Invités</span>
                                <input type="number" min="1" name="capacity" inputmode="numeric" placeholder="Nombre" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:font-semibold placeholder:text-[#9a9ca3]">
                            </label>
                            <button class="mt-3 inline-flex items-center justify-center gap-2 rounded-[1.6rem] bg-[#2f6bff] px-7 py-3.5 text-base font-extrabold text-white shadow-[0_12px_28px_rgba(47,107,255,.26)] transition hover:-translate-y-0.5 hover:bg-[#2258df] lg:mt-0">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                Rechercher
                            </button>
                        </div>
                        <div data-search-results class="relative z-[85] mt-3 hidden rounded-3xl border border-[#dce6f7] bg-[#f8fbff] p-3 text-left shadow-2xl shadow-[#173e7a]/12"></div>
                    </form>

                    <form method="GET" action="{{ route('service-provider-profiles.index') }}" data-hero-panel="professionals" hidden class="mt-3 rounded-[2rem] border border-white/80 bg-white/95 p-3 text-left shadow-[0_18px_55px_rgba(18,30,75,.15)] ring-1 ring-[#cfd3df]">
                        <div class="grid items-center gap-0 lg:grid-cols-[1.4fr_1fr_auto]">
                            <label class="border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5">
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Quel professionnel ?</span>
                                <input type="text" name="q" placeholder="Sonorisation, photographe, décoration…" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:font-semibold placeholder:text-[#9a9ca3]">
                            </label>
                            <label class="border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5">
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Ville</span>
                                <select name="city" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none">
                                    <option value="">Toutes les villes</option>
                                    @foreach ($citySuggestions as $citySuggestion)
                                        <option value="{{ $citySuggestion }}">{{ $citySuggestion }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="mt-3 inline-flex items-center justify-center gap-2 rounded-[1.6rem] bg-[#2f6bff] px-7 py-3.5 text-base font-extrabold text-white shadow-[0_12px_28px_rgba(47,107,255,.26)] transition hover:-translate-y-0.5 hover:bg-[#2258df] lg:mt-0">Trouver un professionnel</button>
                        </div>
                    </form>

                    <form method="GET" action="{{ route('products.index') }}" data-hero-panel="products" hidden class="mt-3 rounded-[2rem] border border-white/80 bg-white/95 p-3 text-left shadow-[0_18px_55px_rgba(18,30,75,.15)] ring-1 ring-[#cfd3df]">
                        <div class="grid items-center gap-0 lg:grid-cols-[1.4fr_1fr_auto]">
                            <label class="border-b border-[#e5e7ee] px-3 py-2.5 lg:border-b-0 lg:border-r lg:px-5">
                                <span class="block text-xs font-extrabold uppercase tracking-[0.04em] text-[#73757b]">Quel matériel ?</span>
                                <input type="text" name="q" placeholder="Chaises, tente, enceinte…" class="mt-1 w-full bg-transparent text-base font-semibold text-[#25262b] outline-none placeholder:font-semibold placeholder:text-[#9a9ca3]">
                            </label>
                            <div class="flex items-center gap-2 border-b border-[#e5e7ee] px-3 py-3 lg:border-b-0 lg:border-r lg:px-5">
                                @foreach (['rental' => 'Louer', 'sale' => 'Acheter'] as $mode => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="mode" value="{{ $mode }}" class="peer sr-only" @checked($loop->first)>
                                        <span class="block rounded-full px-4 py-2 text-sm font-extrabold text-[#52617b] ring-1 ring-[#dce6f7] peer-checked:bg-[#eef4ff] peer-checked:text-[#2f6bff] peer-checked:ring-[#2f6bff]">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <button class="mt-3 inline-flex items-center justify-center gap-2 rounded-[1.6rem] bg-[#2f6bff] px-7 py-3.5 text-base font-extrabold text-white shadow-[0_12px_28px_rgba(47,107,255,.26)] transition hover:-translate-y-0.5 hover:bg-[#2258df] lg:mt-0">Voir le matériel</button>
                        </div>
                    </form>
                </div>

                <div class="animate-baobaa-fade-up mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs font-extrabold text-[#506079] sm:text-sm" style="animation-delay: .3s">
                    @foreach (['Prix clairs avant de réserver', 'Acompte sécurisé', 'Réponse du propriétaire sur la plateforme'] as $promise)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="size-4 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            {{ $promise }}
                        </span>
                    @endforeach
                </div>
            </div>

            @if ($categories->isNotEmpty())
                <div id="categories" class="relative z-10 mx-auto mt-10 max-w-[1240px]">
                    <button type="button" data-category-prev class="absolute -left-2 top-4 z-20 hidden size-10 place-items-center rounded-full bg-white text-[#2f6bff] shadow-lg ring-1 ring-[#d8dce9] transition hover:bg-[#eef3ff] disabled:opacity-0 md:grid" aria-label="Catégories précédentes">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" data-category-next class="absolute -right-2 top-4 z-20 hidden size-10 place-items-center rounded-full bg-white text-[#2f6bff] shadow-lg ring-1 ring-[#d8dce9] transition hover:bg-[#eef3ff] disabled:opacity-0 md:grid" aria-label="Catégories suivantes">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>

                    <div data-category-scroll class="baobaa-scrollbar-none overflow-x-auto scroll-smooth px-1 pb-3 md:px-10">
                        <div class="mx-auto flex w-max gap-6">
                            @foreach ($categories as $index => $category)
                                <a href="{{ route('venues.index', ['category' => $category->slug]) }}" class="group flex w-[88px] shrink-0 flex-col items-center gap-2 text-center">
                                    <span class="grid size-14 place-items-center rounded-full bg-white shadow-[0_2px_10px_rgba(38,48,75,.12)] ring-1 ring-[#d8dce9] transition group-hover:-translate-y-1 group-hover:shadow-[0_8px_22px_rgba(47,107,255,.2)]">
                                        <span class="grid size-9 place-items-center rounded-xl" style="background-color: {{ $categoryColors[$index % count($categoryColors)] }}22; color: {{ $categoryColors[$index % count($categoryColors)] }}">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $categoryIcon($category->icon ?? null) !!}</svg>
                                        </span>
                                    </span>
                                    <span class="line-clamp-2 min-h-9 text-[13px] font-extrabold leading-[1.15] text-[#333b4f] transition group-hover:text-[#2f6bff]">{{ $category->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </section>

        @if ($popularListings->isNotEmpty())
            @php
                $bandTiles = $popularListings->values();
                while ($bandTiles->count() < 8) {
                    $bandTiles = $bandTiles->concat($popularListings);
                }
                $bandRows = [$bandTiles->take(10), $bandTiles->reverse()->take(10)];
            @endphp
            <section class="baobaa-photo-band relative overflow-hidden py-6" aria-label="Aperçu des espaces publiés">
                <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-16 bg-gradient-to-r from-[#eef3ff] to-transparent sm:w-32"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-16 bg-gradient-to-l from-[#eef3ff] to-transparent sm:w-32"></div>
                <div class="space-y-4">
                    @foreach ($bandRows as $rowIndex => $row)
                        <div class="baobaa-photo-track" @if ($rowIndex === 1) data-direction="reverse" @endif style="--track-duration: {{ $rowIndex === 0 ? 70 : 85 }}s">
                            @foreach ([$row, $row] as $copyIndex => $copy)
                                @foreach ($copy as $tile)
                                    <a href="{{ route('venues.show', $tile['slug']) }}" @if ($copyIndex === 1) aria-hidden="true" tabindex="-1" @endif class="group relative block h-36 w-56 shrink-0 overflow-hidden rounded-[1.4rem] shadow-lg shadow-[#173e7a]/10 ring-1 ring-white sm:h-44 sm:w-72">
                                        <img src="{{ $tile['image'] }}" alt="{{ $copyIndex === 0 ? $tile['title'] : '' }}" width="288" height="176" loading="lazy" decoding="async" referrerpolicy="no-referrer" class="h-full w-full object-cover transition duration-700 group-hover:scale-110">
                                        <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#07152f]/80 to-transparent p-3 text-left">
                                            <span class="block truncate text-sm font-extrabold text-white">{{ $tile['title'] }}</span>
                                            <span class="block truncate text-[11px] font-bold text-white/75">{{ $tile['city'] }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mx-auto max-w-7xl px-5 py-14 sm:px-8" data-reveal>
            <div class="text-center">
                <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#2f6bff]">Comment ça marche</p>
                <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.03em] text-[#07152f] sm:text-4xl">Votre événement en trois étapes</h2>
            </div>
            <div class="relative mt-10 grid gap-6 md:grid-cols-3">
                <div class="baobaa-step-line pointer-events-none absolute left-[16%] right-[16%] top-8 hidden h-0.5 rounded-full bg-[linear-gradient(90deg,#2f6bff,#7c5cff,#00a3ff)] md:block" aria-hidden="true"></div>
                @foreach ([
                    ['Trouvez le bon lieu', 'Filtrez par ville, date, capacité et budget, puis comparez photos, prix et avis réels.', 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm10 2-4.3-4.3'],
                    ['Réservez en sécurité', 'Échangez avec le propriétaire, validez la proforma et bloquez la date avec un acompte.', 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Zm-3-10 2 2 4-4'],
                    ['Complétez votre événement', 'Ajoutez des professionnels et louez le matériel nécessaire, au même endroit.', 'M12 3l7 4v10l-7 4-7-4V7l7-4Zm0 0v8m7-4-7 4-7-4'],
                ] as $stepIndex => [$stepTitle, $stepText, $stepIcon])
                    <div class="relative rounded-[1.6rem] bg-white p-6 text-center shadow-sm ring-1 ring-[#dce6f7] transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2f6bff]/10" data-reveal style="--reveal-delay: {{ 150 + $stepIndex * 160 }}ms">
                        <span class="relative mx-auto grid size-16 place-items-center rounded-2xl bg-[#2f6bff] text-white shadow-lg shadow-[#2f6bff]/30">
                            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $stepIcon }}"/></svg>
                            <span class="absolute -right-2 -top-2 grid size-7 place-items-center rounded-full bg-white text-xs font-extrabold text-[#2f6bff] ring-2 ring-[#2f6bff]">{{ $stepIndex + 1 }}</span>
                        </span>
                        <h3 class="mt-5 text-lg font-extrabold text-[#07152f]">{{ $stepTitle }}</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#6b7b99]">{{ $stepText }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="espaces" class="mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:py-18" data-carousel-section>
            <div class="mb-8 flex flex-wrap items-end justify-between gap-5" data-reveal>
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
                    <div class="baobaa-venue-carousel-grid grid grid-rows-2 gap-5" data-reveal-stagger="60">
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
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4" data-reveal>
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#2f6bff]">Explorer par besoin</p>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.03em] text-[#07152f] sm:text-4xl">Catégories qui réservent vite</h2>
                </div>
                <a href="{{ route('venues.index') }}" class="rounded-full border border-[#c9d8ef] bg-white px-5 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm transition hover:border-[#2f6bff]">Tout explorer</a>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-reveal-stagger="90" data-reveal-variant="scale">
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
            <div class="overflow-hidden rounded-[2rem] border border-[#dce6f7] bg-white shadow-2xl shadow-[#173e7a]/8" data-reveal="scale">
                <div class="grid lg:grid-cols-[1fr_1.05fr]">
                    <div class="bg-[#07152f] p-7 text-white sm:p-9">
                        <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#8dc1ff]">Où organiser ?</p>
                        <h2 class="mt-3 text-4xl font-extrabold leading-tight tracking-[-0.04em]">Les villes les plus demandées sur BAOBAA.</h2>
                        <p class="mt-4 text-sm font-semibold leading-7 text-white/68">Retrouvez rapidement les espaces disponibles dans les marchés événementiels actifs de la plateforme.</p>
                    </div>

                    <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-7" data-reveal-stagger="80" data-reveal-variant="right">
                        @foreach ($cityHighlights as $city)
                            <a href="{{ route('venues.index', ['city' => $city->city]) }}" class="rounded-[1.25rem] border border-[#edf2fb] bg-[#f7faff] p-4 transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-white">
                                <p class="text-xl font-extrabold tracking-[-0.03em] text-[#151821]">{{ $city->city }}</p>
                                <p class="mt-2 text-sm font-semibold text-[#6b7b99]"><span data-count-to="{{ $city->venues_count }}">{{ $city->venues_count }}</span> espace{{ $city->venues_count > 1 ? 's' : '' }} · dès {{ number_format($city->starting_price, 0, ',', ' ') }} XOF</p>
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
                    <div class="mb-5 flex flex-wrap items-end justify-between gap-4" data-reveal>
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
                            <div class="baobaa-venue-carousel-grid grid grid-rows-1 gap-5" data-reveal-stagger="70">
                                @forelse ($rail['items'] as $listing)
                                    @php
                                        $badge = $listing[$rail['badge']] ?: $listing['category'];
                                    @endphp
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

        <x-navigation.public-footer />
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
                const refreshCategoryArrows = () => {
                    const maxScroll = scroller.scrollWidth - scroller.clientWidth;
                    previous.disabled = scroller.scrollLeft <= 4;
                    next.disabled = scroller.scrollLeft >= maxScroll - 4;
                };

                previous.addEventListener('click', () => scrollCategories(-1));
                next.addEventListener('click', () => scrollCategories(1));
                scroller.addEventListener('scroll', refreshCategoryArrows, { passive: true });
                window.addEventListener('resize', refreshCategoryArrows);
                window.addEventListener('load', refreshCategoryArrows);
                document.fonts?.ready.then(refreshCategoryArrows);
                refreshCategoryArrows();
            }

            const heroTabs = [...document.querySelectorAll('[data-hero-tab]')];
            const heroPanels = [...document.querySelectorAll('[data-hero-panel]')];

            heroTabs.forEach((tab) => tab.addEventListener('click', () => {
                heroTabs.forEach((item) => item.setAttribute('aria-selected', item === tab ? 'true' : 'false'));
                heroPanels.forEach((panel) => {
                    panel.hidden = panel.dataset.heroPanel !== tab.dataset.heroTab;
                });
                document.querySelector(`[data-hero-panel="${tab.dataset.heroTab}"] input[type="text"]`)?.focus({ preventScroll: true });
            }));

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

            const formatDate = (value) => new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
                .format(new Date(`${value}T00:00:00`));

            const updateDateLabel = () => {
                if (!dateToggle) {
                    return;
                }

                if (endDate && startDate?.value) {
                    endDate.min = startDate.value;

                    if (endDate.value && endDate.value < startDate.value) {
                        endDate.value = startDate.value;
                    }
                }

                if (startDate?.value && endDate?.value) {
                    dateToggle.textContent = `${formatDate(startDate.value)} - ${formatDate(endDate.value)}`;
                    dateToggle.classList.remove('text-[#8a8c93]');
                    dateToggle.classList.add('text-[#25262b]');
                    return;
                }

                if (startDate?.value) {
                    dateToggle.textContent = `Le ${formatDate(startDate.value)}`;
                    dateToggle.classList.remove('text-[#8a8c93]');
                    dateToggle.classList.add('text-[#25262b]');
                    return;
                }

                dateToggle.textContent = 'Choisir une date';
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
