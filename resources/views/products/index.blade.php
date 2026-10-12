@php
    $field = 'mt-1 w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-[13px] font-extrabold text-[#151821] outline-none focus:border-[#2f6bff]';
@endphp

<x-layouts.baobaa title="Location et vente de matériel événementiel - BAOBAA" description="Louez ou achetez chaises, tables, sonorisation, lumière, décoration et matériel événementiel auprès de prestataires vérifiés, avec livraison ou retrait.">
    <main class="min-h-screen bg-[#eef3ff] text-[#151821]">
        <x-navigation.public-header active="products" />

        <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8">
            <p class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff] shadow-sm ring-1 ring-[#dce6f7]">Location et vente</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-extrabold leading-tight tracking-[-0.05em] sm:text-5xl">Tout le matériel de votre événement, livré ou à retirer.</h1>
            <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-[#667088]">Chaises, tables, sonorisation, lumière, décoration… Comparez les prix, vérifiez le stock à vos dates et commandez auprès de prestataires vérifiés.</p>

            <div class="mt-7 grid gap-6 lg:grid-cols-[300px_1fr]">
                <form method="GET" action="{{ route('products.index') }}" class="h-max rounded-[24px] border border-[#dce6f7] bg-white p-4 shadow-xl shadow-[#173e7a]/8 lg:sticky lg:top-24">
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

                <div>
                    <p class="mb-4 text-sm font-extrabold text-[#4d5872]">{{ $products->total() }} article{{ $products->total() > 1 ? 's' : '' }}</p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
                                <p class="text-lg font-extrabold">Aucun article ne correspond à votre recherche.</p>
                                <a href="{{ route('products.index') }}" class="mt-4 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white">Voir tout le catalogue</a>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-6">{{ $products->links() }}</div>
                </div>
            </div>
        </section>

        <x-navigation.public-footer />
    </main>
</x-layouts.baobaa>
