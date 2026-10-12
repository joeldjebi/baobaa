@php
    use App\Enums\VerificationStatus;

    $hasFilters = filled($filters['q']) || filled($filters['city']);
@endphp

<x-layouts.baobaa title="Partenaires événementiels - BAOBAA" description="Découvrez les propriétaires de salles et d’espaces événementiels partenaires de BAOBAA et leurs lieux disponibles à la réservation.">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <x-navigation.public-header active="partners" />

        <section class="border-b border-white/80 bg-[linear-gradient(180deg,#f8fbff_0%,#eef4ff_100%)] px-5 py-7 sm:px-8 lg:py-9">
            <div class="mx-auto max-w-7xl">
                <nav class="text-xs font-semibold text-[#6f7890]" aria-label="Fil d’Ariane"><a href="{{ url('/') }}" class="hover:text-[#2f6bff]">Accueil</a> / Partenaires</nav>
                <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-[-0.045em] text-[#121722] sm:text-4xl">Nos partenaires propriétaires</h1>
                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-[#667088]">Des propriétaires vérifiés qui proposent leurs salles, jardins, rooftops et espaces professionnels à la réservation.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-extrabold text-[#4d5872]">
                        <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">{{ number_format($partnersCount, 0, ',', ' ') }} partenaire{{ $partnersCount > 1 ? 's' : '' }}</span>
                        <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">{{ number_format($publishedVenuesCount, 0, ',', ' ') }} espace{{ $publishedVenuesCount > 1 ? 's' : '' }}</span>
                        <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">{{ $cities->count() }} ville{{ $cities->count() > 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('owner-profiles.index') }}" class="mt-6 grid gap-2 rounded-[22px] bg-white p-2 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7] md:grid-cols-[minmax(0,1fr)_220px_auto]">
                    <label class="flex items-center gap-3 rounded-2xl px-4 py-2.5">
                        <svg class="size-5 shrink-0 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                        <span class="sr-only">Rechercher</span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Nom du partenaire ou d’un espace" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:text-[#9aa5ba]">
                    </label>
                    <label class="rounded-2xl bg-[#f7faff] px-4 py-2.5 ring-1 ring-[#e4ebf8]">
                        <span class="sr-only">Ville</span>
                        <select name="city" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none">
                            <option value="">Toutes les villes</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected($filters['city'] === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex gap-2">
                        @if ($hasFilters)
                            <a href="{{ route('owner-profiles.index') }}" class="grid place-items-center rounded-2xl px-4 text-sm font-extrabold text-[#52617b] hover:text-[#2f6bff]">Effacer</a>
                        @endif
                        <button class="flex-1 rounded-2xl bg-[#2f6bff] px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:bg-[#2258df]">Rechercher</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($profiles as $profile)
                    <x-directory.profile-card
                        :name="$profile->business_name"
                        :url="route('owner-profiles.show', $profile->public_uuid)"
                        :logo-url="$profile->logo_url"
                        :cover-url="$profile->venues->first()?->cover_image_url"
                        :location="collect([$profile->city, $profile->country_code])->filter()->implode(', ')"
                        :verified="$profile->verification_status === VerificationStatus::Verified"
                        :meta="$profile->published_venues_count.' espace'.($profile->published_venues_count > 1 ? 's' : '').' publié'.($profile->published_venues_count > 1 ? 's' : '')"
                        action-label="Voir ses espaces"
                    />
                @endforeach
            </div>

            @if ($profiles->isEmpty())
                <div class="rounded-[24px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center shadow-sm">
                    <p class="text-xl font-extrabold text-[#151821]">Aucun partenaire ne correspond à votre recherche.</p>
                    <a href="{{ route('owner-profiles.index') }}" class="mt-5 inline-flex rounded-full bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Voir tous les partenaires</a>
                </div>
            @endif

            @if ($profiles->hasPages())
                <div class="mt-8">{{ $profiles->links() }}</div>
            @endif

            <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-[24px] bg-[#07152f] p-6 text-white">
                <div>
                    <p class="text-lg font-extrabold">Vous possédez une salle ou un espace événementiel ?</p>
                    <p class="mt-1 text-sm font-semibold text-white/70">Inscription gratuite, vérification par notre équipe, puis vos premières demandes de réservation.</p>
                </div>
                <a href="{{ route('venues.list-venue') }}" class="rounded-full bg-white px-5 py-3 text-sm font-extrabold text-[#07152f] transition hover:bg-[#eef4ff]">Devenir partenaire</a>
            </div>
        </section>

        <x-navigation.public-footer />
    </main>
</x-layouts.baobaa>
