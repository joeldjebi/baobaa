@php
    $bookingStatusLabels = [
        'draft' => 'Brouillon',
        'pending_owner' => 'Validation propriétaire',
        'pending_payment' => 'Paiement attendu',
        'confirmed' => 'Confirmée',
        'declined' => 'Refusée',
        'cancelled' => 'Annulée',
        'completed' => 'Terminée',
        'disputed' => 'En litige',
    ];
@endphp

<x-dashboards.client-shell title="Historique des réservations" subtitle="Retrouvez toutes vos demandes, confirmations, annulations et événements terminés." active="reservations" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <section class="rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        <form method="GET" class="grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-2 xl:grid-cols-[1fr_180px_160px_160px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence ou nom de l’espace" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach (['pending_owner' => 'Validation propriétaire', 'pending_payment' => 'Paiement attendu', 'confirmed' => 'Confirmée', 'completed' => 'Terminée', 'cancelled' => 'Annulée'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="mt-5 grid gap-3 lg:hidden">
            @forelse ($bookings as $booking)
                <article class="rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-extrabold text-[#151821]">{{ $booking->venue?->name }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $booking->reference }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-[#eef4ff] px-3 py-1 text-[11px] font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span>
                    </div>
                    <div class="mt-4 grid gap-2 text-sm font-semibold text-[#52617b]">
                        <p>{{ $booking->event_date?->format('d/m/Y') }} · {{ substr($booking->starts_at, 0, 5) }} - {{ substr($booking->ends_at, 0, 5) }}</p>
                        <p>{{ number_format($booking->guests_count, 0, ',', ' ') }} invités · acompte {{ number_format($booking->reservation_amount, 0, ',', ' ') }} {{ $booking->currency }}</p>
                    </div>
                    <a href="{{ route('client.reservations.show', $booking) }}" class="mt-4 inline-flex rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Ouvrir le dossier</a>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-center text-sm font-semibold text-[#6f7890]">Aucune réservation trouvée.</p>
            @endforelse
        </div>

        <div class="mt-5 hidden overflow-x-auto rounded-2xl border border-[#edf2fb] lg:block">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Espace</th><th class="px-4 py-3">Partenaire</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Invités</th><th class="px-4 py-3">Acompte</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($bookings as $booking)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4 font-extrabold text-[#151821]">{{ $booking->reference }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->venue?->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->venue?->ownerProfile?->business_name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $booking->event_date?->format('d/m/Y') }} · {{ substr($booking->starts_at, 0, 5) }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ number_format($booking->guests_count, 0, ',', ' ') }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($booking->reservation_amount, 0, ',', ' ') }} {{ $booking->currency }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span></td>
                            <td class="px-4 py-4 text-right"><a href="{{ route('client.reservations.show', $booking) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucune réservation trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $bookings->links() }}</div>
    </section>
</x-dashboards.client-shell>
