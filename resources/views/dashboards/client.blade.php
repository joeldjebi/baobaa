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
    $paymentStatusLabels = [
        'initiated' => 'Initialisé',
        'pending' => 'En attente',
        'succeeded' => 'Réussi',
        'failed' => 'Échoué',
        'refunded' => 'Remboursé',
        'partially_refunded' => 'Partiellement remboursé',
    ];
@endphp

<x-dashboards.client-shell title="Vue d’ensemble" subtitle="Votre espace pour suivre les réservations, paiements et événements à venir." active="overview" :client="$client" :upcoming-bookings-count="$upcomingBookingsCount" :confirmed-payments-amount="$confirmedPaymentsAmount" :reserved-venues-count="$reservedVenuesCount" :pending-payments-count="$pendingPaymentsCount">
    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Prochains événements</h2>
                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Les réservations actives les plus récentes.</p>
                </div>
                <a href="{{ route('client.projects') }}" class="rounded-full bg-[#eef4ff] px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Voir mes événements</a>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse ($upcomingBookings as $booking)
                    <article class="grid gap-4 rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-base font-extrabold text-[#151821]">{{ $booking->venue?->name }}</p>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $booking->reference }} · {{ $booking->event_date?->format('d/m/Y') }} · {{ substr($booking->starts_at, 0, 5) }} - {{ substr($booking->ends_at, 0, 5) }}</p>
                            <p class="mt-1 text-xs font-bold text-[#8a94aa]">{{ $booking->venue?->city }} · {{ $booking->venue?->district }} · {{ number_format($booking->guests_count, 0, ',', ' ') }} invités</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 md:justify-end">
                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $bookingStatusLabels[$booking->status->value] ?? 'À suivre' }}</span>
                            <a href="{{ route('client.reservations.show', $booking) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Ouvrir</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-8 text-center">
                        <p class="text-base font-extrabold text-[#07152f]">Aucune réservation à venir.</p>
                        <p class="mt-2 text-sm font-semibold text-[#6f7890]">Explorez les espaces disponibles et sécurisez votre prochain événement.</p>
                        <a href="{{ route('venues.index') }}" class="mt-4 inline-flex rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Trouver un espace</a>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="space-y-5">
            <div class="rounded-[26px] border border-white/80 bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/15">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Action rapide</p>
                <h2 class="mt-3 text-xl font-extrabold tracking-[-0.03em]">Réservez avec un acompte sécurisé.</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-white/70">Comparez les espaces, préparez vos demandes et gardez chaque projet événementiel au même endroit.</p>
                <a href="{{ route('venues.index') }}" class="mt-4 inline-flex rounded-2xl bg-white px-4 py-2.5 text-sm font-extrabold text-[#07152f]">Explorer les espaces</a>
            </div>

            <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
                <h2 class="text-lg font-extrabold text-[#07152f]">Paiements récents</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($recentPayments as $payment)
                        <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                            <p class="text-sm font-extrabold text-[#151821]">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $payment->booking?->venue?->name }} · {{ $paymentStatusLabels[$payment->status->value] ?? 'À suivre' }}</p>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-4 text-sm font-semibold text-[#6f7890]">Aucun paiement récent.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</x-dashboards.client-shell>
