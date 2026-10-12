@php
    $input = 'h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]';
    $label = 'mb-2 block text-sm font-extrabold text-[#17233f]';
@endphp

<x-dashboards.service-provider-shell :title="$product ? 'Modifier un article' : 'Ajouter un article'" subtitle="Indiquez clairement les prix, le stock et la caution : le client les voit avant de commander." active="products" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    @if (session('pse_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('pse_status') }}</div>
    @endif

    <form method="POST" action="{{ $product ? route('service-provider.products.update', $product) : route('service-provider.products.store') }}" enctype="multipart/form-data" class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
        @csrf
        @if ($product)
            @method('PATCH')
        @endif

        <x-forms.validation-errors class="mb-5" />

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="{{ $label }}">Nom de l’article</span>
                <input name="name" value="{{ old('name', $product?->name) }}" required placeholder="Ex : Chaise Napoléon dorée" class="{{ $input }}">
            </label>
            <label class="block">
                <span class="{{ $label }}">Catégorie</span>
                <select name="product_category_id" required class="{{ $input }}">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('product_category_id', $product?->product_category_id) === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @if ($categories->isEmpty())
                    <span class="mt-2 block text-xs font-bold text-[#b42318]">Aucune catégorie n’est encore ouverte par BAOBAA.</span>
                @endif
            </label>
        </div>

        <label class="mt-4 block">
            <span class="{{ $label }}">Accroche courte</span>
            <input name="short_description" value="{{ old('short_description', $product?->short_description) }}" class="{{ $input }}">
        </label>
        <label class="mt-4 block">
            <span class="{{ $label }}">Description</span>
            <textarea name="description" rows="4" class="w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 outline-none focus:border-[#2f6bff]">{{ old('description', $product?->description) }}</textarea>
        </label>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-[#dce6f7] bg-[#f8fbff] p-4">
                <label class="flex items-center gap-3 text-sm font-extrabold text-[#151821]">
                    <input type="hidden" name="is_rentable" value="0">
                    <input type="checkbox" name="is_rentable" value="1" @checked(old('is_rentable', $product?->is_rentable ?? true)) class="size-5 accent-[#2f6bff]">
                    Proposer à la location
                </label>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <label><span class="{{ $label }}">Prix par jour (XOF)</span><input type="number" min="1" name="rental_price_per_day" value="{{ old('rental_price_per_day', $product?->rental_price_per_day) }}" class="{{ $input }}"></label>
                    <label><span class="{{ $label }}">Caution par unité (XOF)</span><input type="number" min="0" name="deposit_amount" value="{{ old('deposit_amount', $product?->deposit_amount ?? 0) }}" class="{{ $input }}"></label>
                </div>
                <p class="mt-2 text-xs font-semibold text-[#6f7890]">La caution est payée par le client et vous la restituez (ou en retenez une partie) au retour du matériel.</p>
            </div>
            <div class="rounded-2xl border border-[#dce6f7] bg-[#f8fbff] p-4">
                <label class="flex items-center gap-3 text-sm font-extrabold text-[#151821]">
                    <input type="hidden" name="is_sellable" value="0">
                    <input type="checkbox" name="is_sellable" value="1" @checked(old('is_sellable', $product?->is_sellable ?? false)) class="size-5 accent-[#2f6bff]">
                    Proposer à la vente
                </label>
                <label class="mt-3 block"><span class="{{ $label }}">Prix de vente unitaire (XOF)</span><input type="number" min="1" name="sale_price" value="{{ old('sale_price', $product?->sale_price) }}" class="{{ $input }}"></label>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <label><span class="{{ $label }}">Quantité en stock</span><input type="number" min="0" name="stock_quantity" required value="{{ old('stock_quantity', $product?->stock_quantity ?? 1) }}" class="{{ $input }}"></label>
            <label><span class="{{ $label }}">Ville de l’article</span><input name="city" required value="{{ old('city', $product?->city ?? $profile->city) }}" class="{{ $input }}"></label>
            <label>
                <span class="{{ $label }}">Statut</span>
                <select name="status" class="{{ $input }}">
                    <option value="draft" @selected(old('status', $product?->status?->value ?? 'draft') === 'draft')>Brouillon</option>
                    <option value="published" @selected(old('status', $product?->status?->value) === 'published')>Publié</option>
                </select>
            </label>
        </div>

        <label class="mt-4 block cursor-pointer rounded-2xl border border-dashed border-[#b9caff] bg-[#f8fbff] p-4 transition hover:border-[#2f6bff]">
            <span class="block text-sm font-extrabold text-[#17233f]">Photos de l’article</span>
            <span class="mt-1 block text-xs font-bold leading-5 text-[#6f7890]">Jusqu’à 8 photos (JPG, PNG ou WebP, 8 Mo maximum chacune).</span>
            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-3 block w-full text-sm font-bold text-[#52617b] file:mr-3 file:rounded-full file:border-0 file:bg-[#2f6bff] file:px-4 file:py-2 file:text-sm file:font-extrabold file:text-white">
        </label>

        <div class="mt-5 flex flex-wrap gap-2">
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/24">Enregistrer</button>
            <a href="{{ route('service-provider.products') }}" class="rounded-2xl border border-[#c9d8ef] px-5 py-3 text-sm font-extrabold text-[#2f6bff]">Retour</a>
        </div>
    </form>

    @if ($product && $product->media->isNotEmpty())
        <section class="mt-5 rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <h2 class="text-lg font-extrabold text-[#07152f]">Photos publiées</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($product->media as $media)
                    <div class="overflow-hidden rounded-2xl ring-1 ring-[#dce6f7]">
                        <img src="{{ $media->url }}" alt="{{ $media->alt_text ?? $product->name }}" referrerpolicy="no-referrer" loading="lazy" class="aspect-[4/3] w-full object-cover">
                        <div class="flex items-center justify-between gap-2 p-2">
                            <span class="text-[11px] font-extrabold text-[#2f6bff]">{{ $media->is_primary ? 'Principale' : 'Photo' }}</span>
                            <form method="POST" action="{{ route('service-provider.products.media.destroy', [$product, $media]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full bg-[#fff6f6] px-3 py-1 text-xs font-extrabold text-[#b42318] ring-1 ring-[#ffd0d0]">Retirer</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-dashboards.service-provider-shell>
