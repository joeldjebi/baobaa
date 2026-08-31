@php($labels = ['draft' => 'Brouillon', 'pending_review' => 'En validation', 'published' => 'Publié', 'rejected' => 'Refusé', 'suspended' => 'Suspendu', 'archived' => 'Archivé'])
<x-dashboards.sap-shell title="Espaces" subtitle="Supervisez le catalogue public, les statuts et la qualité des fiches." active="venues" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_190px_auto]"><input name="q" value="{{ request('q') }}" placeholder="Rechercher un espace" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><option value="">Tous les statuts</option>@foreach($labels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select><button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button></form>
        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]"><table class="w-full min-w-[1080px] text-left text-sm"><thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]"><tr><th class="px-4 py-3">Espace</th><th class="px-4 py-3">Partenaire</th><th class="px-4 py-3">Catégorie</th><th class="px-4 py-3">Prix</th><th class="px-4 py-3">Réservations</th><th class="px-4 py-3">Sponsorings</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-[#edf2fb]">
            @forelse($venues as $venue)
                <tr>
                    <td class="px-4 py-4">
                        <a href="{{ route('venues.show', $venue->slug) }}" class="font-extrabold text-[#07152f] hover:text-[#2f6bff]">{{ $venue->name }}</a>
                        <p class="text-xs font-bold text-[#64708a]">{{ $venue->city }} · {{ $venue->district }}</p>
                    </td>
                    <td class="px-4 py-4 font-bold text-[#64708a]">{{ $venue->ownerProfile?->business_name }}</td>
                    <td class="px-4 py-4 font-bold text-[#64708a]">{{ $venue->category?->name }}</td>
                    <td class="px-4 py-4 font-extrabold">{{ number_format($venue->starting_price, 0, ',', ' ') }} {{ $venue->currency }}</td>
                    <td class="px-4 py-4 font-bold">{{ $venue->bookings_count }}</td>
                    <td class="px-4 py-4 font-bold">{{ $venue->sponsorship_campaigns_count }}</td>
                    <td class="px-4 py-4">
                        <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $labels[$venue->status->value] ?? $venue->status->value }}</span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <x-dashboards.action-menu>
                            <a href="{{ route('venues.show', $venue->slug) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir côté public</a>
                            <a href="{{ route('sap.bookings', ['q' => $venue->name]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir les réservations</a>

                            @foreach (['published' => 'Publier l’espace', 'suspended' => 'Suspendre l’espace', 'rejected' => 'Refuser la fiche'] as $status => $label)
                                @if ($venue->status->value !== $status)
                                    <form method="POST" action="{{ route('sap.venues.status', $venue) }}" class="border-t border-[#edf2fb] pt-1">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="w-full rounded-xl px-3 py-2 text-left {{ $status === 'rejected' || $status === 'suspended' ? 'text-[#b42318] hover:bg-[#fff6f6]' : 'text-[#2f6bff] hover:bg-[#f2f7ff]' }}">{{ $label }}</button>
                                    </form>
                                @endif
                            @endforeach
                        </x-dashboards.action-menu>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucun espace trouvé.</td></tr>
            @endforelse
        </tbody></table></div><div class="mt-5">{{ $venues->links() }}</div>
    </section>
</x-dashboards.sap-shell>
