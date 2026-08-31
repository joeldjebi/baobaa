@php
    $title = $venue?->name ?? $fallback['title'];
    $category = $venue?->category?->name ?? $fallback['category'];
    $city = $venue?->city ?? $fallback['city'];
    $district = $venue?->district ?? $fallback['district'];
    $capacity = $venue ? $venue->min_capacity.'-'.$venue->max_capacity.' invités' : $fallback['capacity'];
    $surface = $venue?->surface_area ? $venue->surface_area.' m2' : $fallback['surface'];
    $price = $venue?->starting_price ? number_format($venue->starting_price, 0, ',', ' ').' '.$venue->currency : $fallback['price'];
    $reservation = $venue?->reservation_amount ? number_format($venue->reservation_amount, 0, ',', ' ').' '.$venue->currency : $fallback['reservation'];
    $rating = $venue?->average_rating > 0 ? $venue->average_rating : $fallback['rating'];
    $reviews = $venue?->reviews_count > 0 ? $venue->reviews_count : $fallback['reviews'];
    $description = $venue?->description ?: $fallback['description'];
    $venueMedia = $venue?->media?->sortBy('sort_order') ?? collect();
    $images = $venueMedia->where('type', 'image')->map->signed_url->values()->all() ?: $fallback['images'];
    $videos = $venueMedia->where('type', 'video')->map(fn ($media) => [
        'url' => $media->signed_url,
        'alt' => $media->alt_text ?: $title,
    ])->values()->all();
    $galleryImages = array_values($images);
    $highlights = $venue?->highlights ?: ['Lieu central', 'Réponse en moins de 2 heures', 'Demande de réservation', 'Sur place'];
    $includedItems = $venue?->included_items ?: ['Parking', 'Wi-Fi', 'Lumière naturelle', 'Chaises', 'Toilettes accessibles', 'Musique autorisée', 'Tables rectangulaires', 'Sonorisation disponible'];
    $configurations = $venue?->configurations?->where('is_active', true)->sortBy('sort_order')->values() ?? collect();
    $addOns = $venue?->addOns?->where('is_available', true)->sortBy('sort_order')->values() ?? collect();
    $policies = $venue?->policies?->sortBy('sort_order')->values() ?? collect();
    $faqs = $venue?->faqs?->where('is_active', true)->sortBy('sort_order')->values() ?? collect();
    $amenityDetails = collect($venue?->space_details['amenities'] ?? [])
        ->filter(fn ($amenity) => filled($amenity['name'] ?? null))
        ->values();
    $amenities = $amenityDetails->pluck('name')->take(10)->all()
        ?: ($venue?->amenities?->pluck('name')->take(10)->all() ?: ['Espace de coworking/reunion', 'Tables rectangulaires', 'Wi-Fi disponible', 'Lumiere naturelle', 'Toilettes', 'Accessible fauteuil roulant', 'Stationnement a proximite']);

    $fallbackConfigurations = [
        ['name' => 'Debout', 'capacity' => 120],
        ['name' => 'Banquet', 'capacity' => 80],
        ['name' => 'Conference', 'capacity' => 96],
    ];
    $fallbackAddOns = [
        ['name' => 'Système audio', 'price' => '150 000 XOF'],
        ['name' => 'Téléviseurs / Moniteurs', 'price' => '100 000 XOF'],
        ['name' => 'Système de vidéoconférence Owl', 'price' => '75 000 XOF'],
        ['name' => 'Téléphone haut-parleur Polycom', 'price' => '50 000 XOF'],
        ['name' => 'Service café et thé', 'price' => '50 000 XOF'],
        ['name' => 'Eau filtrée', 'price' => '50 000 XOF'],
        ['name' => 'Chaises pliantes supplémentaires', 'price' => '2 000 XOF'],
        ['name' => 'Podium', 'price' => '75 000 XOF'],
        ['name' => 'Tables pliantes de 6 pieds', 'price' => '10 000 XOF'],
    ];
    $fallbackPolicies = [
        ['title' => 'Annulation', 'summary' => 'Strict', 'content' => 'Jusqu\'a 30 jours avant'],
        ['title' => 'Type de réservation', 'summary' => 'Demande de réservation', 'content' => 'Le propriétaire confirme votre demande'],
        ['title' => 'Fenêtre de réservation', 'summary' => '4 à 15 heures', 'content' => 'Horaires indicatifs selon disponibilité'],
        ['title' => 'Politique musicale et sonore', 'summary' => 'Musique amplifiée autorisée', 'content' => 'Selon les horaires et restrictions du lieu'],
    ];
    $fallbackFaqs = [
        ['question' => 'Les fournisseurs sont-ils vérifiés et assurés ?', 'answer' => 'Les espaces vérifiés passent par un contrôle BAOBAA avant publication.'],
        ['question' => 'Que contient exactement ce forfait ?', 'answer' => 'Le forfait inclut les elements affiches dans la section inclusions.'],
        ['question' => 'Cet événement est-il privé ?', 'answer' => 'Oui, la privatisation dépend du créneau choisi et des conditions du propriétaire.'],
        ['question' => 'Y a-t-il des frais cachés ?', 'answer' => 'Les frais obligatoires sont affichés avant validation de la réservation.'],
    ];
    $tabs = [
        'apercu' => 'Aperçu',
        'inclusions' => 'Ce qui est inclus',
        'commodites' => 'Espace et commodites',
        'modules' => 'Modules complementaires',
        'lieu' => 'Lieu et horaires',
        'conditions' => 'Choses a savoir',
        'avis' => 'Avis clients',
    ];
    $reservationAddOns = $addOns->isNotEmpty()
        ? $addOns->map(fn ($addOn) => [
            'name' => $addOn->name,
            'price' => number_format($addOn->price, 0, ',', ' ').' '.$addOn->currency,
        ])->values()->all()
        : $fallbackAddOns;
