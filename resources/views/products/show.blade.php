@php
    $money = fn (?int $amount): string => number_format((int) $amount, 0, ',', ' ').' '.$product->currency;
    $images = $product->media->map->url->all();
    $provider = $product->serviceProviderProfile;
    $defaultMode = old('mode', $product->is_rentable ? 'rental' : 'sale');
    $isClient = auth()->user()?->hasPortal(\App\Enums\UserRole::Client) ?? false;
@endphp

<x-layouts.baobaa :title="$product->name.' - Location et vente - BAOBAA'" :description="\Illuminate\Support\Str::limit($product->short_description ?: strip_tags((string) $product->description) ?: $product->name, 160)" :image="$images[0] ?? null">
    <main class="min-h-screen bg-white pb-24 text-[#151821] lg:pb-0">
        <x-navigation.public-header active="products" />

        <section class="mx-auto max-w-7xl px-5 py-6 sm:px-8">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-[#6f7890]" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}" class="hover:text-[#2f6bff]">Accueil</a><span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-[#2f6bff]">Location et vente</a><span>/</span>
                <a href="{{ route('products.index', ['category' => $product->category?->slug]) }}" class="hover:text-[#2f6bff]">{{ $product->category?->name }}</a>
            </nav>

            <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_380px]">
                <div>
                    <div class="overflow-hidden rounded-[22px] ring-1 ring-[#dce6f7]">
                        <img src="{{ $images[0] ?? \App\Models\Venue::placeholderImageUrl() }}" alt="{{ $product->name }}" referrerpolicy="no-referrer" class="aspect-[4/3] w-full object-cover" data-product-main-image>
                    </div>
                    @if (count($images) > 1)
                        <div class="mt-3 flex gap-2 overflow-x-auto baobaa-scrollbar-none">
                            @foreach ($images as $image)
                                <button type="button" data-product-thumb="{{ $image }}" class="h-20 w-28 shrink-0 overflow-hidden rounded-xl ring-1 ring-[#dce6f7]"><img src="{{ $image }}" alt="" class="h-full w-full object-cover" loading="lazy"></button>
                            @endforeach
                        </div>
                    @endif

                    <h1 class="mt-6 text-3xl font-extrabold tracking-[-0.015em]">{{ $product->name }}</h1>
                    <p class="mt-2 text-sm font-semibold text-[#6f7890]">Proposé par <a href="{{ route('service-provider-profiles.show', $provider) }}" class="font-extrabold text-[#2f6bff]">{{ $provider->business_name }}</a> · {{ $product->city }}</p>
                    @if ($product->short_description)
                        <p class="mt-4 text-sm font-bold leading-7">{{ $product->short_description }}</p>
                    @endif
                    @if ($product->description)
                        <p class="mt-3 whitespace-pre-line text-sm font-medium leading-7 text-[#4d5872]">{{ $product->description }}</p>
                    @endif

                    <section class="mt-6 rounded-2xl border border-[#dce6f7] p-4">
                        <h2 class="text-base font-extrabold">Livraison ou retrait</h2>
                        <p class="mt-2 text-sm font-semibold text-[#4d5872]">Retrait gratuit chez le prestataire à {{ $provider->city }}.</p>
                        @if ($provider->deliveryZones->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($provider->deliveryZones as $zone)
                                    <span class="rounded-full bg-[#f4f7ff] px-3 py-1 text-xs font-extrabold text-[#4d5872]">Livraison {{ $zone->city }} : {{ $money((int) $zone->fee) }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-1 text-xs font-semibold text-[#6f7890]">Ce prestataire ne propose pas encore la livraison.</p>
                        @endif
                    </section>
                </div>

                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <form method="POST" action="{{ route('cart.store', $product) }}" class="rounded-[24px] bg-white p-5 shadow-2xl shadow-[#173e7a]/12 ring-1 ring-[#dce6f7]" id="ajouter-au-panier">
                        @csrf
                        <x-forms.validation-errors class="mb-4" />

                        <div class="space-y-2">
                            @if ($product->is_rentable)
                                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-[#dce6f7] p-3 has-[:checked]:border-[#2f6bff] has-[:checked]:bg-[#f4f8ff]">
                                    <span class="flex items-center gap-2 text-sm font-extrabold"><input type="radio" name="mode" value="rental" data-mode-input @checked($defaultMode === 'rental') class="accent-[#2f6bff]">Louer</span>
                                    <span class="text-sm font-extrabold text-[#2f6bff]">{{ $money($product->rental_price_per_day) }} / jour</span>
                                </label>
                            @endif
                            @if ($product->is_sellable)
                                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border border-[#dce6f7] p-3 has-[:checked]:border-[#2f6bff] has-[:checked]:bg-[#f4f8ff]">
                                    <span class="flex items-center gap-2 text-sm font-extrabold"><input type="radio" name="mode" value="sale" data-mode-input @checked($defaultMode === 'sale') class="accent-[#2f6bff]">Acheter</span>
                                    <span class="text-sm font-extrabold text-[#047857]">{{ $money($product->sale_price) }}</span>
                                </label>
                            @endif
                        </div>

                        <div data-rental-fields class="mt-4 grid grid-cols-2 gap-2 {{ $defaultMode === 'rental' ? '' : 'hidden' }}">
                            <label class="text-[11px] font-extrabold uppercase text-[#8a94aa]">Du<input type="date" name="rental_starts_on" min="{{ now()->toDateString() }}" value="{{ old('rental_starts_on') }}" class="mt-1 w-full rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold text-[#151821]"></label>
                            <label class="text-[11px] font-extrabold uppercase text-[#8a94aa]">Au (inclus)<input type="date" name="rental_ends_on" min="{{ now()->toDateString() }}" value="{{ old('rental_ends_on') }}" class="mt-1 w-full rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold text-[#151821]"></label>
                            @if ($product->deposit_amount > 0)
                                <p class="col-span-2 text-xs font-semibold text-[#6f7890]">Caution remboursable : {{ $money((int) $product->deposit_amount) }} par unité, restituée au retour du matériel.</p>
                            @endif
                        </div>

                        <label class="mt-4 block text-[11px] font-extrabold uppercase text-[#8a94aa]">Quantité
                            <input type="number" name="quantity" min="1" max="{{ max(1, $product->stock_quantity) }}" value="{{ old('quantity', 1) }}" class="mt-1 w-full rounded-xl border border-[#dce6f7] px-3 py-2 text-sm font-bold text-[#151821]">
                        </label>
                        <p class="mt-2 text-xs font-semibold text-[#6f7890]">{{ $product->stock_quantity }} unité{{ $product->stock_quantity > 1 ? 's' : '' }} au total @if ($product->is_sellable) · {{ $availableForSale }} disponible{{ $availableForSale > 1 ? 's' : '' }} à l’achat aujourd’hui @endif. La disponibilité en location est vérifiée pour vos dates.</p>

                        @if ($isClient)
                            <button class="mt-4 w-full rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Ajouter au panier</button>
                        @elseif (auth()->check())
                            <p class="mt-4 rounded-2xl bg-[#fff8e8] p-3 text-xs font-bold text-[#8a5b00]">Un compte client est nécessaire pour commander.</p>
                        @else
                            <a href="{{ route('portal.login', ['portal' => 'client', 'redirect' => url()->current()]) }}" class="mt-4 flex w-full justify-center rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Me connecter pour commander</a>
                        @endif
                    </form>
                </aside>
            </div>

            @if ($similarProducts->isNotEmpty())
                <section class="mt-12">
                    <h2 class="text-xl font-extrabold">Dans la même catégorie</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($similarProducts as $similarProduct)
                            <a href="{{ route('products.show', $similarProduct) }}" class="overflow-hidden rounded-2xl ring-1 ring-[#dce6f7] transition hover:-translate-y-1">
                                <img src="{{ $similarProduct->cover_image_url }}" alt="{{ $similarProduct->name }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                <p class="p-3 text-sm font-extrabold">{{ $similarProduct->name }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </section>

        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#dce6f7] bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
            <a href="#ajouter-au-panier" class="flex w-full justify-center rounded-full bg-[#2f6bff] px-6 py-3 text-sm font-extrabold text-white">{{ $product->is_rentable ? 'Louer' : 'Acheter' }} cet article</a>
        </div>

        <x-navigation.public-footer />
    </main>

    <script>
        document.querySelectorAll('[data-mode-input]').forEach((input) => input.addEventListener('change', () => {
            document.querySelector('[data-rental-fields]')?.classList.toggle('hidden', input.value !== 'rental');
        }));
        document.querySelectorAll('[data-product-thumb]').forEach((button) => button.addEventListener('click', () => {
            document.querySelector('[data-product-main-image]').src = button.dataset.productThumb;
        }));
    </script>
</x-layouts.baobaa>
