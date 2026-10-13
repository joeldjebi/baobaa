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

@php
    $heroVenue = $popularListings->first();
    $storyVenues = $popularListings->take(3)->values();
    $paymentLabels = collect(\App\Enums\PaymentMethod::cases())->reject(fn ($method) => $method === \App\Enums\PaymentMethod::BaobaaCheckout)->map->label()->values();
    $contact = config('baobaa.contact');
    $faqs = [
        ['Comment réserver un espace sur BAOBAA ?', 'Choisissez un espace, indiquez votre date, vos horaires et le nombre d’invités, puis envoyez votre demande. Le propriétaire la valide, une proforma récapitule le prix et vous payez l’acompte pour bloquer la date.'],
        ['À quoi sert l’acompte ?', 'L’acompte bloque votre date : personne d’autre ne peut réserver le même créneau. Son montant est affiché avant le paiement et le solde se règle selon la proforma validée avec le propriétaire.'],
        ['Puis-je négocier le prix ?', 'Oui. Lors de votre demande, vous pouvez proposer un montant. Le propriétaire répond depuis la messagerie de la réservation et la proforma est mise à jour.'],
        ['Quels moyens de paiement sont acceptés ?', 'Wave, Orange Money, MTN Money, Moov Money et le virement bancaire, selon ce que chaque propriétaire accepte. Les moyens disponibles sont affichés sur chaque annonce.'],
        ['Puis-je réserver des professionnels ou du matériel sans salle ?', 'Oui. Vous pouvez composer un événement uniquement avec des professionnels (son, lumière, photo…) ou louer et acheter du matériel, avec livraison ou retrait.'],
        ['Comment publier mon espace ?', 'Créez votre compte partenaire gratuitement. Notre équipe vérifie votre profil, puis vous publiez vos espaces, vos prix et votre calendrier de disponibilités.'],
    ];
@endphp

