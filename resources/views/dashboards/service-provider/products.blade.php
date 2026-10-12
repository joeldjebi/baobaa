@php($statusLabels = ['draft' => 'Brouillon', 'published' => 'Publié', 'suspended' => 'Suspendu par BAOBAA'])

<x-dashboards.service-provider-shell title="Mes articles" subtitle="Matériel à louer ou à vendre : prix, stock, caution et photos." active="products" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    @if (session('pse_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('pse_status') }}</div>
    @endif

    <section class="rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="grid flex-1 gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[minmax(0,1fr)_190px_auto]">
                <input name="q" value="{{ request('q') }}" placeholder="Nom de l’article" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <option value="">Tous les statuts</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
            </form>
            <a href="{{ route('service-provider.products.create') }}" class="rounded-2xl bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white">Ajouter un article</a>
        </div>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Article</th><th class="px-4 py-3">Location / jour</th><th class="px-4 py-3">Vente</th><th class="px-4 py-3">Caution</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($products as $product)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->cover_image_url }}" alt="" class="size-12 rounded-xl object-cover ring-1 ring-[#dce6f7]">
                                    <div>
                                        <p class="font-extrabold text-[#151821]">{{ $product->name }}</p>
                                        <p class="text-xs font-bold text-[#6f7890]">{{ $product->category?->name }} · {{ $product->city }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-bold text-[#07152f]">{{ $product->is_rentable ? number_format($product->rental_price_per_day, 0, ',', ' ').' '.$product->currency : '—' }}</td>
                            <td class="px-4 py-4 font-bold text-[#07152f]">{{ $product->is_sellable ? number_format($product->sale_price, 0, ',', ' ').' '.$product->currency : '—' }}</td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $product->deposit_amount ? number_format($product->deposit_amount, 0, ',', ' ').' '.$product->currency : '—' }}</td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $product->stock_quantity }} <span class="text-xs text-[#8a94aa]">({{ $product->available_for_sale }} libre{{ $product->available_for_sale > 1 ? 's' : '' }} aujourd’hui)</span></td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$product->status->value] ?? $product->status->value }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('service-provider.products.edit', $product) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Modifier</a>
                                    @unless ($product->status->value === 'suspended')
                                        <form method="POST" action="{{ route('service-provider.products.toggle', $product) }}">
                                            @csrf
                                            <button class="rounded-full border border-[#c9d8ef] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $product->status->value === 'published' ? 'Retirer' : 'Publier' }}</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucun article. Ajoutez votre premier article à louer ou à vendre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $products->links() }}</div>
    </section>
</x-dashboards.service-provider-shell>
