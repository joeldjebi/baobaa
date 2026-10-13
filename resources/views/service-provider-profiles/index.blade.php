@php
    $hasFilters = filled($filters['q'] ?? null) || filled($filters['city'] ?? null) || filled($filters['type_id'] ?? null);
@endphp

<x-layouts.baobaa title="Professionnels de l’événement - BAOBAA" description="Sonorisation, lumière, photo, vidéo, décoration, traiteur : trouvez des professionnels de l’événement vérifiés, comparez leurs prestations et composez votre événement.">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <x-navigation.public-header active="event-services" cta-label="Devenir professionnel" :cta-url="route('service-provider.register')" />

        <section class="baobaa-page-hero border-b border-white/80 px-5 py-7 sm:px-8 lg:py-9">
            <div class="baobaa-hero-intro mx-auto max-w-7xl">
                <nav class="text-xs font-semibold text-[#6f7890]" aria-label="Fil d’Ariane"><a href="{{ url('/') }}" class="hover:text-[#2f6bff]">Accueil</a> / Professionnels</nav>
                <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-[-0.015em] text-[#121722] sm:text-4xl">Professionnels de l’événement</h1>
                        <p class="mt-2 max-w-2xl text-[15px] font-normal leading-7 text-[#5b6680]">Son, lumière, image, décor, scène… Comparez des équipes vérifiées, demandez un devis et suivez tout depuis votre dossier.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-extrabold text-[#4d5872]">
                        <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">{{ number_format($providersCount, 0, ',', ' ') }} professionnel{{ $providersCount > 1 ? 's' : '' }} vérifié{{ $providersCount > 1 ? 's' : '' }}</span>
                        <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">{{ number_format($publishedServicesCount, 0, ',', ' ') }} service{{ $publishedServicesCount > 1 ? 's' : '' }} disponible{{ $publishedServicesCount > 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('service-provider-profiles.index') }}" class="mt-6 grid gap-2 rounded-[22px] bg-white p-2 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] md:grid-cols-[minmax(0,1fr)_200px_220px_auto]">
                    <label class="flex items-center gap-3 rounded-2xl px-4 py-2.5">
                        <svg class="size-5 shrink-0 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                        <span class="sr-only">Rechercher</span>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Équipe ou prestation" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:text-[#9aa5ba]">
                    </label>
                    <label class="rounded-2xl bg-[#f7faff] px-4 py-2.5 ring-1 ring-[#e4ebf8]">
                        <span class="sr-only">Ville</span>
                        <select name="city" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none">
                            <option value="">Toutes les villes</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="rounded-2xl bg-[#f7faff] px-4 py-2.5 ring-1 ring-[#e4ebf8]">
                        <span class="sr-only">Type de service</span>
                        <select name="type_id" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none">
                            <option value="">Tous les services</option>
                            @foreach ($serviceTypes as $serviceType)
                                <option value="{{ $serviceType->id }}" @selected((int) ($filters['type_id'] ?? 0) === (int) $serviceType->id)>{{ $serviceType->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex gap-2">
                        @if ($hasFilters)
                            <a href="{{ route('service-provider-profiles.index') }}" class="grid place-items-center rounded-2xl px-4 text-sm font-extrabold text-[#52617b] hover:text-[#2f6bff]">Effacer</a>
                        @endif
                        <button class="flex-1 rounded-2xl bg-[#2f6bff] px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:bg-[#2258df]">Rechercher</button>
                    </div>
                </form>

                @if ($serviceTypes->isNotEmpty())
                    <div class="mt-4 flex gap-2 overflow-x-auto pb-1 baobaa-scrollbar-none">
                        @foreach ($serviceTypes as $serviceType)
                            <a href="{{ route('service-provider-profiles.index', ['type_id' => $serviceType->id]) }}" @class([
                                'shrink-0 rounded-full px-4 py-2 text-xs font-extrabold transition',
                                'bg-[#2f6bff] text-white' => (int) ($filters['type_id'] ?? 0) === (int) $serviceType->id,
                                'bg-white text-[#4d5872] ring-1 ring-[#dce6f7] hover:text-[#2f6bff]' => (int) ($filters['type_id'] ?? 0) !== (int) $serviceType->id,
                            ])>{{ $serviceType->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <p class="mb-4 text-sm font-extrabold text-[#4d5872]">{{ number_format($profiles->total(), 0, ',', ' ') }} professionnel{{ $profiles->total() > 1 ? 's' : '' }} {{ $hasFilters ? 'correspondant'.($profiles->total() > 1 ? 's' : '').' à votre recherche' : 'disponible'.($profiles->total() > 1 ? 's' : '') }}</p>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal-stagger="70">
                @foreach ($profiles as $profile)
                    @php
                        $startingPrice = $profile->services->min('starting_price');
                        $coverUrl = $profile->services->first(fn ($service) => $service->media->isNotEmpty())?->cover_image_url;
                    @endphp
                    <x-directory.profile-card
                        :name="$profile->business_name"
                        :url="route('service-provider-profiles.show', $profile->public_uuid)"
                        :logo-url="$profile->logo_url"
                        :cover-url="$coverUrl"
                        :location="collect([$profile->district, $profile->city])->filter()->implode(', ')"
                        :verified="true"
                        :description="$profile->description ?: 'Une équipe événementielle disponible pour accompagner votre projet.'"
                        :tags="$profile->services->pluck('type.name')->filter()->unique()->take(3)->values()->all()"
                        :meta="($startingPrice ? 'Dès '.number_format((int) $startingPrice, 0, ',', ' ').' '.($profile->services->first()?->currency ?? 'XOF') : '').($profile->published_products_count > 0 ? ' · '.$profile->published_products_count.' article'.($profile->published_products_count > 1 ? 's' : '') : '')"
                        action-label="Voir"
                    />
                @endforeach
            </div>

            @if ($profiles->isEmpty())
                <div class="rounded-[24px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center shadow-sm">
                    <p class="text-xl font-extrabold text-[#151821]">Aucun prestataire ne correspond à ces critères.</p>
                    <p class="mt-2 text-sm font-semibold text-[#6f7890]">Essayez une autre ville, un autre service ou une recherche plus large.</p>
                    <a href="{{ route('service-provider-profiles.index') }}" class="mt-5 inline-flex rounded-full bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Réinitialiser</a>
                </div>
            @endif

            @if ($profiles->hasPages())
                <div class="mt-8">{{ $profiles->links() }}</div>
            @endif

            <div class="mt-10 grid gap-4 md:grid-cols-2">
                <a href="{{ route('event-services.index') }}" class="rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-[#dce6f7] transition hover:-translate-y-0.5 hover:ring-[#2f6bff]">
                    <p class="text-lg font-extrabold text-[#151821]">Composer mon événement</p>
                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Sélectionnez plusieurs professionnels et envoyez une seule demande de devis.</p>
                </a>
                <a href="{{ route('service-provider.register') }}" class="rounded-[24px] bg-[#07152f] p-6 text-white transition hover:-translate-y-0.5">
                    <p class="text-lg font-extrabold">Vous êtes un professionnel de l’événement ?</p>
                    <p class="mt-1 text-sm font-semibold text-white/70">Publiez vos services et votre matériel, recevez des demandes qualifiées.</p>
                </a>
            </div>
        </section>

        <x-navigation.public-footer />
    </main>
</x-layouts.baobaa>