<x-layouts.baobaa title="BAOBAA - Réservez une salle ou un espace pour votre événement" description="Trouvez et réservez la salle idéale pour votre mariage, séminaire, conférence ou soirée en Afrique francophone. Espaces vérifiés, prix clairs et paiement sécurisé.">
    <div class="baobaa-scroll-progress" data-scroll-progress aria-hidden="true"></div>

    <main class="min-h-screen overflow-x-clip bg-[#f5f7ff] text-[#07152f]">
        <x-navigation.public-header active="explore" />

        {{-- Hero --}}
        <section class="relative px-5 pb-16 pt-10 sm:px-8 lg:pb-24 lg:pt-14">
            <div class="absolute inset-0 opacity-[0.35] [background-image:linear-gradient(rgba(47,107,255,.10)_1px,transparent_1px),linear-gradient(90deg,rgba(47,107,255,.10)_1px,transparent_1px)] [background-size:56px_56px] [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_75%)]" aria-hidden="true"></div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-14 lg:grid-cols-[1.08fr_.92fr]">
                <div>
                    <p class="animate-baobaa-fade-up inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 text-xs font-extrabold text-[#2f6bff] shadow-sm ring-1 ring-[#dce6f7]">
                        <span class="relative grid size-2.5 place-items-center"><span class="baobaa-pulse-ring absolute size-2.5 rounded-full bg-[#2f6bff]"></span><span class="size-2 rounded-full bg-[#2f6bff]"></span></span>
                        La plateforme événementielle d’Afrique francophone
                    </p>

                    @php
                        $headlineWords = explode(' ', 'Le lieu parfait pour votre');
                    @endphp
                    <h1 class="mt-6 font-display text-[44px] font-extrabold leading-[0.98] tracking-[-0.045em] sm:text-[64px] lg:text-[76px]">
                        <span class="sr-only">Le lieu parfait pour votre événement</span>
                        <span aria-hidden="true">
                            @foreach ($headlineWords as $wordIndex => $word)
                                <span class="motion-word" style="--word-index: {{ $wordIndex }}">{{ $word }}</span>
                            @endforeach
                            <span class="motion-word relative" style="--word-index: {{ count($headlineWords) }}">
                                <span class="baobaa-rotating-word pb-[.1em]" data-rotating-words="2400">@foreach (['événement.', 'mariage.', 'séminaire.', 'gala.', 'anniversaire.', 'lancement.'] as $rotatingWord)<span class="baobaa-gradient-text">{{ $rotatingWord }}</span>@endforeach</span>
                                <svg class="absolute -bottom-2 left-0 h-3 w-full text-[#2f6bff]" viewBox="0 0 300 12" preserveAspectRatio="none" aria-hidden="true"><path d="M2 9C60 3 160 1 298 6" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                            </span>
                        </span>
                    </h1>

                    <p class="animate-baobaa-fade-up mt-6 max-w-xl text-base font-semibold leading-7 text-[#52617b] sm:text-lg" style="animation-delay: .5s">
                        Salles, jardins, rooftops et auditoriums vérifiés. Comparez les prix, voyez les dates libres et bloquez la vôtre avec un acompte sécurisé.
                    </p>

                    <div class="animate-baobaa-fade-up relative z-30 mt-7 max-w-xl" style="animation-delay: .6s" data-hero-search>
                        <div class="flex w-max max-w-full gap-1 overflow-x-auto rounded-full bg-white p-1 text-sm font-extrabold shadow-sm ring-1 ring-[#dce6f7] baobaa-scrollbar-none" role="tablist" aria-label="Que recherchez-vous ?">
                            @foreach (['venues' => 'Espaces', 'professionals' => 'Professionnels', 'products' => 'Matériel'] as $tab => $label)
                                <button type="button" role="tab" data-hero-tab="{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="shrink-0 rounded-full px-4 py-2 text-[#52617b] transition hover:text-[#2f6bff] aria-selected:bg-[#07152f] aria-selected:text-white">{{ $label }}</button>
                            @endforeach
                        </div>

                        <form method="GET" action="{{ route('venues.index') }}" data-hero-panel="venues" class="mt-3 rounded-[1.6rem] bg-white p-2 shadow-[0_24px_60px_rgba(18,30,75,.14)] ring-1 ring-[#dce6f7]" data-home-search-form>
                            <div class="grid gap-1 sm:grid-cols-[1fr_1fr_auto]">
                                <label class="rounded-2xl px-4 py-2.5 transition hover:bg-[#f5f7ff]">
                                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.06em] text-[#7d879d]">Quoi</span>
                                    <input type="text" name="q" autocomplete="off" data-search-input placeholder="Salle de mariage…" class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:font-semibold placeholder:text-[#9aa3b5]">
                                </label>
                                <label class="relative rounded-2xl px-4 py-2.5 transition hover:bg-[#f5f7ff]" data-city-autocomplete>
                                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.06em] text-[#7d879d]">Où</span>
                                    <input type="text" name="city" autocomplete="off" data-city-input placeholder="Ville ou quartier" class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:font-semibold placeholder:text-[#9aa3b5]">
                                    <div data-city-list class="absolute left-0 right-0 top-[calc(100%+8px)] z-[90] hidden rounded-2xl border border-[#dce6f7] bg-white p-2 text-left shadow-2xl shadow-[#173e7a]/16"></div>
                                </label>
                                <button class="inline-flex items-center justify-center gap-2 rounded-[1.2rem] bg-[#2f6bff] px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/30 transition hover:-translate-y-0.5 hover:bg-[#2258df]">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    Rechercher
                                </button>
                            </div>
                            <div data-search-results class="relative z-[85] mt-2 hidden rounded-3xl border border-[#dce6f7] bg-[#f8fbff] p-3 text-left"></div>
                        </form>

                        <form method="GET" action="{{ route('service-provider-profiles.index') }}" data-hero-panel="professionals" hidden class="mt-3 rounded-[1.6rem] bg-white p-2 shadow-[0_24px_60px_rgba(18,30,75,.14)] ring-1 ring-[#dce6f7]">
                            <div class="grid gap-1 sm:grid-cols-[1fr_auto]">
                                <label class="rounded-2xl px-4 py-2.5">
                                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.06em] text-[#7d879d]">Quel professionnel ?</span>
                                    <input type="text" name="q" placeholder="Sonorisation, photographe, décoration…" class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:font-semibold placeholder:text-[#9aa3b5]">
                                </label>
                                <button class="rounded-[1.2rem] bg-[#2f6bff] px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/30">Trouver</button>
                            </div>
                        </form>

                        <form method="GET" action="{{ route('products.index') }}" data-hero-panel="products" hidden class="mt-3 rounded-[1.6rem] bg-white p-2 shadow-[0_24px_60px_rgba(18,30,75,.14)] ring-1 ring-[#dce6f7]">
                            <div class="grid gap-1 sm:grid-cols-[1fr_auto]">
                                <label class="rounded-2xl px-4 py-2.5">
                                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.06em] text-[#7d879d]">Quel matériel ?</span>
                                    <input type="text" name="q" placeholder="Chaises, tente, enceinte…" class="mt-0.5 w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:font-semibold placeholder:text-[#9aa3b5]">
                                </label>
                                <button class="rounded-[1.2rem] bg-[#2f6bff] px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/30">Voir le matériel</button>
                            </div>
                        </form>
                    </div>

                    <div class="animate-baobaa-fade-up mt-7 flex flex-wrap items-center gap-x-4 gap-y-2" style="animation-delay: .75s">
                        <span class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#7d879d]">Payez avec</span>
                        @foreach ($paymentLabels as $paymentIndex => $paymentLabel)
                            <span class="inline-flex items-center gap-1.5 text-sm font-bold text-[#38445c]"><span class="size-2 rounded-full" style="background: {{ ['#1dc8ff', '#ff7a00', '#ffcb05', '#1f4fbf', '#0f9f6e'][$paymentIndex % 5] }}"></span>{{ $paymentLabel }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Phone mockup --}}
                <div class="relative mx-auto grid w-full max-w-[520px] place-items-center py-6" aria-hidden="true">
                    <div class="absolute size-[360px] rounded-full bg-[linear-gradient(140deg,#2f6bff,#7c5cff)] opacity-95 sm:size-[420px]" data-reveal="scale"></div>
                    <svg class="baobaa-orbit absolute size-[460px] text-[#2f6bff]/45 sm:size-[520px]" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="49" stroke="currentColor" stroke-width=".35" stroke-dasharray="1.4 1.6"/></svg>

                    <div class="baobaa-aurora relative" data-aurora="-14">
                        <div class="baobaa-phone">
                            <div class="baobaa-phone-notch"></div>
                            <div class="baobaa-phone-screen px-4 pt-12">
                                <div class="flex items-center justify-between text-[11px] font-extrabold text-[#07152f]"><span>9:41</span><span class="rounded-full bg-[#eef4ff] px-2 py-0.5 text-[#2f6bff]">BAOBAA</span></div>
                                <p class="mt-4 font-display text-xl font-extrabold leading-tight text-[#07152f]">On fête quoi&nbsp;?</p>
                                <div class="mt-3 flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-[11px] font-bold text-[#9aa3b5] ring-1 ring-[#e4ebf8]">
                                    <svg class="size-3.5 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                                    Salle, jardin, rooftop…
                                </div>
                                @if ($heroVenue)
                                    <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-lg shadow-[#173e7a]/10 ring-1 ring-[#e4ebf8]">
                                        <img src="{{ $heroVenue['image'] }}" alt="" width="270" height="150" decoding="async" referrerpolicy="no-referrer" class="h-32 w-full object-cover">
                                        <div class="p-3">
                                            <p class="line-clamp-1 text-xs font-extrabold text-[#07152f]">{{ $heroVenue['title'] }}</p>
                                            <p class="mt-0.5 text-[10px] font-bold text-[#7d879d]">{{ $heroVenue['city'] }} · {{ $heroVenue['guests'] }}</p>
                                            <div class="mt-2 flex items-center justify-between">
                                                <span class="text-xs font-extrabold text-[#2f6bff]">{{ $heroVenue['price'] }}</span>
                                                <span class="rounded-full bg-[#2f6bff] px-3 py-1 text-[10px] font-extrabold text-white">Réserver</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <p class="mt-4 text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#7d879d]">Dates libres</p>
                                <div class="mt-2 grid grid-cols-7 gap-1 text-center text-[10px] font-extrabold" data-animate-on-view>
                                    @foreach (range(1, 14) as $day)
                                        @php
                                            $isBooked = in_array($day, [3, 4, 9], true);
                                            $isChosen = $day === 12;
                                        @endphp
                                        <span @class([
                                            'baobaa-calendar-cell grid h-6 place-items-center rounded-md',
                                            'bg-[#eceff5] text-[#b4bccb] line-through' => $isBooked,
                                            'bg-[#2f6bff] text-white shadow-md shadow-[#2f6bff]/40' => $isChosen,
                                            'bg-white text-[#07152f] ring-1 ring-[#e4ebf8]' => ! $isBooked && ! $isChosen,
                                        ]) style="--cell-delay: {{ 400 + $day * 45 }}ms">{{ $day }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="baobaa-float-card absolute -left-2 top-16 rounded-2xl bg-white px-3.5 py-2.5 shadow-xl shadow-[#173e7a]/15 ring-1 ring-[#e4ebf8] sm:-left-6" style="--float-delay: -1s" data-reveal="left">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-8 place-items-center rounded-full bg-[#12b76a] text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg></span>
                            <span><span class="block text-xs font-extrabold text-[#07152f]">Demande acceptée</span><span class="block text-[10px] font-bold text-[#7d879d]">Le propriétaire a répondu</span></span>
                        </div>
                    </div>
                    <div class="baobaa-float-card absolute -right-2 top-[46%] rounded-2xl bg-white px-3.5 py-2.5 shadow-xl shadow-[#173e7a]/15 ring-1 ring-[#e4ebf8] sm:-right-8" style="--float-delay: -3s" data-reveal="right">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-8 place-items-center rounded-xl bg-[#1dc8ff] text-sm font-extrabold text-white">W</span>
                            <span><span class="block text-xs font-extrabold text-[#07152f]">Acompte payé</span><span class="block text-[10px] font-bold text-[#7d879d]">Via Wave · date bloquée</span></span>
                        </div>
                    </div>
                    <div class="baobaa-float-card absolute bottom-20 left-0 rounded-2xl bg-[#07152f] px-3.5 py-2.5 text-white shadow-xl shadow-[#07152f]/30 sm:-left-10" style="--float-delay: -5s" data-reveal="left">
                        <span class="block text-[10px] font-bold text-white/60">Votre date</span>
                        <span class="block text-xs font-extrabold">Samedi 12 · confirmée</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Bento --}}
        <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-24">
            <div class="max-w-2xl" data-reveal>
                <p class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]"><span class="size-1.5 rounded-full bg-[#2f6bff]"></span>Pourquoi BAOBAA</p>
                <h2 class="mt-3 font-display text-4xl font-extrabold leading-[1.02] tracking-[-0.04em] sm:text-5xl">Organiser, sans <span class="text-[#2f6bff]">mauvaise surprise.</span></h2>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-12">
                <article class="relative overflow-hidden rounded-[2rem] bg-[#07152f] p-7 text-white lg:col-span-7 lg:row-span-2" data-reveal>
                    <h3 class="font-display text-2xl font-extrabold">Les vraies dates libres.</h3>
                    <p class="mt-2 max-w-md text-sm font-semibold leading-6 text-white/65">Chaque espace affiche son calendrier : dates réservées, bloquées ou libres. Vous ne demandez que ce qui est réellement disponible.</p>
                    <div class="mx-auto mt-8 max-w-md" data-animate-on-view>
                        <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-extrabold text-white/40">
                            @foreach (['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $weekday)
                                <span>{{ $weekday }}</span>
                            @endforeach
                        </div>
                        <div class="mt-2 grid grid-cols-7 gap-2 text-center text-sm font-extrabold">
                            @foreach (range(1, 28) as $day)
                                @php
                                    $state = in_array($day, [5, 6, 13, 19, 20], true) ? 'booked' : ($day === 21 ? 'chosen' : 'free');
                                @endphp
                                <span @class([
                                    'baobaa-calendar-cell relative grid aspect-square place-items-center rounded-xl',
                                    'bg-white/5 text-white/25 line-through' => $state === 'booked',
                                    'bg-[#2f6bff] text-white' => $state === 'chosen',
                                    'bg-white/10 text-white' => $state === 'free',
                                ]) style="--cell-delay: {{ $day * 35 }}ms">
                                    @if ($state === 'chosen')
                                        <span class="baobaa-pulse-ring absolute inset-0 rounded-xl ring-2 ring-[#2f6bff]"></span>
                                    @endif
                                    {{ $day }}
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-5 flex flex-wrap gap-4 text-xs font-bold text-white/60">
                            <span class="inline-flex items-center gap-2"><span class="size-3 rounded bg-white/10"></span>Libre</span>
                            <span class="inline-flex items-center gap-2"><span class="size-3 rounded bg-white/5 ring-1 ring-white/15"></span>Réservée</span>
                            <span class="inline-flex items-center gap-2"><span class="size-3 rounded bg-[#2f6bff]"></span>Votre date</span>
                        </div>
                    </div>
                </article>

                <article class="rounded-[2rem] bg-white p-7 ring-1 ring-[#dce6f7] lg:col-span-5" data-reveal>
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <h3 class="font-display text-2xl font-extrabold">Acompte sécurisé.</h3>
                            <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">Un acompte bloque votre date. Le solde suit la proforma validée avec le propriétaire.</p>
                        </div>
                        <svg class="size-28 shrink-0 -rotate-90" viewBox="0 0 100 100" data-reveal style="--gauge-offset: 87" aria-hidden="true">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="#eef2fb" stroke-width="8"/>
                            <circle class="baobaa-gauge-value" cx="50" cy="50" r="46" fill="none" stroke="#2f6bff" stroke-width="8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </article>

                <article class="rounded-[2rem] bg-[#e9efff] p-7 lg:col-span-5" data-reveal>
                    <h3 class="font-display text-2xl font-extrabold">Payez comme vous voulez.</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">Mobile Money ou virement, selon ce que l’espace accepte.</p>
                    <div class="mt-5 flex flex-wrap gap-2.5" data-animate-on-view>
                        @foreach ($paymentLabels as $chipIndex => $paymentLabel)
                            <span class="baobaa-chip rounded-full px-4 py-2 text-sm font-extrabold text-white shadow-lg" style="--chip-rotate: {{ [-4, 3, -2, 5, -3][$chipIndex % 5] }}deg; --chip-delay: {{ $chipIndex * 110 }}ms; background: {{ ['#1dc8ff', '#ff7a00', '#e8b100', '#1f4fbf', '#0f9f6e'][$chipIndex % 5] }}">{{ $paymentLabel }}</span>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-[2rem] bg-white p-7 ring-1 ring-[#dce6f7] lg:col-span-4" data-reveal>
                    <h3 class="font-display text-2xl font-extrabold">Des lieux vérifiés.</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">Photos réelles, prix clairs et avis de clients ayant réservé.</p>
                    <div class="relative mt-6 h-36">
                        @foreach ($popularListings->take(3)->values() as $stackIndex => $stackVenue)
                            <img src="{{ $stackVenue['image'] }}" alt="" width="200" height="140" loading="lazy" decoding="async" referrerpolicy="no-referrer" class="absolute left-1/2 top-0 h-32 w-44 rounded-2xl object-cover shadow-xl ring-4 ring-white transition duration-700" style="transform: translateX(calc(-50% + {{ ($stackIndex - 1) * 64 }}px)) rotate({{ ($stackIndex - 1) * 8 }}deg); z-index: {{ $stackIndex === 1 ? 3 : 1 }}">
                        @endforeach
                    </div>
                </article>

                <article class="rounded-[2rem] bg-[#07152f] p-7 text-white lg:col-span-4" data-reveal>
                    <h3 class="font-display text-2xl font-extrabold">On se parle, simplement.</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-white/65">Échangez avec le propriétaire et négociez depuis votre réservation.</p>
                    <div class="mt-5 space-y-2" data-animate-on-view>
                        <p class="baobaa-bubble w-max max-w-[85%] rounded-2xl rounded-bl-md bg-white px-4 py-2.5 text-sm font-bold text-[#07152f]" style="--bubble-delay: 100ms">La salle est libre le 21 ?</p>
                        <p class="baobaa-bubble ml-auto w-max max-w-[85%] rounded-2xl rounded-br-md bg-[#2f6bff] px-4 py-2.5 text-sm font-bold" style="--bubble-delay: 700ms">Oui ! Je vous envoie la proforma.</p>
                    </div>
                </article>

                <article class="rounded-[2rem] bg-white p-7 ring-1 ring-[#dce6f7] lg:col-span-4" data-reveal>
                    <h3 class="font-display text-2xl font-extrabold">Tout au même endroit.</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">Le lieu, les professionnels et le matériel, dans un seul dossier.</p>
                    <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs font-extrabold" data-reveal-stagger="120" data-reveal-variant="scale">
                        <a href="{{ route('venues.index') }}" class="rounded-2xl bg-[#eef4ff] px-2 py-4 text-[#2f6bff] transition hover:-translate-y-1">Espaces</a>
                        <a href="{{ route('service-provider-profiles.index') }}" class="rounded-2xl bg-[#f3efff] px-2 py-4 text-[#7c5cff] transition hover:-translate-y-1">Pros</a>
                        <a href="{{ route('products.index') }}" class="rounded-2xl bg-[#e8f8f1] px-2 py-4 text-[#0f9f6e] transition hover:-translate-y-1">Matériel</a>
                    </div>
                </article>
            </div>
        </section>

        {{-- Story --}}
        <section class="bg-[#07152f] px-5 py-20 text-white sm:px-8 lg:py-28" data-story>
            <div class="mx-auto max-w-7xl">
                <div class="max-w-2xl" data-reveal>
                    <p class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#8db4ff]"><span class="size-1.5 rounded-full bg-[#8db4ff]"></span>Comment ça marche</p>
                    <h2 class="mt-3 font-display text-4xl font-extrabold leading-[1.02] tracking-[-0.04em] sm:text-5xl">Réserver, c’est quatre gestes.</h2>
                </div>

                <div class="mt-14 grid gap-10 lg:grid-cols-[1fr_auto_380px]">
                    <div>
                        @foreach ([
                            ['Cherchez.', 'Filtrez par ville, date, nombre d’invités et budget. Seuls les espaces réellement libres à vos dates s’affichent.'],
                            ['Comparez.', 'Photos, capacité, prix, options et avis : tout ce qu’il faut pour choisir sereinement, sans appel interminable.'],
                            ['Réservez.', 'Envoyez votre demande, validez la proforma et payez l’acompte par Mobile Money pour bloquer la date.'],
                            ['Célébrez.', 'Ajoutez des professionnels et le matériel nécessaire, puis profitez. Tout est suivi depuis votre espace client.'],
                        ] as $stepIndex => [$stepTitle, $stepText])
                            <div @class(['baobaa-story-step flex min-h-[42vh] flex-col justify-center py-6 lg:min-h-[70vh]', 'is-active' => $stepIndex === 0]) data-story-step>
                                <p class="text-sm font-extrabold text-[#8db4ff]">Étape 0{{ $stepIndex + 1 }}</p>
                                <h3 class="mt-2 font-display text-5xl font-extrabold tracking-[-0.045em] sm:text-6xl">{{ $stepTitle }}</h3>
                                <p class="mt-4 max-w-md text-base font-semibold leading-7 text-white/65">{{ $stepText }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="relative hidden lg:block" aria-hidden="true">
                        <div class="sticky top-[30vh] h-[40vh] w-0.5 rounded-full bg-white/10">
                            <div class="baobaa-timeline-fill absolute inset-0 rounded-full bg-[#2f6bff]" style="transform: scaleY(var(--story-progress, .25))"></div>
                            @foreach (range(0, 3) as $dotIndex)
                                <span data-story-dot class="absolute left-1/2 grid size-4 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-white/25 bg-[#07152f] transition [&.is-reached]:border-[#2f6bff] [&.is-reached]:bg-[#2f6bff]" style="top: {{ $dotIndex * 33.33 }}%"></span>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative hidden lg:block" aria-hidden="true">
                        <div class="sticky top-[12vh] grid place-items-center">
                            <div class="absolute size-[340px] rounded-full bg-[#2f6bff]/25 blur-3xl"></div>
                            <div class="baobaa-phone">
                                <div class="baobaa-phone-notch"></div>
                                <div class="baobaa-phone-screen">
                                    <div class="baobaa-story-screen is-active px-4 pt-12" data-story-screen>
                                        <p class="font-display text-lg font-extrabold text-[#07152f]">Espaces à Abidjan</p>
                                        <p class="text-[10px] font-bold text-[#7d879d]">Samedi · 150 invités</p>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($storyVenues as $storyVenue)
                                                <div class="flex gap-2 rounded-xl bg-white p-2 ring-1 ring-[#e4ebf8]">
                                                    <img src="{{ $storyVenue['image'] }}" alt="" width="64" height="64" loading="lazy" referrerpolicy="no-referrer" class="size-14 rounded-lg object-cover">
                                                    <div class="min-w-0">
                                                        <p class="line-clamp-1 text-[11px] font-extrabold text-[#07152f]">{{ $storyVenue['title'] }}</p>
                                                        <p class="text-[9px] font-bold text-[#7d879d]">{{ $storyVenue['city'] }}</p>
                                                        <p class="mt-1 text-[10px] font-extrabold text-[#2f6bff]">{{ $storyVenue['price'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="baobaa-story-screen" data-story-screen>
                                        @if ($heroVenue)
                                            <img src="{{ $heroVenue['image'] }}" alt="" width="270" height="200" loading="lazy" referrerpolicy="no-referrer" class="h-48 w-full object-cover">
                                            <div class="px-4 pt-3">
                                                <p class="font-display text-lg font-extrabold leading-tight text-[#07152f]">{{ $heroVenue['title'] }}</p>
                                                <p class="text-[10px] font-bold text-[#7d879d]">{{ $heroVenue['city'] }}</p>
                                                <div class="mt-3 flex flex-wrap gap-1.5 text-[10px] font-extrabold text-[#38445c]">
                                                    <span class="rounded-full bg-[#eef4ff] px-2.5 py-1">{{ $heroVenue['guests'] }}</span>
                                                    <span class="rounded-full bg-[#eef4ff] px-2.5 py-1">{{ $heroVenue['category'] }}</span>
                                                    @if ($heroVenue['verified'])
                                                        <span class="rounded-full bg-[#e8f8f1] px-2.5 py-1 text-[#0f9f6e]">Vérifié</span>
                                                    @endif
                                                </div>
                                                <p class="mt-4 text-[10px] font-bold text-[#7d879d]">À partir de</p>
                                                <p class="font-display text-xl font-extrabold text-[#2f6bff]">{{ $heroVenue['price'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="baobaa-story-screen px-4 pt-12" data-story-screen>
                                        <p class="font-display text-lg font-extrabold text-[#07152f]">Récapitulatif</p>
                                        <div class="mt-3 space-y-2 rounded-xl bg-white p-3 text-[11px] font-bold text-[#52617b] ring-1 ring-[#e4ebf8]">
                                            <p class="flex justify-between"><span>Date</span><span class="text-[#07152f]">Samedi 21</span></p>
                                            <p class="flex justify-between"><span>Invités</span><span class="text-[#07152f]">150</span></p>
                                            <p class="flex justify-between"><span>Proforma</span><span class="text-[#0f9f6e]">Validée</span></p>
                                        </div>
                                        <p class="mt-4 text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#7d879d]">Payer l’acompte</p>
                                        <div class="mt-2 grid grid-cols-2 gap-2 text-[11px] font-extrabold">
                                            <span class="rounded-xl bg-[#1dc8ff] px-3 py-3 text-white ring-2 ring-[#07152f]">Wave</span>
                                            <span class="rounded-xl bg-white px-3 py-3 text-[#ff7a00] ring-1 ring-[#e4ebf8]">Orange Money</span>
                                        </div>
                                        <span class="mt-4 block rounded-xl bg-[#2f6bff] py-3 text-center text-xs font-extrabold text-white">Payer et bloquer la date</span>
                                    </div>
                                    <div class="baobaa-story-screen grid place-items-center px-6 text-center" data-story-screen>
                                        <div>
                                            <span class="relative mx-auto grid size-20 place-items-center rounded-full bg-[#12b76a] text-white shadow-xl shadow-[#12b76a]/40">
                                                <span class="baobaa-pulse-ring absolute inset-0 rounded-full bg-[#12b76a]"></span>
                                                <svg class="relative size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                                            </span>
                                            <p class="mt-5 font-display text-2xl font-extrabold text-[#07152f]">C’est réservé&nbsp;!</p>
                                            <p class="mt-1 text-[11px] font-bold text-[#7d879d]">Votre date est bloquée. Bonne fête&nbsp;!</p>
                                            <div class="mt-5 space-y-1.5 text-left text-[10px] font-extrabold">
                                                <p class="rounded-lg bg-white px-3 py-2 text-[#38445c] ring-1 ring-[#e4ebf8]">+ Sonorisation ajoutée</p>
                                                <p class="rounded-lg bg-white px-3 py-2 text-[#38445c] ring-1 ring-[#e4ebf8]">+ 150 chaises louées</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Popular venues --}}
        <section id="espaces" class="mx-auto max-w-7xl px-5 py-20 sm:px-8" data-carousel-section>
            <div class="mb-8 flex flex-wrap items-end justify-between gap-5" data-reveal>
                <div>
                    <p class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]"><span class="size-1.5 rounded-full bg-[#2f6bff]"></span>Tendances</p>
                    <h2 class="mt-3 font-display text-4xl font-extrabold tracking-[-0.04em] sm:text-5xl">Les espaces les plus demandés.</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-carousel-prev class="grid size-11 place-items-center rounded-full bg-white text-[#07152f] ring-1 ring-[#dce6f7] transition hover:bg-[#07152f] hover:text-white" aria-label="Espaces précédents">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" data-carousel-next class="grid size-11 place-items-center rounded-full bg-white text-[#07152f] ring-1 ring-[#dce6f7] transition hover:bg-[#07152f] hover:text-white" aria-label="Espaces suivants">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                    <a href="{{ route('venues.index') }}" class="rounded-full bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white transition hover:bg-[#2f6bff]">Tous les espaces</a>
                </div>
            </div>

            <div data-carousel class="baobaa-scrollbar-none overflow-x-auto scroll-smooth px-1 pb-4">
                <div class="baobaa-venue-carousel-grid grid grid-rows-1 gap-5" data-reveal-stagger="70">
                    @forelse ($popularListings as $listing)
                        <x-venues.carousel-card :listing="$listing" :badge="$listing['category']" />
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-6 text-sm font-semibold text-[#6b7b99] ring-1 ring-[#dce6f7]">Aucun espace publié pour le moment.</div>
                    @endforelse
                </div>
            </div>

            @if ($categories->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2" data-reveal-stagger="50" data-reveal-variant="scale">
                    @foreach ($categories as $category)
                        <a href="{{ route('venues.index', ['category' => $category->slug]) }}" class="rounded-full bg-white px-4 py-2 text-sm font-extrabold text-[#38445c] ring-1 ring-[#dce6f7] transition hover:bg-[#2f6bff] hover:text-white hover:ring-[#2f6bff]">{{ $category->name }} <span class="text-[#9aa3b5]">{{ $category->published_venues_count }}</span></a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Audiences --}}
        <section class="mx-auto max-w-7xl px-5 pb-20 sm:px-8">
            <div class="max-w-2xl" data-reveal>
                <p class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]"><span class="size-1.5 rounded-full bg-[#2f6bff]"></span>Pour tout le monde</p>
                <h2 class="mt-3 font-display text-4xl font-extrabold leading-[1.02] tracking-[-0.04em] sm:text-5xl">Organisez, louez ou proposez vos services.</h2>
            </div>

            <div class="baobaa-audience-group mt-10 flex flex-col gap-3 lg:h-[420px] lg:flex-row" data-reveal>
                @foreach ([
                    ['J’organise', 'Mon événement', 'Trouvez le lieu, les professionnels et le matériel, comparez et réservez en ligne avec un acompte sécurisé.', ['Espaces vérifiés et dates libres', 'Paiement Mobile Money', 'Suivi depuis votre espace client'], route('venues.index'), 'Trouver un espace', 'bg-[#2f6bff]', 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
                    ['Je loue', 'Mon espace', 'Publiez votre salle gratuitement, recevez des demandes qualifiées et encaissez des acomptes sécurisés.', ['Inscription gratuite', 'Calendrier et proformas', 'Commission uniquement si vous louez'], route('owner.register'), 'Devenir partenaire', 'bg-[#07152f]', 'M4 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M8 7h2M12 7h2M8 11h2M12 11h2M2 21h20'],
                    ['Je propose', 'Mes services', 'Son, lumière, photo, décoration, matériel : présentez vos offres aux organisateurs et recevez des devis.', ['Services et matériel', 'Devis et messagerie', 'Livraison par ville'], route('service-provider.register'), 'Devenir professionnel', 'bg-[#0f9f6e]', 'M9 18V5l12-2v13M6 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm12-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                ] as [$audienceKicker, $audienceTitle, $audienceText, $audiencePoints, $audienceUrl, $audienceCta, $audienceColor, $audienceIcon])
                    <a href="{{ $audienceUrl }}" class="baobaa-audience {{ $audienceColor }} group relative flex flex-1 overflow-hidden rounded-[2rem] p-7 text-white">
                        <svg class="pointer-events-none absolute -bottom-6 -right-6 size-48 text-white/10 transition duration-700 group-hover:rotate-6 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $audienceIcon }}"/></svg>
                        <span class="baobaa-audience-label absolute bottom-7 left-7 hidden origin-bottom-left -rotate-90 translate-x-6 whitespace-nowrap font-display text-2xl font-extrabold lg:block">{{ $audienceKicker }}</span>
                        <div class="baobaa-audience-body relative flex flex-col">
                            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-white/70">{{ $audienceKicker }}</p>
                            <p class="mt-2 font-display text-3xl font-extrabold">{{ $audienceTitle }}</p>
                            <p class="mt-3 max-w-sm text-sm font-semibold leading-6 text-white/80">{{ $audienceText }}</p>
                            <ul class="mt-5 space-y-2 text-sm font-bold">
                                @foreach ($audiencePoints as $audiencePoint)
                                    <li class="flex items-center gap-2"><span class="grid size-5 place-items-center rounded-full bg-white/20"><svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg></span>{{ $audiencePoint }}</li>
                                @endforeach
                            </ul>
                            <span class="mt-6 inline-flex w-max items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-extrabold text-[#07152f] transition group-hover:gap-3">{{ $audienceCta }} <span aria-hidden="true">→</span></span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Figures --}}
        <section class="border-y border-[#dce6f7] bg-white">
            <div class="mx-auto grid max-w-7xl sm:grid-cols-2 lg:grid-cols-4" data-reveal-stagger="100">
                @foreach ([
                    [$publishedVenuesCount, 'espaces', 'Publiés et vérifiés'],
                    [$publishedCitiesCount, 'villes', 'Où organiser votre événement'],
                    [$professionalsCount, 'pros', 'Professionnels de l’événement'],
                    [count($paymentLabels), 'moyens', 'De paiement acceptés'],
                ] as [$figureValue, $figureUnit, $figureLabel])
                    <div class="border-[#dce6f7] px-5 py-10 sm:px-8 [&:not(:last-child)]:border-b sm:[&:nth-child(odd)]:border-r lg:border-b-0 lg:[&:not(:last-child)]:border-r">
                        <p class="font-display text-6xl font-extrabold tracking-[-0.05em] text-[#07152f]"><span data-count-to="{{ $figureValue }}">{{ $figureValue }}</span><span class="ml-1 text-2xl text-[#2f6bff]">{{ $figureUnit }}</span></p>
                        <p class="mt-2 text-sm font-semibold text-[#52617b]">{{ $figureLabel }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- FAQ --}}
        <section class="mx-auto grid max-w-7xl gap-10 px-5 py-20 sm:px-8 lg:grid-cols-[.8fr_1.2fr]">
            <div data-reveal="left">
                <p class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]"><span class="size-1.5 rounded-full bg-[#2f6bff]"></span>FAQ</p>
                <h2 class="mt-3 font-display text-5xl font-extrabold leading-[0.98] tracking-[-0.045em]">Questions fréquentes.</h2>
                <div class="mt-8 rounded-[2rem] bg-[#07152f] p-7 text-white">
                    <p class="font-display text-xl font-extrabold">Une autre question&nbsp;?</p>
                    <p class="mt-1 text-sm font-semibold text-white/65">Notre équipe vous répond.</p>
                    <div class="mt-5 grid gap-2">
                        @if (filled($contact['whatsapp']))
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact['whatsapp']) }}" target="_blank" rel="noopener" class="rounded-full bg-[#25d366] px-5 py-3 text-sm font-extrabold text-white">WhatsApp · {{ $contact['whatsapp'] }}</a>
                        @endif
                        @if (filled($contact['email']))
                            <a href="mailto:{{ $contact['email'] }}" class="rounded-full bg-white/10 px-5 py-3 text-sm font-extrabold text-white">{{ $contact['email'] }}</a>
                        @endif
                        <a href="{{ route('pages.contact') }}" class="rounded-full bg-white px-5 py-3 text-center text-sm font-extrabold text-[#07152f]">Nous contacter</a>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-[#dce6f7] border-y border-[#dce6f7]" data-reveal-stagger="70">
                @foreach ($faqs as $faqIndex => [$question, $answer])
                    <details class="baobaa-faq group" @if ($faqIndex === 0) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6">
                            <span class="font-display text-xl font-extrabold tracking-[-0.02em]">{{ $question }}</span>
                            <span class="baobaa-faq-icon grid size-10 shrink-0 place-items-center rounded-full bg-[#eef2fb] text-xl font-bold text-[#07152f] group-open:rotate-45 group-open:bg-[#2f6bff] group-open:text-white" aria-hidden="true">+</span>
                        </summary>
                        <p class="baobaa-faq-answer max-w-2xl pb-6 text-base font-semibold leading-7 text-[#52617b]">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        {{-- Final call to action --}}
        <section class="px-5 pb-20 sm:px-8">
            <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[2.5rem] bg-[linear-gradient(135deg,#2f6bff,#5b4dff)] px-7 py-14 text-white sm:px-14 sm:py-20" data-reveal="scale">
                <div class="baobaa-aurora pointer-events-none absolute -right-20 -top-20 size-80 rounded-full bg-white/15 blur-2xl" data-aurora="30" aria-hidden="true"></div>
                <div class="relative max-w-2xl">
                    <h2 class="font-display text-4xl font-extrabold leading-[1] tracking-[-0.045em] sm:text-6xl">Votre prochain événement commence ici.</h2>
                    <p class="mt-4 text-base font-semibold text-white/80">Trouvez le lieu idéal en quelques minutes, ou publiez votre espace gratuitement.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('venues.index') }}" class="rounded-full bg-white px-6 py-3.5 text-sm font-extrabold text-[#07152f] transition hover:-translate-y-0.5">Trouver un espace</a>
                        <a href="{{ route('owner.register') }}" class="rounded-full bg-[#07152f] px-6 py-3.5 text-sm font-extrabold text-white transition hover:-translate-y-0.5">Publier mon espace</a>
                    </div>
                </div>
            </div>
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
