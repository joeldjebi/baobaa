<div data-venues-results>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-extrabold text-[#4d5872]">{{ $venues->total() }} espace{{ $venues->total() > 1 ? 's' : '' }} trouvé{{ $venues->total() > 1 ? 's' : '' }}</p>
            <p class="mt-1 text-xs font-bold text-[#8a94aa]">Les catégories sans espace publié sont masquées automatiquement.</p>
        </div>
        <div class="rounded-full border border-[#dce6f7] bg-white px-4 py-2 text-xs font-extrabold text-[#6f7890] shadow-sm">Tri : espaces récents</div>
    </div>

    <div class="space-y-4">
        @forelse ($venues as $venue)
            @php
                $image = $venue->media->sortBy('sort_order')->first()?->signed_url
                    ?? 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=85';
            @endphp
            <a href="{{ route('venues.show', $venue->slug) }}" class="group grid overflow-hidden rounded-[1.5rem] bg-white shadow-sm ring-1 ring-[#dce6f7] transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#2f6bff]/12 md:grid-cols-[220px_1fr] xl:grid-cols-[250px_1fr_190px]">
                <div class="relative min-h-52 overflow-hidden md:min-h-full">
                    <img src="{{ $image }}" alt="{{ $venue->name }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-[#2f6bff] shadow-sm">Vérifié</span>
                    <span class="absolute bottom-4 left-4 rounded-full bg-[#151821]/78 px-3 py-1 text-xs font-extrabold text-white shadow-sm">{{ $venue->category?->name ?? 'Espace événementiel' }}</span>
                </div>
                <div class="p-5">
                    <p class="text-sm font-semibold text-[#6b7b99]">{{ $venue->district }}, {{ $venue->city }}</p>
                    <h2 class="mt-2 text-xl font-extrabold leading-7 tracking-[-0.025em] text-[#081225]">{{ $venue->name }}</h2>
                    <p class="mt-2 line-clamp-2 text-sm font-medium leading-6 text-[#6b7b99]">{{ $venue->short_description ?: $venue->description }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#f4f7ff] px-3 py-1 text-xs font-extrabold text-[#4d5872]">{{ $venue->min_capacity }}-{{ $venue->max_capacity }} invités</span>
                        @if ($venue->surface_area)
                            <span class="rounded-full bg-[#f4f7ff] px-3 py-1 text-xs font-extrabold text-[#4d5872]">{{ $venue->surface_area }} m²</span>
                        @endif
                        <span class="rounded-full bg-[#edf4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ number_format((float) $venue->average_rating, 1, ',', ' ') }} ★</span>
                    </div>
                </div>
                <div class="flex border-t border-[#edf2fb] p-5 md:col-span-2 xl:col-span-1 xl:border-l xl:border-t-0">
                    <div class="flex w-full flex-row items-center justify-between gap-4 xl:flex-col xl:items-start xl:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">À partir de</p>
                            <p class="mt-1 text-lg font-extrabold text-[#2f6bff]">{{ number_format($venue->starting_price, 0, ',', ' ') }} {{ $venue->currency }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#2f6bff] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition group-hover:bg-[#2258df]">
                            Voir détails
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-[28px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center shadow-sm">
                <p class="text-xl font-extrabold tracking-[-0.03em] text-[#151821]">Aucun espace ne correspond à ces filtres.</p>
                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-[#6f7890]">Essayez d’élargir la période, le budget ou la capacité pour afficher plus de résultats.</p>
                <a href="{{ route('venues.index') }}" class="mt-5 inline-flex rounded-full bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Réinitialiser la recherche</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8" data-venues-pagination>
        {{ $venues->links() }}
    </div>
</div>
