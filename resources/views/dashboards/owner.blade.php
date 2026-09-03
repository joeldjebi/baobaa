@php
    $maxRevenue = max(1, collect($monthlyRevenue)->max('amount') ?? 1);
    $bookingStatusLabels = [
        'draft' => 'Brouillon',
        'pending_owner' => 'À valider',
        'pending_payment' => 'Paiement attendu',
        'confirmed' => 'Confirmée',
        'declined' => 'Refusée',
        'cancelled' => 'Annulée',
        'completed' => 'Terminée',
        'disputed' => 'En litige',
    ];
@endphp

<x-dashboards.owner-shell
    title="Tableau de bord"
    subtitle="Pilotez vos espaces, vos réservations et vos revenus depuis un espace professionnel."
    active="overview"
    :owner-profile="$ownerProfile"
    :active-venues-count="$activeVenuesCount"
    :pending-bookings-count="$pendingBookingsCount"
    :confirmed-bookings-count="$confirmedBookingsCount"
    :gross-revenue="$grossRevenue"
    :active-subscription="$activeSubscription"
    :active-deposit-rule="$activeDepositRule"
    :billing-preference-label="$billingPreferenceLabel"
>
    <div class="grid gap-6 xl:grid-cols-[1.25fr_.75fr]">
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Performance</p>
                    <h2 class="mt-1 text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Revenus confirmés sur 6 mois</h2>
                </div>
                <a href="{{ route('owner.payments') }}" class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Voir les paiements</a>
            </div>

            <div class="mt-6 flex h-72 items-end gap-3 rounded-[22px] bg-[#f7faff] px-4 pb-4 pt-8">
                @foreach ($monthlyRevenue as $month)
                    <div class="flex h-full flex-1 flex-col justify-end gap-2">
                        <div class="relative flex flex-1 items-end justify-center">
                            <span class="w-full max-w-12 rounded-t-2xl bg-gradient-to-t from-[#2f6bff] to-[#8dc1ff] shadow-lg shadow-[#2f6bff]/15" style="height: {{ max(8, ($month['amount'] / $maxRevenue) * 100) }}%"></span>
                        </div>
                        <p class="text-center text-xs font-extrabold text-[#6f7890]">{{ $month['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[26px] border border-white/80 bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/12">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Actions rapides</p>
            <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.04em]">Accélérez vos ventes.</h2>
            <div class="mt-5 grid gap-3">
                <a href="{{ route('owner.venues.create') }}" class="rounded-2xl bg-white px-4 py-3 text-sm font-extrabold text-[#07152f] transition hover:-translate-y-0.5">Ajouter un espace</a>
                <a href="{{ route('owner.calendar') }}" class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-extrabold transition hover:bg-white/15">Gérer les disponibilités</a>
                <a href="{{ route('owner.addons') }}" class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-extrabold transition hover:bg-white/15">Configurer les modules</a>
                <a href="{{ route('owner.settings') }}" class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-extrabold transition hover:bg-white/15">Mettre à jour le profil</a>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[.95fr_1.05fr]">
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Espaces les plus demandés</h2>
            <div class="mt-4 grid gap-3">
                @forelse ($topVenues as $venue)
                    <a href="{{ route('venues.show', $venue->slug) }}" class="flex items-center justify-between gap-4 rounded-2xl bg-[#f7faff] p-4 transition hover:bg-[#eef4ff]">
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-extrabold text-[#151821]">{{ $venue->name }}</span>
                            <span class="mt-1 block text-xs font-bold text-[#6f7890]">{{ $venue->city }} · {{ $venue->bookings_count }} réservation{{ $venue->bookings_count > 1 ? 's' : '' }}</span>
                        </span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ number_format($venue->starting_price, 0, ',', ' ') }} XOF</span>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold text-[#6f7890]">Ajoutez votre premier espace pour suivre sa performance.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Dernières réservations</h2>
                <a href="{{ route('owner.bookings') }}" class="text-xs font-extrabold text-[#2f6bff]">Tout voir</a>
            </div>
            <div class="mt-4 overflow-hidden rounded-2xl border border-[#edf2fb]">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                        <tr>
                            <th class="px-4 py-3">Référence</th>
                            <th class="px-4 py-3">Espace</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf2fb]">
                        @forelse ($recentBookings as $booking)
                            <tr>
                                <td class="px-4 py-3 font-extrabold text-[#151821]">{{ $booking->reference }}</td>
                                <td class="px-4 py-3 font-semibold text-[#52617b]">{{ $booking->venue?->name }}</td>
                                <td class="px-4 py-3 font-semibold text-[#52617b]">{{ $booking->event_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center font-semibold text-[#6f7890]">Aucune réservation pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-dashboards.owner-shell>
