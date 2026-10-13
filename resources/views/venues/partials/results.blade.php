<div data-venues-results>
    @php
        $filterLabels = [
            'q' => fn ($value) => '« '.$value.' »',
            'country' => fn ($value) => 'Pays : '.$value,
            'city' => fn ($value) => $value,
            'category' => fn ($value) => 'Catégorie : '.\Illuminate\Support\Str::headline($value),
            'capacity' => fn ($value) => $value.' invités min.',
            'min_price' => fn ($value) => 'Dès '.number_format((int) $value, 0, ',', ' ').' XOF',
            'max_price' => fn ($value) => 'Jusqu’à '.number_format((int) $value, 0, ',', ' ').' XOF',
            'start_date' => fn ($value) => 'Du '.\Carbon\Carbon::parse($value)->format('d/m'),
            'end_date' => fn ($value) => 'Au '.\Carbon\Carbon::parse($value)->format('d/m'),
            'start_time' => fn ($value) => 'Dès '.$value,
            'end_time' => fn ($value) => 'Jusqu’à '.$value,
        ];
        $activeFilterChips = collect($filterLabels)
            ->filter(fn ($label, $key) => request()->filled($key))
            ->map(function ($label, $key) {
                try {
                    return ['label' => $label((string) request($key)), 'url' => route('venues.index', request()->except([$key, 'page']))];
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter();
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm font-extrabold text-[#4d5872]" aria-live="polite">{{ $venues->total() }} espace{{ $venues->total() > 1 ? 's' : '' }} trouvé{{ $venues->total() > 1 ? 's' : '' }}</p>
        <label class="flex items-center gap-2 rounded-full border border-[#dce6f7] bg-white px-4 py-1.5 text-xs font-extrabold text-[#6f7890] shadow-sm">
            Trier par
            <select name="sort" form="venues-filter-form" data-sort-select class="bg-transparent text-xs font-extrabold text-[#151821] outline-none">
                @foreach (\App\Http\Controllers\PublicVenueController::sortOptions() as $value => $label)
                    <option value="{{ $value }}" @selected(request('sort', 'recent') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>

    @if ($activeFilterChips->isNotEmpty())
        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ($activeFilterChips as $chip)
                <a href="{{ $chip['url'] }}" data-filter-chip class="inline-flex items-center gap-1.5 rounded-full bg-[#eef4ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff] ring-1 ring-[#cfdcf7] transition hover:bg-[#2f6bff] hover:text-white" aria-label="Retirer le filtre {{ $chip['label'] }}">
                    {{ $chip['label'] }} <span aria-hidden="true">×</span>
                </a>
            @endforeach
        </div>
    @endif

    <div class="space-y-4" data-reveal-stagger="70">
        @forelse ($venues as $venue)
            @php
                $image = $venue->cover_image_url;
                $publicReviewsCount = (int) $venue->public_reviews_count;
            @endphp
            <a href="{{ route('venues.show', $venue->slug) }}" class="group grid overflow-hidden rounded-[1.5rem] bg-white shadow-sm ring-1 ring-[#dce6f7] transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#2f6bff]/12 md:grid-cols-[220px_1fr] xl:grid-cols-[250px_1fr_190px]">
                <div class="relative min-h-52 overflow-hidden md:min-h-full">
                    <img src="{{ $image }}" alt="{{ $venue->name }}" width="400" height="300" decoding="async" referrerpolicy="no-referrer" loading="{{ $loop->index < 2 ? 'eager' : 'lazy' }}" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                    @if ($venue->verification_status === \App\Enums\VerificationStatus::Verified)
                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-[#2f6bff] shadow-sm">Vérifié</span>
                    @endif
                    <span class="absolute bottom-4 left-4 rounded-full bg-[#151821]/78 px-3 py-1 text-xs font-extrabold text-white shadow-sm">{{ $venue->category?->name ?? 'Espace événementiel' }}</span>
                </div>
                <div class="p-5">
                    <p class="text-sm font-semibold text-[#6b7b99]">{{ collect([$venue->district, $venue->city])->filter()->implode(', ') }}</p>
                    <h2 class="mt-2 text-xl font-extrabold leading-7 tracking-[-0.01em] text-[#081225]">{{ $venue->name }}</h2>
                    <p class="mt-2 line-clamp-2 text-sm font-medium leading-6 text-[#6b7b99]">{{ $venue->short_description ?: $venue->description }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#f4f7ff] px-3 py-1 text-xs font-extrabold text-[#4d5872]">{{ $venue->min_capacity }}-{{ $venue->max_capacity }} invités</span>
                        @if ($venue->openingHoursLabel())
                            <span class="rounded-full bg-[#f4f7ff] px-3 py-1 text-xs font-extrabold text-[#4d5872]">{{ $venue->openingHoursLabel() }}</span>
                        @endif
                        @if ($venue->surface_area)
                            <span class="rounded-full bg-[#f4f7ff] px-3 py-1 text-xs font-extrabold text-[#4d5872]">{{ $venue->surface_area }} m²</span>
                        @endif
                        @if ($publicReviewsCount > 0)
                            <span class="rounded-full bg-[#edf4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ number_format((float) $venue->public_rating, 1, ',', ' ') }} ★ ({{ $publicReviewsCount }} avis)</span>
                        @endif
                    </div>
                </div>
                <div class="flex border-t border-[#edf2fb] p-5 md:col-span-2 xl:col-span-1 xl:border-l xl:border-t-0">
                    <div class="flex w-full flex-row items-center justify-between gap-4 xl:flex-col xl:items-start xl:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#8a94aa]">À partir de</p>
                            <p class="mt-1 text-lg font-extrabold text-[#2f6bff]">{{ number_format($venue->starting_price, 0, ',', ' ') }} {{ $venue->currency }}</p>
                            <p class="text-xs font-bold text-[#6f7890]">{{ ($venue->price_unit ?? \App\Enums\PriceUnit::Event)->label() }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#2f6bff] px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25 transition group-hover:bg-[#2258df]">
                            Voir détails
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-[28px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center shadow-sm">
                <p class="text-xl font-extrabold tracking-[-0.015em] text-[#151821]">Aucun espace ne correspond à ces filtres.</p>
                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-[#6f7890]">Essayez d’élargir la période, le budget ou la capacité pour afficher plus de résultats.</p>
                <a href="{{ route('venues.index') }}" class="mt-5 inline-flex rounded-full bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Réinitialiser la recherche</a>
            </div>
        @endforelse
    </div>

    <div class="mt-8" data-venues-pagination>
        {{ $venues->links() }}
    </div>
</div>
