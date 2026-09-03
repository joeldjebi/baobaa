@php
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

<x-dashboards.owner-shell title="Réservations" subtitle="Suivez les demandes clients, les créneaux et les statuts à traiter." active="bookings" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    @if (session('booking_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('booking_status') }}</div>
    @endif

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <div class="mb-5 grid gap-3 md:grid-cols-4">
            @foreach ([
                ['label' => 'À valider', 'status' => 'pending_owner'],
                ['label' => 'Paiement attendu', 'status' => 'pending_payment'],
                ['label' => 'Confirmées', 'status' => 'confirmed'],
                ['label' => 'Terminées', 'status' => 'completed'],
            ] as $item)
                <a href="{{ route('owner.bookings', ['status' => $item['status']]) }}" class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7] transition hover:bg-[#eef4ff]">
                    <p class="text-sm font-extrabold text-[#07152f]">{{ $item['label'] }}</p>
                    <p class="mt-1 text-xs font-bold text-[#6f7890]">Voir les demandes</p>
                </a>
            @endforeach
        </div>

        <form method="GET" class="grid gap-3 md:grid-cols-[1fr_190px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence, client ou espace" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach (['pending_payment' => 'Paiement attendu', 'pending_owner' => 'À valider', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Espace</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Invités</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($bookings as $booking)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4 font-extrabold text-[#151821]">{{ $booking->reference }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->client?->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->venue?->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->event_date?->format('d/m/Y') }} · {{ substr($booking->starts_at, 0, 5) }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->guests_count }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($booking->reservation_amount, 0, ',', ' ') }} {{ $booking->currency }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('owner.bookings.show', $booking) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Détails</a>
                                    @if (in_array($booking->status->value, ['pending_owner', 'pending_payment'], true))
                                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="confirm">
                                            <button class="rounded-full bg-[#eaf7ef] px-3 py-1.5 text-xs font-extrabold text-[#128043]">Confirmer</button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="decline">
                                            <button class="rounded-full bg-[#fff6f6] px-3 py-1.5 text-xs font-extrabold text-[#b42318]">Refuser</button>
                                        </form>
                                    @endif
                                    @if ($booking->status->value === 'confirmed')
                                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="complete">
                                            <button class="rounded-full bg-[#eef4ff] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">Terminer</button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="cancel">
                                            <button class="rounded-full border border-[#ffd0d0] px-3 py-1.5 text-xs font-extrabold text-[#b42318]">Annuler</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucune réservation trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $bookings->links() }}</div>
    </section>
</x-dashboards.owner-shell>
