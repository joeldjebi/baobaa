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
    $paymentStatusLabels = [
        'initiated' => 'Initialisé',
        'pending' => 'En attente',
        'succeeded' => 'Réussi',
        'failed' => 'Échoué',
        'refunded' => 'Remboursé',
        'partially_refunded' => 'Partiellement remboursé',
    ];
@endphp

<x-dashboards.owner-shell title="Détail réservation" subtitle="Consultez le dossier client, le créneau, le paiement et les prochaines actions." active="bookings" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="min-w-0 space-y-5">
            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <a href="{{ route('owner.bookings') }}" class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">Réservations</a>
                        <h2 class="mt-2 break-words text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $booking->reference }}</h2>
                        <p class="mt-1 break-words text-sm font-semibold text-[#6f7890]">{{ $booking->venue?->name }} · {{ $booking->event_date?->format('d/m/Y') }}</p>
                    </div>
                    <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-4">
                    @foreach ([
                        ['label' => 'Invités', 'value' => number_format($booking->guests_count, 0, ',', ' ')],
                        ['label' => 'Créneau', 'value' => substr($booking->starts_at, 0, 5).' - '.substr($booking->ends_at, 0, 5)],
                        ['label' => 'Total', 'value' => number_format($booking->total_amount, 0, ',', ' ').' '.$booking->currency],
                        ['label' => 'Réservation', 'value' => number_format($booking->reservation_amount, 0, ',', ' ').' '.$booking->currency],
                    ] as $item)
                        <div class="min-w-0 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <p class="text-[11px] font-extrabold uppercase text-[#7d8aa7]">{{ $item['label'] }}</p>
                            <p class="mt-2 break-words text-sm font-extrabold text-[#07152f]">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($booking->client_notes)
                    <div class="mt-5 rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4">
                        <p class="text-xs font-extrabold uppercase text-[#7d8aa7]">Note client</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#52617b]">{{ $booking->client_notes }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h3 class="text-lg font-extrabold text-[#07152f]">Paiements liés</h3>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-[#edf2fb]">
                    <table class="w-full min-w-[680px] text-left text-sm">
                        <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                            <tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Méthode</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Date</th></tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf2fb]">
                            @forelse ($booking->payments as $payment)
                                <tr>
                                    <td class="px-4 py-4 font-extrabold text-[#151821]">{{ $payment->reference }}</td>
                                    <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->payment_method ?? $payment->provider }}</td>
                                    <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                                    <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $paymentStatusLabels[$payment->status->value] ?? 'À suivre' }}</span></td>
                                    <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center font-semibold text-[#6f7890]">Aucun paiement enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <aside class="min-w-0 space-y-4">
            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h3 class="text-sm font-extrabold text-[#07152f]">Client</h3>
                <p class="mt-3 break-words text-lg font-extrabold text-[#151821]">{{ $booking->client?->name }}</p>
                <p class="mt-1 break-words text-sm font-semibold text-[#6f7890]">{{ $booking->client?->email }}</p>
                <p class="mt-1 break-words text-sm font-semibold text-[#6f7890]">{{ $booking->client?->phone ?? 'Téléphone non renseigné' }}</p>
            </div>

            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h3 class="text-sm font-extrabold text-[#07152f]">Actions</h3>
                <div class="mt-4 grid gap-2">
                    @if (in_array($booking->status->value, ['pending_owner', 'pending_payment'], true))
                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                            @csrf
                            <input type="hidden" name="action" value="confirm">
                            <button class="w-full rounded-2xl bg-[#2f6bff] px-4 py-3 text-sm font-extrabold text-white">Confirmer la réservation</button>
                        </form>
                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                            @csrf
                            <input type="hidden" name="action" value="decline">
                            <button class="w-full rounded-2xl bg-[#fff6f6] px-4 py-3 text-sm font-extrabold text-[#b42318]">Refuser la demande</button>
                        </form>
                    @endif
                    @if ($booking->status->value === 'confirmed')
                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                            @csrf
                            <input type="hidden" name="action" value="complete">
                            <button class="w-full rounded-2xl bg-[#eaf7ef] px-4 py-3 text-sm font-extrabold text-[#128043]">Marquer comme terminée</button>
                        </form>
                        <form method="POST" action="{{ route('owner.bookings.status', $booking) }}">
                            @csrf
                            <input type="hidden" name="action" value="cancel">
                            <button class="w-full rounded-2xl border border-[#ffd0d0] px-4 py-3 text-sm font-extrabold text-[#b42318]">Annuler</button>
                        </form>
                    @endif
                    <a href="{{ route('venues.show', $booking->venue?->slug) }}" class="w-full rounded-2xl border border-[#c9d8ef] px-4 py-3 text-center text-sm font-extrabold text-[#2f6bff]">Voir l’espace public</a>
                </div>
            </div>
        </aside>
    </div>
</x-dashboards.owner-shell>