@endphp

<x-layouts.baobaa :title="$title.' - BAOBAA'">
    <main class="min-h-screen scroll-smooth bg-white text-[#151821]">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">marché événementiel</span>
                    </span>
                </a>

                <x-navigation.public-menu active="venues" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('owner.register') }}" class="hidden rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] lg:inline-flex">Devenir partenaire</a>
                    @endguest
                    <x-navigation.account-menu />
                </div>
            </div>
        </header>

        <section class="mx-auto max-w-7xl px-5 py-6 sm:px-8">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-[#6f7890]">
                <a href="{{ url('/') }}" class="hover:text-[#2f6bff]">Maison</a>
                <span>/</span>
                <span>{{ $city }}</span>
                <span>/</span>
                <span>{{ $category }}</span>
            </div>

            <div class="mt-4 flex flex-wrap items-start justify-between gap-5">
                <div>
                    <h1 class="max-w-4xl text-3xl font-extrabold leading-tight tracking-[-0.035em] text-[#151821] sm:text-4xl">{{ $title }}</h1>
                    <p class="mt-2 text-sm font-semibold text-[#6f7890]">{{ $district }}, {{ $city }} · {{ $category }}</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="venue-share-button" data-share-title="{{ $title }}" data-share-text="{{ $description }}" data-share-url="{{ url()->current() }}" class="rounded-full border border-[#d9e2f6] px-4 py-2 text-sm font-extrabold text-[#2f6bff] transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef4ff]">Partager</button>
                    <button type="button" id="venue-save-button" data-save-key="baobaa_saved_venue_{{ $venue?->id ?? md5($title) }}" data-save-title="{{ $title }}" data-save-url="{{ url()->current() }}" class="rounded-full border border-[#d9e2f6] px-4 py-2 text-sm font-extrabold text-[#2f6bff] transition hover:-translate-y-0.5 hover:border-[#2f6bff] hover:bg-[#eef4ff]">Sauvegarder</button>
                </div>
            </div>

            <div id="photos" class="mt-5 grid scroll-mt-28 gap-2 overflow-hidden rounded-[18px] lg:grid-cols-[1.08fr_.92fr]">
                <button type="button" data-gallery-index="0" class="relative min-h-[420px] cursor-zoom-in overflow-hidden text-left">
                    <img src="{{ $images[0] }}" alt="{{ $title }}" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full min-h-[420px] w-full object-cover transition duration-500 hover:scale-[1.02]">
                    <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-[#151821]">Lieu</span>
                </button>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach (array_slice($images, 1, 3) as $imageIndex => $image)
                        <button type="button" data-gallery-index="{{ $imageIndex + 1 }}" class="h-[206px] cursor-zoom-in overflow-hidden text-left">
                            <img src="{{ $image }}" alt="{{ $title }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover transition duration-500 hover:scale-[1.04]">
                        </button>
                    @endforeach
                    <div class="relative h-[206px] overflow-hidden">
                        <img src="{{ $images[0] }}" alt="{{ $title }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                        <button type="button" data-gallery-index="0" class="absolute bottom-4 right-4 rounded-full bg-white px-4 py-2 text-sm font-extrabold text-[#151821] shadow-lg transition hover:-translate-y-0.5">Voir les photos</button>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $capacity }}</span>
                <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">4-15 heures</span>
                <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $surface }}</span>
                <span class="rounded-full bg-[#eaf1ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $city }}</span>
                @foreach ($highlights as $highlight)
                    <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $highlight }}</span>
                @endforeach
            </div>

            @if (count($videos) > 0)
                <div class="mt-5 grid gap-3 md:grid-cols-2">
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
                    <a href="#{{ $target }}" data-tab-link class="shrink-0 rounded-full px-4 py-2.5 transition hover:bg-[#f4f7ff] hover:text-[#2f6bff]">{{ $tab }}</a>
                @endforeach
            </nav>
        </div>

        <section class="mx-auto grid max-w-7xl gap-8 px-5 pb-14 sm:px-8 lg:grid-cols-[1fr_360px] lg:items-start mt-5">
            <article class="space-y-8">
                <section class="rounded-2xl border border-[#dce6f7] bg-white p-5 shadow-sm shadow-[#173e7a]/5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="grid size-11 place-items-center rounded-full bg-[#eaf1ff] font-extrabold text-[#2f6bff]">B</span>
                            <div>
                                <p class="text-sm font-extrabold text-[#151821]">Présenté par {{ $venue?->ownerProfile?->business_name ?? 'BAOBAA Partner' }}</p>
                                <p class="text-xs font-semibold text-[#8a94aa]">Répond en moins de 2 heures</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <a href="{{ $venue?->ownerProfile ? route('owner-profiles.show', $venue->ownerProfile->public_uuid) : route('owner-profiles.show', '2d08df2a-8d0f-42a5-a22e-47a617a7b101') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 ring-4 ring-[#eaf1ff] transition hover:-translate-y-0.5 hover:bg-[#2258df]">
                                Découvrir le partenaire
                                <span class="text-base leading-none">→</span>
                            </a>
                            <button class="rounded-full border border-[#cfdaf5] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Contacter le fournisseur</button>
                        </div>
                    </div>
                </section>

                <section id="apercu" class="scroll-mt-32">
                    <h2 class="text-xl font-extrabold text-[#151821]">Aperçu</h2>
                    <p class="mt-3 text-sm font-medium leading-7 text-[#4d5872]">{{ $description }}</p>
                </section>

                <section>
                    <h2 class="text-xl font-extrabold text-[#151821]">Description</h2>
                    <p class="mt-3 text-sm font-medium leading-7 text-[#4d5872]">{{ $description }}</p>
                    <ul class="mt-4 space-y-2 text-sm font-medium text-[#4d5872]">
                        @foreach (($venue?->house_rules ?: ['Lumière naturelle disponible', 'Toilettes sur place', 'Parking à proximité', 'Musique amplifiée autorisée selon horaires']) as $rule)
                            <li class="flex gap-2"><span class="text-[#2f6bff]">•</span>{{ $rule }}</li>
                        @endforeach
                    </ul>
                </section>

                <section id="inclusions" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Qu'est-ce qui est inclus dans votre réservation ?</h2>
                    <div class="mt-4 grid overflow-hidden rounded-2xl border border-[#e4ebf8] bg-white sm:grid-cols-2">
                        @foreach ($includedItems as $item)
                            <div class="flex items-center gap-3 border-b border-[#edf2fb] px-4 py-3 text-sm font-semibold text-[#4d5872] even:bg-[#fbfcff] sm:odd:border-r">
                                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-[#eaf1ff] text-xs text-[#2f6bff]">✓</span>{{ $item }}
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="commodites" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-extrabold text-[#151821]">Espace et commodités</h2>
                        <span class="text-xs font-extrabold text-[#8a94aa]">Capacité par configuration</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @forelse ($configurations as $configuration)
                            <div class="rounded-2xl bg-[#f7f9fd] p-5 text-center">
                                <p class="text-sm font-extrabold text-[#151821]">{{ $configuration->name }}</p>
                                <p class="mt-2 text-2xl font-extrabold text-[#2f6bff]">{{ $configuration->capacity }}</p>
                            </div>
                        @empty
                            @foreach ($fallbackConfigurations as $configuration)
                                <div class="rounded-2xl bg-[#f7f9fd] p-5 text-center">
                                    <p class="text-sm font-extrabold text-[#151821]">{{ $configuration['name'] }}</p>
                                    <p class="mt-2 text-2xl font-extrabold text-[#2f6bff]">{{ $configuration['capacity'] }}</p>
                                </div>
                            @endforeach
                        @endforelse
                    </div>

                    <div class="mt-5 divide-y divide-[#edf0f7] rounded-2xl border border-[#edf0f7]">
                        @foreach ($amenities as $index => $amenity)
                            <details class="group p-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-extrabold text-[#151821]">
                                    {{ $amenity }}
                                    <span class="text-[#8a94aa] group-open:rotate-180">⌄</span>
                                </summary>
                                <p class="mt-2 text-sm font-medium text-[#6f7890]">{{ $amenityDetails->get($index)['detail'] ?? "Information renseignée par le propriétaire de l'espace." }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section id="modules" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Modules complémentaires du fournisseur</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($addOns as $addOn)
                            <div class="rounded-2xl border border-[#dce6f7] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                <p class="text-sm font-extrabold text-[#151821]">{{ $addOn->name }}</p>
                                <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ number_format($addOn->price, 0, ',', ' ') }} {{ $addOn->currency }}</p>
                            </div>
                        @empty
                            @foreach ($fallbackAddOns as $addOn)
                                <div class="rounded-2xl border border-[#dce6f7] bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                    <p class="text-sm font-extrabold text-[#151821]">{{ $addOn['name'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ $addOn['price'] }}</p>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </section>

                <section id="lieu" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Emplacement et disponibilité</h2>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-[#dce6f7] bg-[#dfefff]">
                        <div class="flex h-72 items-center justify-center bg-[linear-gradient(135deg,#bfe9ff,#dce7ff,#f2f7ff)] text-center">
                            <div>
                                <p class="text-2xl font-extrabold text-[#2f6bff]">{{ $city }}</p>
                                <p class="mt-2 text-sm font-semibold text-[#4d5872]">L'adresse exacte sera communiquée une fois la demande confirmée.</p>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-[#4d5872]">{{ $district }}, {{ $city }} · Contactez le fournisseur pour connaître les disponibilités.</p>
                </section>

                <section id="conditions" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Choses a savoir</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach (array_slice($policies->isNotEmpty() ? $policies->toArray() : $fallbackPolicies, 0, 3) as $policy)
                            <div class="rounded-2xl border border-[#dce6f7] p-4">
                                <p class="text-xs font-extrabold text-[#8a94aa]">{{ $policy['title'] }}</p>
                                <p class="mt-1 text-sm font-extrabold text-[#151821]">{{ $policy['summary'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ $policy['content'] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 divide-y divide-[#edf0f7] rounded-2xl border border-[#edf0f7]">
                        @foreach ($policies->isNotEmpty() ? $policies : collect($fallbackPolicies) as $policy)
                            <details class="group p-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-extrabold text-[#151821]">
                                    {{ is_array($policy) ? $policy['title'] : $policy->title }}
                                    <span class="text-[#8a94aa] group-open:rotate-180">⌄</span>
                                </summary>
                                <p class="mt-2 text-sm font-medium text-[#6f7890]">{{ is_array($policy) ? $policy['content'] : $policy->content }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section class="border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Foire aux questions</h2>
                    <div class="mt-4 divide-y divide-[#edf0f7]">
                        @foreach ($faqs->isNotEmpty() ? $faqs : collect($fallbackFaqs) as $faq)
                            <details class="group py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-extrabold text-[#151821]">
                                    {{ is_array($faq) ? $faq['question'] : $faq->question }}
                                    <span class="text-[#8a94aa] group-open:rotate-180">⌄</span>
                                </summary>
                                <p class="mt-2 text-sm font-medium text-[#6f7890]">{{ is_array($faq) ? $faq['answer'] : $faq->answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section id="avis" class="scroll-mt-32 border-t border-[#edf0f7] pt-7">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-extrabold text-[#151821]">Avis clients</h2>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">Seuls les clients ayant réservé cet espace peuvent publier un commentaire.</p>
                        </div>
                        <span class="rounded-full bg-[#eaf1ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $approvedReviews->count() }} avis publié{{ $approvedReviews->count() > 1 ? 's' : '' }}</span>
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
                                Aucun avis publié pour le moment. Les premiers commentaires apparaîtront après validation.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5 rounded-2xl border border-[#dce6f7] bg-white p-5 shadow-sm">
                        @guest
                            <p class="text-sm font-extrabold text-[#151821]">Connectez-vous pour commenter cet espace.</p>
                            <a href="{{ route('portal.login', ['portal' => 'client']) }}" class="mt-3 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Connexion client</a>
                        @else
                            @if (! $venue)
                                <p class="text-sm font-semibold text-[#6f7890]">Les avis seront disponibles lorsque cet espace sera enregistré en base.</p>
                            @elseif ($existingReview)
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
                                        <textarea name="comment" rows="4" minlength="10" maxlength="1200" placeholder="Partagez votre retour après réservation..." class="mt-1 w-full resize-none bg-transparent text-sm font-semibold leading-6 text-[#151821] outline-none placeholder:text-[#a0a8b8]">{{ old('comment') }}</textarea>
                                    </label>
                                    <button class="justify-self-start rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Envoyer mon avis</button>
                                </form>
                            @else
                                <p class="text-sm font-extrabold text-[#151821]">Avis réservé aux clients ayant déjà réservé cet espace.</p>
                                <p class="mt-1 text-xs font-semibold leading-5 text-[#6f7890]">Après une réservation confirmée ou terminée, le formulaire de commentaire sera disponible ici.</p>
                            @endif
                        @endguest
                    </div>
                </section>

                <section class="border-t border-[#edf0f7] pt-7">
                    <h2 class="text-xl font-extrabold text-[#151821]">Plus de salles similaires</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($similarVenues as $similarVenue)
                            <a href="{{ route('venues.show', $similarVenue['slug']) }}" class="group overflow-hidden rounded-2xl border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-[#173e7a]/10">
                                <div class="relative overflow-hidden">
                                    <img src="{{ $similarVenue['image'] }}" alt="{{ $similarVenue['title'] }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-40 w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                                    <span class="absolute right-3 top-3 rounded-full bg-white/94 px-3 py-1 text-xs font-extrabold text-[#2f6bff] shadow-sm">Voir les détails</span>
                                </div>
                                <div class="p-4">
                                    <p class="line-clamp-2 text-sm font-extrabold text-[#151821]">{{ $similarVenue['title'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ $similarVenue['city'] }} · {{ $similarVenue['capacity'] }}</p>
                                    <div class="mt-3 flex items-center justify-between border-t border-[#edf2fb] pt-3">
                                        <p class="text-xs font-extrabold text-[#151821]">{{ $similarVenue['price'] }}</p>
                                        <span class="text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">Ouvrir →</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            </article>

            <aside class="sticky top-28">
                <div class="overflow-hidden rounded-[18px] bg-white shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7]">
                    <div class="border-b border-[#edf2fb] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-2xl font-extrabold tracking-[-0.035em] text-[#151821]">{{ $price }}</p>
                                <p class="mt-0.5 text-[11px] font-bold text-[#6f7890]">Tarif horaire · acompte {{ $reservation }}</p>
                            </div>
                            <span class="rounded-full bg-[#eaf1ff] px-2.5 py-0.5 text-[11px] font-extrabold text-[#2f6bff]">{{ $rating }} ★</span>
                        </div>
                    </div>

                    @if (session('booking_status'))
                        <div class="mx-4 mt-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-xs font-extrabold leading-5 text-[#2f6bff]">
                            {{ session('booking_status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ $venue ? route('bookings.store', $venue) : '#' }}" class="space-y-3 p-4">
                        @csrf
                        <div class="grid grid-cols-2 overflow-hidden rounded-[14px] border border-[#dce6f7] bg-white">
                            <label class="border-b border-r border-[#edf2fb] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Date de début</span>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-0.5 w-full bg-transparent text-xs font-bold text-[#151821] outline-none">
                            </label>
                            <label class="border-b border-[#edf2fb] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Heure début</span>
                                <input type="time" name="starts_at" value="{{ old('starts_at') }}" class="mt-0.5 w-full bg-transparent text-xs font-bold text-[#151821] outline-none">
                            </label>
                            <label class="border-r border-[#edf2fb] px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Date de fin</span>
                                <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-0.5 w-full bg-transparent text-xs font-bold text-[#151821] outline-none">
                            </label>
                            <label class="px-3 py-2.5">
                                <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Heure de fin</span>
                                <input type="time" name="ends_at" value="{{ old('ends_at') }}" class="mt-0.5 w-full bg-transparent text-xs font-bold text-[#151821] outline-none">
                            </label>
                        </div>

                        <label class="block rounded-[14px] border border-[#dce6f7] bg-[#fbfcff] px-3 py-2.5">
                            <span class="block text-[10px] font-extrabold uppercase text-[#8a94aa]">Type d'événement</span>
                            <select name="event_type" class="mt-0.5 w-full bg-transparent text-xs font-bold text-[#151821] outline-none">
                                <option value="conference">Conférence / Séminaire</option>
                                <option value="mariage">Mariage / Célébration</option>
                                <option>Concert / Spectacle</option>
                                <option>Lancement de produit</option>
                            </select>
                        </label>

                        <label class="block rounded-[14px] border border-[#dce6f7] bg-[#fbfcff] px-3 py-2.5">
                            <span class="block whitespace-nowrap text-[9px] font-extrabold uppercase tracking-[0.02em] text-[#8a94aa]">Invités</span>
                            <input type="number" min="1" max="{{ $venue?->max_capacity ?? 450 }}" name="guests_count" value="{{ old('guests_count') }}" placeholder="Ex : 120" class="mt-1 block w-full min-w-0 bg-transparent text-xs font-bold text-[#151821] outline-none">
                        </label>

                        <div class="rounded-[14px] border border-[#dce6f7] bg-white p-3">
                            <div class="flex w-full items-center justify-between text-left text-xs font-extrabold text-[#151821]">
                                <span>Modules complémentaires</span>
                                <span class="rounded-full bg-[#eaf1ff] px-2 py-0.5 text-[10px] text-[#2f6bff]">{{ count($reservationAddOns) }} dispo.</span>
                            </div>
                            <button type="button" id="open-addons-modal" class="mt-2.5 flex w-full items-center justify-between rounded-[14px] border border-dashed border-[#b9caff] bg-[#f7faff] px-3 py-2.5 text-left text-xs font-extrabold text-[#2f6bff] transition hover:border-[#2f6bff] hover:bg-[#eef4ff]">
                                <span>Ajouter des modules complémentaires</span>
                                <span class="text-base leading-none">+</span>
                            </button>
                            <p id="selected-addons-summary" class="mt-2.5 hidden text-[11px] font-bold leading-5 text-[#6f7890]"></p>
                        </div>

                        <div class="space-y-1.5 rounded-[14px] bg-[#f7f9fd] p-3 text-xs font-bold text-[#4d5872]">
                            <div class="flex justify-between"><span>Réservation</span><span>{{ $reservation }}</span></div>
                            <div class="flex justify-between"><span>Frais de service</span><span>Calculés au paiement</span></div>
                        </div>

                        @guest
                            <a href="{{ route('portal.login', ['portal' => 'client', 'redirect' => url()->current()]) }}" class="flex w-full justify-center rounded-[14px] bg-[#2f6bff] px-4 py-3 text-xs font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Enregistrer ma réservation</a>
                            <p class="text-center text-[11px] font-bold leading-5 text-[#6f7890]">Connexion client obligatoire avant l'enregistrement et le paiement.</p>
                        @else
                            @if (auth()->user()?->hasPortal(\App\Enums\UserRole::Client))
                                <button type="submit" @disabled(! $venue) class="w-full rounded-[14px] bg-[#2f6bff] px-4 py-3 text-xs font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df] disabled:cursor-not-allowed disabled:bg-[#b7c6e5] disabled:shadow-none">Enregistrer ma réservation</button>
                            @else
                                <a href="{{ route('venues.index') }}" class="flex w-full justify-center rounded-[14px] bg-[#07152f] px-4 py-3 text-xs font-extrabold text-white shadow-lg shadow-[#07152f]/15">Explorer comme visiteur</a>
                                <p class="text-center text-[11px] font-bold leading-5 text-[#6f7890]">La réservation est réservée aux comptes clients.</p>
                            @endif
                        @endguest
                    </form>
                </div>
                <div class="mt-4 rounded-3xl border border-[#cfe0ff] bg-[#f7faff] p-5">
                    <p class="text-sm font-extrabold text-[#2f6bff]">Réservations sécurisées et fiables</p>
                    <p class="mt-2 text-xs font-semibold leading-5 text-[#6f7890]">Chaque prestataire est vérifié quant à ses qualités. Vous pouvez réserver en toute tranquillité.</p>
                </div>
            </aside>
        </section>

        <div id="addons-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/72 px-4 py-8 backdrop-blur-sm" aria-hidden="true">
            <div class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-[28px] bg-white shadow-2xl ring-1 ring-black/10">
                <div class="flex items-center justify-between border-b border-[#e6eaf2] px-6 py-5 sm:px-8">
                    <h2 class="text-2xl font-extrabold tracking-[-0.035em] text-[#151821] sm:text-3xl">Modules complémentaires</h2>
                    <button type="button" id="close-addons-modal" class="grid size-10 place-items-center rounded-full text-3xl font-light text-[#4d5872] transition hover:bg-[#f4f7ff]" aria-label="Fermer les modules">&times;</button>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5 sm:px-8">
                    <label class="block">
                        <span class="sr-only">Rechercher un module complémentaire</span>
                        <input id="addons-search" type="search" placeholder="Rechercher un module complémentaire" class="w-full rounded-2xl border border-[#4d5872] px-5 py-4 text-xl font-semibold text-[#151821] outline-none ring-2 ring-[#151821]/20 transition placeholder:text-[#a6abb6] focus:border-[#2f6bff] focus:ring-[#2f6bff]/25">
                    </label>

                    <div id="addons-list" class="mt-5 divide-y divide-[#e6eaf2]">
                        @foreach ($reservationAddOns as $index => $addOn)
                            <label data-addon-row class="flex cursor-pointer items-start gap-4 py-4 transition hover:bg-[#fbfcff]" data-addon-name="{{ strtolower($addOn['name']) }}">
                                <input type="checkbox" value="{{ $addOn['name'] }}" data-addon-checkbox class="mt-1 size-5 shrink-0 rounded border-[#d4d9e3] accent-[#2f6bff]">
                                <span class="min-w-0">
                                    <span class="block truncate text-xl font-bold text-[#5c6373]">{{ $addOn['name'] }}</span>
                                    <span class="mt-1 block text-base font-bold text-[#8a94aa]">{{ $addOn['price'] }} chacun</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-[#e6eaf2] bg-white px-6 py-5 sm:px-8">
                    <button type="button" id="confirm-addons" class="w-full rounded-full bg-[#2f6bff] px-6 py-4 text-xl font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Fait</button>
                </div>
            </div>
        </div>

        <div id="venue-gallery-modal" class="fixed inset-0 z-50 hidden bg-[#071225]/95 p-4 text-white" aria-hidden="true">
            <button type="button" id="gallery-close" class="absolute right-5 top-5 z-10 grid size-11 place-items-center rounded-full bg-white/10 text-2xl font-bold backdrop-blur transition hover:bg-white/20" aria-label="Fermer la galerie">&times;</button>
            <button type="button" id="gallery-prev" class="absolute left-5 top-1/2 z-10 hidden size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-3xl font-bold backdrop-blur transition hover:bg-white/20 sm:grid" aria-label="Image précédente">‹</button>
            <button type="button" id="gallery-next" class="absolute right-5 top-1/2 z-10 hidden size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-3xl font-bold backdrop-blur transition hover:bg-white/20 sm:grid" aria-label="Image suivante">›</button>

            <div class="mx-auto flex h-full max-w-6xl flex-col justify-center gap-4">
                <div class="flex min-h-0 flex-1 items-center justify-center">
                    <img id="gallery-active-image" src="{{ $galleryImages[0] }}" alt="{{ $title }}" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="max-h-[76vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl">
                </div>
                <div class="mx-auto flex max-w-full gap-3 overflow-x-auto rounded-2xl bg-white/8 p-3 baobaa-scrollbar-none">
                    @foreach ($galleryImages as $imageIndex => $image)
                        <button type="button" data-gallery-thumb="{{ $imageIndex }}" class="h-20 w-28 shrink-0 overflow-hidden rounded-xl ring-2 ring-transparent transition">
                            <img src="{{ $image }}" alt="{{ $title }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
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
                const openButtons = document.querySelectorAll('[data-gallery-index]');
                const thumbButtons = document.querySelectorAll('[data-gallery-thumb]');
                const tabLinks = document.querySelectorAll('[data-tab-link]');
                const sections = [...tabLinks].map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
                const addOnsModal = document.getElementById('addons-modal');
                const addOnsSearch = document.getElementById('addons-search');
                const addOnsRows = document.querySelectorAll('[data-addon-row]');
                const addOnsCheckboxes = document.querySelectorAll('[data-addon-checkbox]');
                const selectedAddOnsSummary = document.getElementById('selected-addons-summary');
                let activeIndex = 0;
                let toastTimer;

                const showToast = (message) => {
                    clearTimeout(toastTimer);
                    actionToast.textContent = message;
                    actionToast.classList.remove('hidden');
                    toastTimer = setTimeout(() => actionToast.classList.add('hidden'), 2600);
                };

                const setActiveImage = (index) => {
                    activeIndex = (index + images.length) % images.length;
                    activeImage.src = images[activeIndex];
                    thumbButtons.forEach((button) => {
                        const selected = Number(button.dataset.galleryThumb) === activeIndex;
                        button.classList.toggle('ring-white', selected);
                        button.classList.toggle('opacity-60', ! selected);
                    });
                };

                const openGallery = (index) => {
                    setActiveImage(index);
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                };

                const closeGallery = () => {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                };

                const updateSelectedAddOns = () => {
                    const selected = [...addOnsCheckboxes]
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);

                    selectedAddOnsSummary.classList.toggle('hidden', selected.length === 0);
                    selectedAddOnsSummary.textContent = selected.length > 0
                        ? `${selected.length} module${selected.length > 1 ? 's' : ''} sélectionné${selected.length > 1 ? 's' : ''} : ${selected.join(', ')}`
                        : '';
                };

                const openAddOnsModal = () => {
                    addOnsModal.classList.remove('hidden');
                    addOnsModal.classList.add('flex');
                    addOnsModal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                    setTimeout(() => addOnsSearch.focus(), 60);
                };

                const closeAddOnsModal = () => {
                    addOnsModal.classList.add('hidden');
                    addOnsModal.classList.remove('flex');
                    addOnsModal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                    updateSelectedAddOns();
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
                    const saved = savedVenues().some((venue) => venue.key === saveButton.dataset.saveKey);
                    saveButton.textContent = saved ? 'Sauvegardé' : 'Sauvegarder';
                    saveButton.classList.toggle('bg-[#2f6bff]', saved);
                    saveButton.classList.toggle('text-white', saved);
                    saveButton.classList.toggle('border-[#2f6bff]', saved);
                };

                shareButton.addEventListener('click', async () => {
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

                saveButton.addEventListener('click', () => {
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

                openButtons.forEach((button) => button.addEventListener('click', () => openGallery(Number(button.dataset.galleryIndex))));
                thumbButtons.forEach((button) => button.addEventListener('click', () => setActiveImage(Number(button.dataset.galleryThumb))));
                document.getElementById('gallery-close').addEventListener('click', closeGallery);
                document.getElementById('gallery-prev').addEventListener('click', () => setActiveImage(activeIndex - 1));
                document.getElementById('gallery-next').addEventListener('click', () => setActiveImage(activeIndex + 1));
                document.getElementById('open-addons-modal').addEventListener('click', openAddOnsModal);
                document.getElementById('close-addons-modal').addEventListener('click', closeAddOnsModal);
                document.getElementById('confirm-addons').addEventListener('click', closeAddOnsModal);
                addOnsCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectedAddOns));
                addOnsSearch.addEventListener('input', () => {
                    const query = addOnsSearch.value.trim().toLowerCase();

                    addOnsRows.forEach((row) => {
                        row.classList.toggle('hidden', ! row.dataset.addonName.includes(query));
                    });
                });
                addOnsModal.addEventListener('click', (event) => {
                    if (event.target === addOnsModal) {
                        closeAddOnsModal();
                    }
                });
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeGallery();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (! addOnsModal.classList.contains('hidden') && event.key === 'Escape') {
                        closeAddOnsModal();

                        return;
                    }

                    if (modal.classList.contains('hidden')) {
                        return;
                    }

                    if (event.key === 'Escape') closeGallery();
                    if (event.key === 'ArrowLeft') setActiveImage(activeIndex - 1);
                    if (event.key === 'ArrowRight') setActiveImage(activeIndex + 1);
                });

                tabLinks.forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();

                        const target = document.querySelector(link.getAttribute('href'));

                        if (! target) {
                            return;
                        }

                        const offset = 128;
                        const top = target.getBoundingClientRect().top + window.scrollY - offset;

                        window.scrollTo({ top, behavior: 'smooth' });
                        history.replaceState(null, '', link.getAttribute('href'));
                    });
                });

                const setActiveTab = () => {
                    const current = [...sections].reverse().find((section) => section.getBoundingClientRect().top <= 150) ?? sections[0];

                    tabLinks.forEach((link) => {
                        const active = link.getAttribute('href') === `#${current.id}`;
                        link.classList.toggle('bg-[#2f6bff]', active);
                        link.classList.toggle('text-white', active);
                        link.classList.toggle('text-[#6f7890]', ! active);
                    });
                };

                setActiveTab();
                refreshSaveButton();
                document.addEventListener('scroll', setActiveTab, { passive: true });
            })();
        </script>
    </main>
</x-layouts.baobaa>
