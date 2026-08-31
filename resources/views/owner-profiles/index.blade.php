@php
    $partnerCards = $profiles->isNotEmpty()
        ? $profiles->map(fn ($profile) => [
            'name' => $profile->business_name,
            'public_uuid' => $profile->public_uuid,
            'logo_url' => $profile->logo_url,
            'logo_alt_text' => $profile->logo_alt_text,
            'city' => $profile->city,
            'country' => $profile->country_code,
            'venues_count' => $profile->published_venues_count,
        ])->values()
        : collect($fallbackProfiles)->map(fn ($profile) => [
            'name' => $profile['name'],
            'public_uuid' => $profile['public_uuid'],
            'logo_url' => null,
            'logo_alt_text' => $profile['name'],
            'city' => $profile['city'],
            'country' => $profile['country'],
            'venues_count' => $profile['venues_count'],
        ]);
@endphp

<x-layouts.baobaa title="Partenaires événementiels - BAOBAA">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <span class="grid size-11 place-items-center rounded-2xl bg-[#2f6bff] text-lg font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">B</span>
                    <span class="leading-none">
                        <span class="block text-xl font-extrabold tracking-[-0.045em]">baobaa</span>
                        <span class="block text-[11px] font-extrabold tracking-[-0.035em] text-[#6f7890]">marché événementiel</span>
                    </span>
                </a>

                <x-navigation.public-menu active="partners" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('owner.register') }}" class="hidden rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] lg:inline-flex">Devenir partenaire</a>
                    @endguest
                    <x-navigation.account-menu />
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden px-5 py-10 sm:px-8">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f7faff_0%,#eef3ff_58%,#f7f4ff_100%)]"></div>
            <div class="relative mx-auto max-w-7xl">
                <div class="grid gap-8 lg:grid-cols-[1fr_390px] lg:items-end">
                    <div>
                        <p class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff] shadow-sm ring-1 ring-[#dce6f7]">Partenaires vérifiés</p>
                        <h1 class="mt-5 max-w-4xl text-4xl font-extrabold leading-[1.02] tracking-[-0.055em] text-[#151821] sm:text-6xl">Les vitrines premium des meilleurs espaces BAOBAA</h1>
                        <p class="mt-4 max-w-2xl text-base font-semibold leading-7 text-[#667088]">Découvrez les partenaires qui publient des salles, jardins, rooftops, auditoriums et espaces professionnels sélectionnés pour vos événements.</p>

                        <div class="mt-6 grid max-w-2xl gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/82 p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ $partnerCards->count() }}+</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">partenaires actifs</p>
                            </div>
                            <div class="rounded-2xl bg-white/82 p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ $partnerCards->sum('venues_count') }}+</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">espaces disponibles</p>
                            </div>
                            <div class="rounded-2xl bg-white/82 p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">5.0</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">expérience visée</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-white bg-white/88 p-4 shadow-2xl shadow-[#173e7a]/10 backdrop-blur">
                        <label class="flex items-center gap-3 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3">
                            <svg class="size-5 shrink-0 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                            <input type="search" placeholder="Rechercher par nom, ville, pays" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:text-[#9aa5ba]">
                        </label>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['Abidjan', 'Dakar', 'Cotonou', 'Auditoriums', 'Jardins'] as $filter)
                                <span class="rounded-full bg-[#eef4ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $filter }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($partnerCards as $profile)
                        <a href="{{ route('owner-profiles.show', $profile['public_uuid']) }}" class="group overflow-hidden rounded-[22px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12">
                            <div class="h-24 bg-[linear-gradient(135deg,#2f6bff,#7ea2ff_55%,#eef4ff)]"></div>
                            <div class="p-5">
                                <div class="-mt-12 flex items-end justify-between gap-4">
                                    <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-[22px] border-4 border-white bg-[#eaf1ff] text-3xl font-extrabold text-[#2f6bff] shadow-lg shadow-[#173e7a]/8">
                                        @if ($profile['logo_url'])
                                            <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['logo_alt_text'] ?? $profile['name'] }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(substr($profile['name'], 0, 1)) }}
                                        @endif
                                    </span>
                                    <span class="mb-2 rounded-full bg-[#ecfdf5] px-3 py-1 text-xs font-extrabold text-[#047857]">Vérifié</span>
                                </div>
                                <h2 class="mt-4 truncate text-xl font-extrabold tracking-[-0.035em] text-[#151821]">{{ $profile['name'] }}</h2>
                                <p class="mt-1 text-sm font-bold text-[#6f7890]">{{ $profile['city'] }}, {{ $profile['country'] }}</p>
                                <div class="mt-5 flex items-center justify-between border-t border-[#edf2fb] pt-4">
                                    <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $profile['venues_count'] }} espaces</span>
                                    <span class="inline-flex items-center gap-1 text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">Ouvrir la vitrine <span>→</span></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if (method_exists($profiles, 'links') && $profiles->isNotEmpty())
                    <div class="mt-8">
                        {{ $profiles->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
</x-layouts.baobaa>
