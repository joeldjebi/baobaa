@php
    $publicName = $serviceProviderProfile->business_name;
    $isVerified = $serviceProviderProfile->verification_status->value === 'verified';
    $hasFilters = filled($filters['q'] ?? null) || filled($filters['type_id'] ?? null);
@endphp

<x-layouts.baobaa :title="$publicName.' - BAOBAA'" :description="\Illuminate\Support\Str::limit($serviceProviderProfile->description ?: 'Découvrez les services événementiels de '.$publicName.' sur BAOBAA.', 160)" :image="$serviceProviderProfile->logo_url">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <x-navigation.public-header active="event-services" cta-label="Devenir professionnel" :cta-url="route('service-provider.register')" />

        <section class="relative overflow-hidden px-5 py-8 sm:px-8">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,#f8fbff_0%,#eef3ff_62%,#f7f4ff_100%)]"></div>
            <div class="relative mx-auto max-w-7xl">
                <div class="baobaa-scroll-progress" data-scroll-progress aria-hidden="true"></div>
                <a href="{{ route('service-provider-profiles.index') }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-[#2f6bff] transition hover:translate-x-[-2px]">
                    <span>←</span>
                    <span>Tous les prestataires</span>
                </a>

                <div class="baobaa-hero-card mt-5 overflow-hidden rounded-[28px] border border-white bg-white shadow-2xl shadow-[#173e7a]/10">
                    <div class="partner-cover h-40 bg-[linear-gradient(135deg,#1d4ed8,#2f6bff_45%,#9bb8ff)]" data-partner-cover="{{ $serviceProviderProfile->logo_url ?? '' }}" data-default-gradient="linear-gradient(135deg, #1d4ed8, #2f6bff 45%, #9bb8ff)" style="background-image: linear-gradient(135deg, #1d4ed8, #2f6bff 45%, #9bb8ff);"></div>
                    <div class="p-6 sm:p-8">
                        <div class="-mt-20 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                                <span class="grid size-28 shrink-0 place-items-center overflow-hidden rounded-[30px] border-4 border-white bg-[#eaf1ff] text-5xl font-extrabold text-[#2f6bff] shadow-xl shadow-[#173e7a]/12">
                                    @if ($serviceProviderProfile->logo_url)
                                        <img src="{{ $serviceProviderProfile->logo_url }}" alt="{{ $serviceProviderProfile->logo_alt_text ?? $publicName }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/baobaa.jpg') }}';" class="h-full w-full object-cover">
                                    @else
                                        {{ strtoupper(substr($publicName, 0, 1)) }}
                                    @endif
                                </span>
                                <div class="pb-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h1 class="text-3xl font-extrabold tracking-[-0.015em] text-[#151821] sm:text-5xl">{{ $publicName }}</h1>
                                        <span class="rounded-full {{ $isVerified ? 'bg-[#ecfdf5] text-[#047857]' : 'bg-[#fff7ed] text-[#c2410c]' }} px-3 py-1 text-xs font-extrabold">{{ $isVerified ? 'Équipe vérifiée' : 'Vérification en cours' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-bold text-[#6f7890]">{{ $serviceProviderProfile->district ? $serviceProviderProfile->district.', ' : '' }}{{ $serviceProviderProfile->city }}, {{ $serviceProviderProfile->country_code }} · {{ $serviceProviderProfile->published_services_count }} service{{ $serviceProviderProfile->published_services_count > 1 ? 's' : '' }} disponible{{ $serviceProviderProfile->published_services_count > 1 ? 's' : '' }}</p>
                                    <p class="mt-3 max-w-2xl text-[15px] font-normal leading-7 text-[#52617b]">{{ $serviceProviderProfile->description ?: 'Une équipe événementielle disponible pour accompagner votre projet.' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('event-services.index', ['prestataire' => $serviceProviderProfile->public_uuid]) }}" class="inline-flex items-center justify-center rounded-full bg-[#2f6bff] px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Réserver ces services</a>
                        </div>

                        <div class="mt-8 grid gap-3 md:grid-cols-3" data-reveal-stagger="90">
                            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ $serviceProviderProfile->published_services_count }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">services disponibles</p>
                            </div>
                            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">{{ $serviceProviderProfile->service_area ?: 'Zone élargie' }}</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">zone d’intervention</p>
                            </div>
                            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                                <p class="text-2xl font-extrabold text-[#151821]">Sur devis</p>
                                <p class="mt-1 text-xs font-bold text-[#6f7890]">négociation encadrée</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="services" class="mt-8 scroll-mt-24" data-reveal>
                    <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#2f6bff]">Prestations disponibles</p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.015em] text-[#151821]">Services disponibles</h2>
                        </div>
                        <a href="{{ route('event-services.index', ['prestataire' => $serviceProviderProfile->public_uuid]) }}" class="inline-flex items-center justify-center rounded-full bg-white px-5 py-2.5 text-sm font-extrabold text-[#2f6bff] shadow-sm ring-1 ring-[#c9d8ef] transition hover:-translate-y-0.5 hover:ring-[#2f6bff]">Composer une demande</a>
                    </div>

                    @if ($services->isNotEmpty() || $hasFilters)
                        <form method="GET" action="{{ route('service-provider-profiles.show', $serviceProviderProfile->public_uuid) }}" class="mb-6 grid gap-3 rounded-[24px] border border-white bg-white/86 p-3 shadow-sm ring-1 ring-[#dce6f7] backdrop-blur lg:grid-cols-[minmax(0,1fr)_260px_auto]">
                            <label class="flex min-h-12 items-center gap-3 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 focus-within:border-[#2f6bff] focus-within:bg-white">
                                <svg class="size-5 shrink-0 text-[#2f6bff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher dans les services" class="w-full bg-transparent text-sm font-bold text-[#151821] outline-none placeholder:text-[#9aa5ba]">
                            </label>
                            <label class="block rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-3 py-2">
                                <span class="block text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">Type</span>
                                <select name="type_id" class="mt-0.5 w-full bg-transparent text-sm font-extrabold text-[#151821] outline-none">
                                    <option value="">Tous les services</option>
                                    @foreach ($serviceTypes as $serviceType)
                                        <option value="{{ $serviceType->id }}" @selected((int) ($filters['type_id'] ?? 0) === (int) $serviceType->id)>{{ $serviceType->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="flex items-center gap-2">
                                @if ($hasFilters)
                                    <a href="{{ route('service-provider-profiles.show', $serviceProviderProfile->public_uuid) }}" class="inline-flex h-12 items-center rounded-full border border-[#dce6f7] bg-white px-4 text-xs font-extrabold text-[#52617b] transition hover:border-[#2f6bff] hover:text-[#2f6bff]">Effacer</a>
                                @endif
                                <button class="inline-flex h-12 items-center rounded-full bg-[#2f6bff] px-5 text-xs font-extrabold text-white shadow-lg shadow-[#2f6bff]/20 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Filtrer</button>
                            </div>
                        </form>
                    @endif

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal-stagger="70">
                        @forelse ($services as $service)
                            <article class="overflow-hidden rounded-[22px] border border-[#dce6f7] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#173e7a]/12">
                                <div class="relative">
                                    <img src="{{ $service->cover_image_url }}" alt="{{ $service->name }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="aspect-[4/3] w-full object-cover">
                                    <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1.5 text-xs font-extrabold text-[#2f6bff] shadow-sm">{{ $service->type?->name ?? 'Service événementiel' }}</span>
                                    @if ($service->media->count() > 1)
                                        <span class="absolute bottom-3 right-3 rounded-full bg-[#07152f]/80 px-3 py-1 text-xs font-extrabold text-white">{{ $service->media->count() }} photos</span>
                                    @endif
                                </div>
                                <div class="p-5">
                                <h3 class="mt-4 line-clamp-2 text-xl font-extrabold tracking-[-0.015em] text-[#151821]">{{ $service->name }}</h3>
                                <p class="mt-3 line-clamp-3 text-sm font-semibold leading-6 text-[#52617b]">{{ $service->short_description ?: $service->description }}</p>
                                <div class="mt-5 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#e2eaf8]">
                                    <div class="flex items-center justify-between gap-3 text-sm font-extrabold">
                                        <span class="text-[#6f7890]">À partir de</span>
                                        <span class="text-[#151821]">{{ number_format($service->starting_price, 0, ',', ' ') }} {{ $service->currency }}</span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-3 text-sm font-bold">
                                        <span class="text-[#6f7890]">Intervention</span>
                                        <span class="text-right text-[#151821]">{{ $service->service_area ?: $serviceProviderProfile->city }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('event-services.index', ['prestataire' => $serviceProviderProfile->public_uuid]) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5 hover:bg-[#2258df]">Réserver ce service</a>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[18px] border border-dashed border-[#cbd8f4] bg-white p-8 text-sm font-bold text-[#6f7890]">
                                Aucun service public disponible pour cette équipe.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        @if (method_exists($services, 'links'))
                            {{ $services->links() }}
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
