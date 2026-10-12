@php
    use App\Enums\VerificationStatus;

    $partnerCards = $profiles->map(fn ($profile) => [
        'name' => $profile->business_name,
        'public_uuid' => $profile->public_uuid,
        'logo_url' => $profile->logo_url,
        'logo_alt_text' => $profile->logo_alt_text,
        'city' => $profile->city,
        'country' => $profile->country_code,
        'venues_count' => $profile->published_venues_count,
        'verified' => $profile->verification_status === VerificationStatus::Verified,
    ])->values();
    $hasFilters = filled($filters['q']) || filled($filters['city']);
@endphp

<x-layouts.baobaa title="Partenaires événementiels - BAOBAA" description="Découvrez les propriétaires de salles et d’espaces événementiels partenaires de BAOBAA et leurs lieux disponibles à la réservation.">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
                </a>

                <x-navigation.public-menu active="partners" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('venues.list-venue') }}" class="hidden rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] lg:inline-flex">Devenir partenaire</a>
                    @endguest
                    <x-navigation.account-menu />
                    <x-navigation.mobile-menu active="partners" />
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
                                <p class="text-2xl font-extrabold text-[#151821]">{{ number_format($partnersCount, 0, ',', ' ') }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">partenaire{{ $partnersCount > 1 ? 's' : '' }} actif{{ $partnersCount > 1 ? 's' : '' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/82 p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ number_format($publishedVenuesCount, 0, ',', ' ') }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">espace{{ $publishedVenuesCount > 1 ? 's' : '' }} disponible{{ $publishedVenuesCount > 1 ? 's' : '' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/82 p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ $cities->count() }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">ville{{ $cities->count() > 1 ? 's' : '' }} couverte{{ $cities->count() > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('owner-profiles.index') }}" class="rounded-[24px] border border-white bg-white/88 p-4 shadow-2xl shadow-[#173e7a]/10 backdrop-blur">
                        <label class="flex items-center gap-3 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3">
                            <svg class="size-5 shrink-0 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Rechercher un partenaire, un espace ou une ville" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:text-[#9aa5ba]">
                        </label>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($cities->take(6) as $city)
                                <a href="{{ route('owner-profiles.index', ['city' => $city]) }}" @class([
                                    'rounded-full px-3 py-1.5 text-xs font-extrabold transition',
                                    'bg-[#2f6bff] text-white' => $filters['city'] === $city,
                                    'bg-[#eef4ff] text-[#4d5872] hover:bg-[#2f6bff] hover:text-white' => $filters['city'] !== $city,
                                ])>{{ $city }}</a>
                            @endforeach
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-2">
                            @if ($hasFilters)
                                <a href="{{ route('owner-profiles.index') }}" class="rounded-full px-3 py-2 text-xs font-extrabold text-[#52617b] hover:text-[#2f6bff]">Effacer</a>
                            @else
                                <span></span>
                            @endif
                            <button class="rounded-full bg-[#2f6bff] px-5 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Rechercher</button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($partnerCards as $profile)
                        <a href="{{ route('owner-profiles.show', $profile['public_uuid']) }}" class="group overflow-hidden rounded-[22px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12">
                            <div class="partner-cover h-24 bg-[linear-gradient(135deg,#2f6bff,#7ea2ff_55%,#eef4ff)]" data-partner-cover="{{ $profile['logo_url'] ? $profile['logo_url'] : '' }}" data-default-gradient="linear-gradient(135deg, #2f6bff, #7ea2ff 55%, #eef4ff)" style="background-image: linear-gradient(135deg, #2f6bff, #7ea2ff 55%, #eef4ff);"></div>
                            <div class="p-5">
                                <div class="-mt-12 flex items-end justify-between gap-4">
                                    <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-[22px] border-4 border-white bg-[#eaf1ff] text-3xl font-extrabold text-[#2f6bff] shadow-lg shadow-[#173e7a]/8">
                                        @if ($profile['logo_url'])
                                            <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['logo_alt_text'] ?? $profile['name'] }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(substr($profile['name'], 0, 1)) }}
                                        @endif
                                    </span>
                                    @if ($profile['verified'])
                                        <span class="mb-2 rounded-full bg-[#ecfdf5] px-3 py-1 text-xs font-extrabold text-[#047857]">Vérifié</span>
                                    @endif
                                </div>
                                <h2 class="mt-4 truncate text-xl font-extrabold tracking-[-0.035em] text-[#151821]">{{ $profile['name'] }}</h2>
                                <p class="mt-1 text-sm font-bold text-[#6f7890]">{{ collect([$profile['city'], $profile['country']])->filter()->implode(', ') }}</p>
                                <div class="mt-5 flex items-center justify-between border-t border-[#edf2fb] pt-4">
                                    <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $profile['venues_count'] }} espace{{ $profile['venues_count'] > 1 ? 's' : '' }}</span>
                                    <span class="inline-flex items-center gap-1 text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">Ouvrir la vitrine <span>→</span></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($partnerCards->isEmpty())
                    <div class="mt-8 rounded-[24px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center shadow-sm">
                        <p class="text-xl font-extrabold text-[#151821]">Aucun partenaire ne correspond à votre recherche.</p>
                        <a href="{{ route('owner-profiles.index') }}" class="mt-5 inline-flex rounded-full bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Voir tous les partenaires</a>
                    </div>
                @endif

                <script>
                    (() => {
                        const createGradientFromImage = (img) => {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            const sampleSize = 40;

                            if (!ctx || !img || !img.width || !img.height) {
                                return null;
                            }

                            canvas.width = sampleSize;
                            canvas.height = sampleSize;
                            ctx.drawImage(img, 0, 0, sampleSize, sampleSize);

                            const { data } = ctx.getImageData(0, 0, sampleSize, sampleSize);
                            let r = 0, g = 0, b = 0, count = 0;

                            for (let i = 0; i < data.length; i += 4) {
                                const alpha = data[i + 3];
                                if (alpha < 128) continue;

                                r += data[i];
                                g += data[i + 1];
                                b += data[i + 2];
                                count += 1;
                            }

                            if (!count) return null;

                            const avgR = Math.round(r / count);
                            const avgG = Math.round(g / count);
                            const avgB = Math.round(b / count);

                            const shift = 20;
                            const first = `rgb(${avgR}, ${avgG}, ${avgB})`;
                            const second = `rgb(${Math.min(255, avgR + shift)}, ${Math.min(255, avgG + shift)}, ${Math.min(255, avgB + shift)})`;
                            const third = `rgb(${Math.max(0, avgR - 30)}, ${Math.max(0, avgG - 30)}, ${Math.max(0, avgB - 30)})`;

                            return `linear-gradient(135deg, ${first} 0%, ${second} 45%, ${third} 100%)`;
                        };

                        document.querySelectorAll('[data-partner-cover]').forEach((cover) => {
                            const source = cover.dataset.partnerCover;
                            if (!source) return;

                            const img = new Image();
                            img.crossOrigin = 'anonymous';
                            img.onload = () => {
                                const gradient = createGradientFromImage(img);
                                if (gradient) {
                                    cover.style.backgroundImage = gradient;
                                }
                            };
                            img.onerror = () => {
                                cover.style.backgroundImage = cover.dataset.defaultGradient || 'linear-gradient(135deg, #2f6bff, #7ea2ff 55%, #eef4ff)';
                            };
                            img.src = source;
                        });
                    })();
                </script>

                @if ($profiles->hasPages())
                    <div class="mt-8">
                        {{ $profiles->links() }}
                    </div>
                @endif
            </div>
        </section>

        <x-navigation.public-footer />
    </main>
</x-layouts.baobaa>
