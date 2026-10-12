@php
    $providerCards = $profiles->map(fn ($profile) => [
        'name' => $profile->business_name,
        'public_uuid' => $profile->public_uuid,
        'logo_url' => $profile->logo_url,
        'logo_alt_text' => $profile->logo_alt_text,
        'city' => $profile->city,
        'district' => $profile->district,
        'country' => $profile->country_code,
        'service_area' => $profile->service_area,
        'description' => $profile->description,
        'services_count' => $profile->published_services_count,
        'starting_price' => $profile->services->min('starting_price'),
        'currency' => $profile->services->first()?->currency ?? 'XOF',
        'services' => $profile->services,
    ])->values();
    $hasFilters = filled($filters['q'] ?? null) || filled($filters['city'] ?? null) || filled($filters['type_id'] ?? null);
@endphp

<x-layouts.baobaa title="Prestataires événementiels - BAOBAA" description="Sonorisation, lumière, photo, vidéo, décoration, traiteur : trouvez des prestataires événementiels vérifiés et composez votre événement.">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 px-5 py-3 shadow-sm shadow-[#173e7a]/5 sm:px-8 baobaa-sticky-stable">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 text-[#2f6bff]">
                    <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
                </a>

                <x-navigation.public-menu active="event-services" />

                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('service-provider.register') }}" class="hidden rounded-full bg-[#151821] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#151821]/10 transition hover:-translate-y-0.5 hover:bg-[#2f6bff] lg:inline-flex">Rejoindre BAOBAA</a>
                    @endguest
                    <x-navigation.account-menu />
                    <x-navigation.mobile-menu active="event-services" />
                </div>
            </div>
        </header>

        <section class="relative overflow-hidden px-5 pb-12 pt-8 sm:px-8">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f7faff_0%,#eef3ff_44%,#f7f4ff_100%)]"></div>
            <div class="absolute inset-x-0 top-0 h-72 bg-[linear-gradient(135deg,rgba(47,107,255,.14),rgba(255,255,255,0)_58%)]"></div>

            <div class="relative mx-auto max-w-7xl">
                <div class="overflow-hidden rounded-[34px] border border-white bg-white/78 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] backdrop-blur">
                    <div class="grid gap-0 lg:grid-cols-[1.08fr_.92fr]">
                        <div class="p-6 sm:p-8 lg:p-10">
                            <p class="inline-flex rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff] ring-1 ring-[#dce6f7]">Prestataires événementiels</p>
                            <h1 class="mt-5 max-w-4xl text-4xl font-extrabold leading-[1.02] tracking-[-0.055em] text-[#151821] sm:text-6xl">Des équipes fiables pour composer un événement sans friction</h1>
                            <p class="mt-4 max-w-2xl text-base font-semibold leading-7 text-[#667088]">Repérez les spécialistes du son, de la lumière, de l’image, du décor ou de la scène, puis construisez une demande claire en quelques étapes.</p>

                            <div class="mt-7 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                    <p class="text-2xl font-extrabold text-[#151821]">{{ number_format($providersCount, 0, ',', ' ') }}</p>
                                    <p class="mt-1 text-xs font-bold text-[#6f7890]">prestataire{{ $providersCount > 1 ? 's' : '' }} vérifié{{ $providersCount > 1 ? 's' : '' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                                    <p class="text-2xl font-extrabold text-[#151821]">{{ number_format($publishedServicesCount, 0, ',', ' ') }}</p>
                                    <p class="mt-1 text-xs font-bold text-[#6f7890]">service{{ $publishedServicesCount > 1 ? 's' : '' }} disponible{{ $publishedServicesCount > 1 ? 's' : '' }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#07162f] p-4 shadow-lg shadow-[#07162f]/14">
                                    <p class="text-2xl font-extrabold text-white">Sur mesure</p>
                                    <p class="mt-1 text-xs font-bold text-[#b9cfff]">devis et échanges</p>
                                </div>
                            </div>
                        </div>

                        <div class="relative min-h-[360px] bg-[#07162f] p-6 text-white sm:p-8 lg:p-10">
                            <div class="absolute inset-0 bg-[linear-gradient(135deg,#07162f_0%,#123c86_58%,#2f6bff_100%)]"></div>
                            <div class="absolute inset-0 opacity-30 [background-image:linear-gradient(rgba(255,255,255,.12)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.12)_1px,transparent_1px)] [background-size:42px_42px]"></div>

                            <div class="relative flex h-full flex-col justify-between">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#bfd2ff]">Recherche rapide</p>
                                    <form method="GET" action="{{ route('service-provider-profiles.index') }}" class="mt-4 rounded-[26px] bg-white p-3 shadow-2xl shadow-black/20">
                                        <label class="flex items-center gap-3 rounded-2xl bg-[#f8fbff] px-4 py-3 ring-1 ring-[#dce6f7] focus-within:ring-[#2f6bff]">
                                            <svg class="size-5 shrink-0 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                                            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Équipe, ville ou service" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:text-[#9aa5ba]">
                                        </label>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <label class="block rounded-2xl bg-[#f8fbff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                                <span class="block text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">Ville</span>
                                                <select name="city" class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                                    <option value="">Toutes les villes</option>
                                                    @foreach ($cities as $city)
                                                        <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label class="block rounded-2xl bg-[#f8fbff] px-3 py-2.5 ring-1 ring-[#dce6f7]">
                                                <span class="block text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">Service</span>
                                                <select name="type_id" class="mt-1 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                                    <option value="">Tous les services</option>
                                                    @foreach ($serviceTypes as $serviceType)
                                                        <option value="{{ $serviceType->id }}" @selected((int) ($filters['type_id'] ?? 0) === (int) $serviceType->id)>{{ $serviceType->name }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                            @if ($hasFilters)
                                                <a href="{{ route('service-provider-profiles.index') }}" class="rounded-full px-4 py-2 text-xs font-extrabold text-[#52617b] transition hover:bg-[#eef4ff] hover:text-[#2f6bff]">Effacer</a>
                                            @else
                                                <span class="text-xs font-bold text-[#8a94aa]">Profils vérifiés uniquement</span>
                                            @endif
                                            <button class="rounded-full bg-[#2f6bff] px-5 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Filtrer</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="mt-6 flex flex-wrap gap-2">
                                    @foreach ($serviceTypes->take(5) as $serviceType)
                                        <a href="{{ route('service-provider-profiles.index', ['type_id' => $serviceType->id]) }}" class="rounded-full bg-white/12 px-3 py-1.5 text-xs font-extrabold text-white ring-1 ring-white/15 transition hover:bg-white hover:text-[#2f6bff]">{{ $serviceType->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-3 rounded-[24px] border border-white bg-white/70 p-4 shadow-sm ring-1 ring-[#dce6f7] backdrop-blur md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-extrabold text-[#151821]">{{ number_format($profiles->total(), 0, ',', ' ') }} prestataire{{ $profiles->total() > 1 ? 's' : '' }} {{ $hasFilters ? 'correspondant'.($profiles->total() > 1 ? 's' : '').' à votre recherche' : 'disponible'.($profiles->total() > 1 ? 's' : '') }}</p>
                        <p class="mt-1 text-xs font-bold text-[#6f7890]">Une sélection organisée pour comparer vite les équipes et leurs prestations.</p>
                    </div>
                    @if ($hasFilters)
                        <div class="flex flex-wrap gap-2 text-xs font-extrabold text-[#52617b]">
                            @if (filled($filters['q'] ?? null))
                                <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">Recherche : {{ $filters['q'] }}</span>
                            @endif
                            @if (filled($filters['city'] ?? null))
                                <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">Ville : {{ $filters['city'] }}</span>
                            @endif
                            @if (filled($filters['type_id'] ?? null) && ($selectedServiceType = $serviceTypes->firstWhere('id', $filters['type_id'])))
                                <span class="rounded-full bg-white px-3 py-1.5 ring-1 ring-[#dce6f7]">Service : {{ $selectedServiceType->name }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($providerCards as $profile)
                        <a href="{{ route('service-provider-profiles.show', $profile['public_uuid']) }}" class="group overflow-hidden rounded-[24px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12">
                            <div class="partner-cover relative h-28 bg-[linear-gradient(135deg,#2f6bff,#7ea2ff_55%,#eef4ff)]" data-partner-cover="{{ $profile['logo_url'] ? $profile['logo_url'] : '' }}" data-default-gradient="linear-gradient(135deg, #2f6bff, #7ea2ff 55%, #eef4ff)" style="background-image: linear-gradient(135deg, #2f6bff, #7ea2ff 55%, #eef4ff);"></div>
                            <div class="p-5">
                                <div class="-mt-14 flex items-end justify-between gap-4">
                                    <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-[22px] border-4 border-white bg-[#eaf1ff] text-3xl font-extrabold text-[#2f6bff] shadow-lg shadow-[#173e7a]/8">
                                        @if ($profile['logo_url'])
                                            <img src="{{ $profile['logo_url'] }}" alt="{{ $profile['logo_alt_text'] ?? $profile['name'] }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(substr($profile['name'], 0, 1)) }}
                                        @endif
                                    </span>
                                    <span class="mb-2 rounded-full bg-[#ecfdf5] px-3 py-1 text-xs font-extrabold text-[#047857]">Vérifié</span>
                                </div>
                                <h2 class="mt-4 line-clamp-1 text-xl font-extrabold tracking-[-0.035em] text-[#151821]">{{ $profile['name'] }}</h2>
                                <p class="mt-1 text-sm font-bold text-[#6f7890]">{{ collect([$profile['district'], $profile['city'], $profile['country']])->filter()->implode(', ') }}</p>
                                <p class="mt-3 line-clamp-2 text-sm font-semibold leading-6 text-[#52617b]">{{ $profile['description'] ?: 'Une équipe événementielle disponible pour accompagner votre projet.' }}</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @forelse ($profile['services']->take(2) as $service)
                                        <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $service->type?->name ?? $service->name }}</span>
                                    @empty
                                        <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">{{ $profile['services_count'] }} service{{ $profile['services_count'] > 1 ? 's' : '' }}</span>
                                    @endforelse
                                </div>
                                <div class="mt-5 flex items-center justify-between border-t border-[#edf2fb] pt-4">
                                    <span class="rounded-full bg-[#f4f7ff] px-3 py-1.5 text-xs font-extrabold text-[#4d5872]">Dès {{ number_format((int) $profile['starting_price'], 0, ',', ' ') }} {{ $profile['currency'] }}</span>
                                    <span class="inline-flex items-center gap-1 text-sm font-extrabold text-[#2f6bff] transition group-hover:translate-x-1">Voir <span>→</span></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($providerCards->isEmpty())
                    <div class="mt-6 rounded-[24px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center shadow-sm">
                        <p class="text-xl font-extrabold text-[#151821]">Aucun prestataire ne correspond à ces critères.</p>
                        <p class="mt-2 text-sm font-semibold text-[#6f7890]">Essayez une autre ville, un autre service ou une recherche plus large.</p>
                        <a href="{{ route('service-provider-profiles.index') }}" class="mt-5 inline-flex rounded-full bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Réinitialiser</a>
                    </div>
                @endif

                @if ($profiles->hasPages())
                    <div class="mt-8">
                        {{ $profiles->links() }}
                    </div>
                @endif
            </div>
        </section>

        <x-navigation.public-footer />

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
    </main>
</x-layouts.baobaa>
