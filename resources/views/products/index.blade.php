@php
    $field = 'mt-1 w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-[13px] font-extrabold text-[#151821] outline-none focus:border-[#2f6bff]';
@endphp

<x-layouts.baobaa title="Location et vente de matériel événementiel - BAOBAA" description="Louez ou achetez chaises, tables, sonorisation, lumière, décoration et matériel événementiel auprès de prestataires vérifiés, avec livraison ou retrait.">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <x-navigation.public-header active="products" />

        <section class="baobaa-page-hero border-b border-white/80 px-5 py-7 sm:px-8 lg:py-9">
            <div class="baobaa-hero-intro mx-auto max-w-7xl">
                <nav class="text-xs font-semibold text-[#6f7890]" aria-label="Fil d’Ariane"><a href="{{ url('/') }}" class="hover:text-[#2f6bff]">Accueil</a> / Location et vente</nav>
                <h1 class="mt-2 text-3xl font-extrabold tracking-[-0.015em] text-[#121722] sm:text-4xl">Location et vente de matériel événementiel</h1>
                <p class="mt-2 max-w-2xl text-[15px] font-normal leading-7 text-[#5b6680]">Chaises, tables, sonorisation, lumière, décoration… Vérifiez le stock à vos dates, puis faites-vous livrer ou retirez le matériel.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($categories->take(8) as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" @class([
                            'rounded-full px-4 py-2 text-xs font-extrabold transition',
                            'bg-[#2f6bff] text-white' => request('category') === $category->slug,
                            'bg-white text-[#4d5872] ring-1 ring-[#dce6f7] hover:text-[#2f6bff]' => request('category') !== $category->slug,
                        ])>{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <div class="grid gap-6 lg:grid-cols-[300px_1fr]">
                <div>
                <button type="button" data-filters-toggle aria-expanded="false" aria-controls="products-filter-form" class="mb-3 flex w-full items-center justify-between rounded-2xl bg-white px-4 py-3 text-sm font-extrabold text-[#151821] shadow-sm ring-1 ring-[#dce6f7] lg:hidden">
                    <span>Filtres{{ count(request()->except('page')) > 0 ? ' actifs' : '' }}</span>
                    <span aria-hidden="true">⌄</span>
                </button>
                <form method="GET" action="{{ route('products.index') }}" id="products-filter-form" class="hidden h-max rounded-[24px] border border-[#dce6f7] bg-white p-4 shadow-xl shadow-[#173e7a]/8 lg:sticky lg:top-24 lg:block">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-extrabold">Filtres</p>
                        @if (request()->query())
                            <a href="{{ route('products.index') }}" class="text-xs font-extrabold text-[#2f6bff]">Effacer</a>
                        @endif
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-1 rounded-2xl bg-[#f2f6ff] p-1 text-xs font-extrabold">
                        @foreach (['' => 'Tout', 'rental' => 'Louer', 'sale' => 'Acheter'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="mode" value="{{ $value }}" class="peer sr-only" @checked(($filters['mode'] ?? '') === $value)>
                                <span class="block rounded-xl px-2 py-2 text-center text-[#52617b] peer-checked:bg-white peer-checked:text-[#2f6bff] peer-checked:shadow-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <label class="mt-3 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Recherche<input name="q" value="{{ request('q') }}" placeholder="Chaise, enceinte..." class="{{ $field }}"></label>
                    <label class="mt-3 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Catégorie
                        <select name="category" class="{{ $field }}">
                            <option value="">Toutes</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="mt-3 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Ville
                        <select name="city" class="{{ $field }}">
                            <option value="">Toutes</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="mt-3 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Prix maximum (XOF)<input type="number" min="1" name="max_price" value="{{ request('max_price') }}" class="{{ $field }}"></label>
                    <p class="mt-3 text-[11px] font-extrabold uppercase text-[#8a94aa]">Disponible pour la location</p>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" name="start_date" min="{{ now()->toDateString() }}" value="{{ $filters['start_date'] }}" aria-label="Du" class="{{ $field }}">
                        <input type="date" name="end_date" min="{{ now()->toDateString() }}" value="{{ $filters['end_date'] }}" aria-label="Au" class="{{ $field }}">
                    </div>
                    <button class="mt-4 w-full rounded-xl bg-[#2f6bff] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Appliquer</button>
                </form>
                </div>

                <div>
                    <p class="mb-4 text-sm font-extrabold text-[#4d5872]">{{ $products->total() }} article{{ $products->total() > 1 ? 's' : '' }}</p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-reveal-stagger="70">
                        @forelse ($products as $product)
                            <a href="{{ route('products.show', $product) }}" class="group overflow-hidden rounded-[22px] bg-white shadow-sm ring-1 ring-[#dce6f7] transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-[#2f6bff]/12">
                                <div class="relative">
                                    <img src="{{ $product->cover_image_url }}" alt="{{ $product->name }}" referrerpolicy="no-referrer" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Models\Venue::placeholderImageUrl() }}';" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105">
                                    <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[11px] font-extrabold text-[#2f6bff]">{{ $product->category?->name }}</span>
                                </div>
                                <div class="p-4">
                                    <h2 class="line-clamp-2 text-base font-extrabold text-[#081225]">{{ $product->name }}</h2>
                                    <p class="mt-1 text-xs font-bold text-[#6b7b99]">{{ $product->serviceProviderProfile?->business_name }} · {{ $product->city }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2 border-t border-[#edf2fb] pt-3 text-xs font-extrabold">
                                        @if ($product->is_rentable)
                                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-[#2f6bff]">Location {{ number_format($product->rental_price_per_day, 0, ',', ' ') }} {{ $product->currency }}/jour</span>
                                        @endif
                                        @if ($product->is_sellable)
                                            <span class="rounded-full bg-[#ecfdf5] px-3 py-1 text-[#047857]">Achat {{ number_format($product->sale_price, 0, ',', ' ') }} {{ $product->currency }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-[#cbd8f4] bg-white p-10 text-center sm:col-span-2 xl:col-span-3">
                                @if (count(request()->except('page')) > 0)
                                    <p class="text-lg font-extrabold">Aucun article ne correspond à votre recherche.</p>
                                    <p class="mt-2 text-sm font-semibold text-[#6f7890]">Élargissez vos dates, la ville ou le prix maximum.</p>
                                    <a href="{{ route('products.index') }}" class="mt-4 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white">Voir tout le catalogue</a>
                                @else
                                    <p class="text-lg font-extrabold">Le catalogue de matériel ouvre bientôt.</p>
                                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-[#6f7890]">Les professionnels publient actuellement leur matériel. En attendant, trouvez une équipe pour votre événement.</p>
                                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                                        <a href="{{ route('service-provider-profiles.index') }}" class="rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white">Voir les professionnels</a>
                                        <a href="{{ route('service-provider.register') }}" class="rounded-full border border-[#dce6f7] px-5 py-2.5 text-sm font-extrabold text-[#151821]">Je loue du matériel</a>
                                    </div>
                                @endif
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-6">{{ $products->links() }}</div>
                </div>
            </div>
        </section>

        <x-navigation.public-footer />
    </main>

    <script>
        document.querySelector('[data-filters-toggle]')?.addEventListener('click', (event) => {
            const isHidden = document.getElementById('products-filter-form').classList.toggle('hidden');
            event.currentTarget.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
        });
    </script>
</x-layouts.baobaa>
