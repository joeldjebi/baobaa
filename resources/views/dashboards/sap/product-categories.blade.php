<x-dashboards.sap-shell title="Catégories d’articles" subtitle="Catégories proposées aux prestataires pour le matériel à louer ou à vendre." active="product-categories" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    @if (session('sap_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('sap_status') }}</div>
    @endif
    <x-forms.validation-errors class="mb-4" />

    <div class="grid gap-6 lg:grid-cols-[340px_1fr]">
        <form method="POST" action="{{ route('sap.product-categories.store') }}" class="h-max rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            @csrf
            <h2 class="text-xl font-extrabold text-[#07152f]">Nouvelle catégorie</h2>
            <input name="name" required value="{{ old('name') }}" placeholder="Ex : Mobilier, Sonorisation" class="mt-4 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
            <input name="sort_order" type="number" min="0" value="{{ old('sort_order', 0) }}" placeholder="Ordre d’affichage" class="mt-3 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
            <button class="mt-4 h-12 w-full rounded-2xl bg-[#2f6bff] text-sm font-extrabold text-white">Ajouter</button>
        </form>

        <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
            <div class="grid gap-2">
                @forelse ($categories as $category)
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-3 ring-1 ring-[#edf2fb]">
                        <p class="font-extrabold text-[#07152f]">{{ $category->name }} <span class="ml-2 text-xs font-bold text-[#6f7890]">{{ $category->products_count }} article(s) · {{ $category->is_active ? 'Active' : 'Inactive' }}</span></p>
                        <form method="POST" action="{{ route('sap.product-categories.toggle', $category) }}">
                            @csrf
                            <button class="rounded-full bg-white px-3 py-1 text-xs font-extrabold {{ $category->is_active ? 'text-[#b42318] ring-1 ring-[#ffd0d0]' : 'text-[#2f6bff] ring-1 ring-[#dce6f7]' }}">{{ $category->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm font-semibold text-[#64708a]">Aucune catégorie : les prestataires ne peuvent pas encore publier d’articles.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboards.sap-shell>
