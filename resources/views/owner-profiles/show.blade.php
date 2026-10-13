@php
    $publicName = $ownerProfile->business_name;
    $isVerified = $ownerProfile->verification_status->value === 'verified';
@endphp

<x-layouts.baobaa :title="$publicName.' - BAOBAA'" :description="'Découvrez les espaces événementiels proposés par '.$publicName.' sur BAOBAA'.($ownerProfile->city ? ' à '.$ownerProfile->city : '').' et réservez en ligne.'" :image="$ownerProfile->logo_url">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <x-navigation.public-header active="partners" />

        <section class="relative overflow-hidden px-5 py-8 sm:px-8">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f8fbff_0%,#eef3ff_62%,#f7f4ff_100%)]"></div>
            <div class="relative mx-auto max-w-7xl">
                <a href="{{ route('owner-profiles.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-[#2f6bff] transition hover:translate-x-[-2px]">
                    <span>←</span>
                    <span>Tous les partenaires</span>
                </a>

                <div class="mt-5 overflow-hidden rounded-[28px] border border-white bg-white shadow-2xl shadow-[#173e7a]/10">
                    <div class="partner-cover h-40 bg-[linear-gradient(135deg,#1d4ed8,#2f6bff_45%,#9bb8ff)]" data-partner-cover="{{ $ownerProfile->logo_url ?? '' }}" data-default-gradient="linear-gradient(135deg, #1d4ed8, #2f6bff 45%, #9bb8ff)" style="background-image: linear-gradient(135deg, #1d4ed8, #2f6bff 45%, #9bb8ff);"></div>
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
                                        <h1 class="text-3xl font-extrabold tracking-[-0.01em] text-[#151821] sm:text-5xl">{{ $publicName }}</h1>
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
                            <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.01em] text-[#151821]">Espaces disponibles</h2>
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
                                $image = $venue->cover_image_url;
                            @endphp
                            <a href="{{ route('venues.show', $venue->slug) }}" class="group overflow-hidden rounded-[22px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12">
                                <div class="relative">
                                    <img src="{{ $image }}" alt="{{ $venue->name }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-52 w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                    <span class="absolute left-3 top-3 rounded-full bg-white/94 px-3 py-1 text-xs font-extrabold text-[#2f6bff] shadow-sm">{{ $venue->category?->name ?? 'Espace événementiel' }}</span>
                                </div>
                                <div class="p-4">
                                    <h3 class="line-clamp-2 text-lg font-extrabold tracking-[-0.01em] text-[#151821]">{{ $venue->name }}</h3>
                                <p class="mt-2 text-xs font-bold text-[#6f7890]">{{ collect([$venue->district, $venue->city])->filter()->implode(', ') }} · jusqu’à {{ $venue->max_capacity }} invités</p>
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

        <x-navigation.public-footer />
    </main>

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
                const third = `rgb(${Math.max(0, avgR - 35)}, ${Math.max(0, avgG - 35)}, ${Math.max(0, avgB - 35)})`;

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
                    cover.style.backgroundImage = cover.dataset.defaultGradient || 'linear-gradient(135deg, #1d4ed8, #2f6bff 45%, #9bb8ff)';
                };
                img.src = source;
            });
        })();
    </script>
</x-layouts.baobaa>
