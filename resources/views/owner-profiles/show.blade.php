@php
    $publicName = $ownerProfile->business_name;
    $isVerified = $ownerProfile->verification_status->value === 'verified';
@endphp

<x-layouts.baobaa :title="$publicName.' - BAOBAA'">
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

                <x-navigation.account-menu />
            </div>
        </header>

        <section class="relative overflow-hidden px-5 py-8 sm:px-8">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f8fbff_0%,#eef3ff_62%,#f7f4ff_100%)]"></div>
            <div class="relative mx-auto max-w-7xl">
                <a href="{{ route('owner-profiles.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-[#2f6bff] transition hover:translate-x-[-2px]">
                    <span>←</span>
                    <span>Tous les partenaires</span>
                </a>

                <div class="mt-5 overflow-hidden rounded-[28px] border border-white bg-white shadow-2xl shadow-[#173e7a]/10">
                    <div class="h-40 bg-[linear-gradient(135deg,#1d4ed8,#2f6bff_45%,#9bb8ff)]"></div>
                    <div class="p-6 sm:p-8">
                        <div class="-mt-20 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                                <span class="grid size-28 shrink-0 place-items-center overflow-hidden rounded-[30px] border-4 border-white bg-[#eaf1ff] text-5xl font-extrabold text-[#2f6bff] shadow-xl shadow-[#173e7a]/12">
                                    @if ($ownerProfile->logo_url)
                                        <img src="{{ $ownerProfile->logo_url }}" alt="{{ $ownerProfile->logo_alt_text ?? $publicName }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                                    @else
                                        {{ strtoupper(substr($publicName, 0, 1)) }}
                                    @endif
                                </span>
                                <div class="pb-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h1 class="text-3xl font-extrabold tracking-[-0.055em] text-[#151821] sm:text-5xl">{{ $publicName }}</h1>
                                        <span class="rounded-full {{ $isVerified ? 'bg-[#ecfdf5] text-[#047857]' : 'bg-[#fff7ed] text-[#c2410c]' }} px-3 py-1 text-xs font-extrabold">{{ $isVerified ? 'Partenaire vérifié' : 'Vérification en cours' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-bold text-[#6f7890]">{{ $ownerProfile->city }}, {{ $ownerProfile->country_code }} · {{ $ownerProfile->published_venues_count }} espaces publics</p>
                                </div>
                            </div>
                            <a href="#espaces" class="inline-flex items-center justify-center rounded-full bg-[#2f6bff] px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Explorer les espaces</a>
                        </div>

                        <div class="mt-8 grid gap-3 md:grid-cols-3">
                            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ $ownerProfile->published_venues_count }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">espaces disponibles</p>
                            </div>
                            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">2h</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">délai de réponse cible</p>
                            </div>
                            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">Secure</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">réservation encadrée BAOBAA</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="espaces" class="mt-8 scroll-mt-24">
                    <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Catalogue partenaire</p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.045em] text-[#151821]">Espaces disponibles</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['Vérifiés', 'Prix clairs', 'Réservation sécurisée'] as $badge)
                                <span class="rounded-full bg-white px-3 py-1.5 text-xs font-extrabold text-[#4d5872] shadow-sm ring-1 ring-[#dce6f7]">{{ $badge }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($venues as $venue)
                            @php
                                $image = $venue->media->sortBy('sort_order')->first()?->signed_url
                                    ?? 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=85';
                            @endphp
                            <a href="{{ route('venues.show', $venue->slug) }}" class="group overflow-hidden rounded-[22px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12">
                                <div class="relative">
                                    <img src="{{ $image }}" alt="{{ $venue->name }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-52 w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    <span class="absolute left-3 top-3 rounded-full bg-white/94 px-3 py-1 text-xs font-extrabold text-[#2f6bff] shadow-sm">{{ $venue->category?->name ?? 'Espace événementiel' }}</span>
                                </div>
                                <div class="p-4">
                                    <h3 class="line-clamp-2 text-lg font-extrabold tracking-[-0.025em] text-[#151821]">{{ $venue->name }}</h3>
                                <p class="mt-2 text-xs font-bold text-[#6f7890]">{{ $venue->district }}, {{ $venue->city }} · jusqu'à {{ $venue->max_capacity }} invités</p>
                                    <div class="mt-4 flex items-center justify-between border-t border-[#edf2fb] pt-4">
                                        <p class="text-sm font-extrabold text-[#151821]">{{ number_format($venue->starting_price, 0, ',', ' ') }} {{ $venue->currency }}</p>
                                        <span class="text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">Voir →</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-[18px] border border-dashed border-[#cbd8f4] bg-white p-8 text-sm font-bold text-[#6f7890]">
                                Aucun espace public disponible pour ce partenaire.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        @if (method_exists($venues, 'links'))
                            {{ $venues->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-layouts.baobaa>
