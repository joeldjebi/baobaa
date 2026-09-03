@php
    $venueStatusLabels = [
        'draft' => 'Brouillon',
        'pending_review' => 'En validation',
        'published' => 'Actif',
        'rejected' => 'À corriger',
        'suspended' => 'Désactivé',
        'archived' => 'Archivé',
    ];
@endphp

<x-dashboards.owner-shell title="Mes espaces" subtitle="Gérez votre catalogue public, vos prix et vos statuts de publication." active="venues" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    @if (session('venue_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('venue_status') }}</div>
    @endif

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="grid flex-1 gap-3 md:grid-cols-[1fr_180px_auto]">
                <input name="q" value="{{ request('q') }}" placeholder="Rechercher un espace" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <select name="status" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                    <option value="">Tous les statuts</option>
                    @foreach (['draft' => 'Brouillon', 'pending_review' => 'En validation', 'published' => 'Publié', 'suspended' => 'Suspendu', 'archived' => 'Archivé'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
            </form>
            <a href="{{ route('owner.venues.create') }}" class="rounded-2xl bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#07152f]/12">Ajouter un espace</a>
        </div>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[1080px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr>
                        <th class="px-4 py-3">Espace</th>
                        <th class="px-4 py-3">Catégorie</th>
                        <th class="px-4 py-3">Capacité</th>
                        <th class="px-4 py-3">Prix</th>
                        <th class="px-4 py-3">Médias</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Créé</th>
                        <th class="px-4 py-3">Modifié</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($venues as $venue)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4"><a href="{{ route('venues.show', $venue->slug) }}" class="font-extrabold text-[#151821] hover:text-[#2f6bff]">{{ $venue->name }}</a><p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $venue->city }} · {{ $venue->district }}</p></td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $venue->category?->name ?? 'Non classé' }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $venue->min_capacity }}-{{ $venue->max_capacity }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($venue->starting_price, 0, ',', ' ') }} {{ $venue->currency }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $venue->media_count }} image{{ $venue->media_count > 1 ? 's' : '' }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $venueStatusLabels[$venue->status->value] ?? 'À suivre' }}</span></td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $venue->created_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $venue->updated_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-4">
                                <details class="relative ml-auto w-max">
                                    <summary class="list-none rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-[#2f6bff] [&::-webkit-details-marker]:hidden">Actions</summary>
                                    <div class="absolute right-0 z-30 mt-2 w-48 overflow-hidden rounded-2xl border border-[#dce6f7] bg-white p-2 text-xs font-extrabold shadow-2xl shadow-[#173e7a]/15">
                                        <a href="{{ route('owner.venues.edit', $venue) }}" class="block rounded-xl px-3 py-2 text-[#07152f] hover:bg-[#f2f7ff]">Modifier la fiche</a>
                                        <a href="{{ route('venues.show', $venue->slug) }}" class="block rounded-xl px-3 py-2 text-[#2f6bff] hover:bg-[#f2f7ff]">Voir côté public</a>
                                        @if ($venue->status === \App\Enums\VenueStatus::Published)
                                            <a href="{{ route('owner.sponsorships', ['venue_id' => $venue->id]) }}" class="block rounded-xl px-3 py-2 text-[#2f6bff] hover:bg-[#f2f7ff]">Sponsoriser</a>
                                        @endif
                                        <form method="POST" action="{{ route('owner.venues.status', $venue) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="{{ $venue->status === \App\Enums\VenueStatus::Published ? 'disable' : 'activate' }}">
                                            <button class="w-full rounded-xl px-3 py-2 text-left text-[#2f6bff] hover:bg-[#f2f7ff]">{{ $venue->status === \App\Enums\VenueStatus::Published ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.venues.status', $venue) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="archive">
                                            <button class="w-full rounded-xl px-3 py-2 text-left text-[#b42318] hover:bg-[#fff6f6]">Archiver</button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucun espace trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $venues->links() }}</div>
    </section>
</x-dashboards.owner-shell>
