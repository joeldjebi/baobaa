@php
    $paymentStatusLabels = ['initiated' => 'Initialisé', 'pending' => 'En attente', 'succeeded' => 'Réussi', 'failed' => 'Échoué', 'refunded' => 'Remboursé', 'partially_refunded' => 'Partiellement remboursé'];
    $subscriptionStatusLabels = ['active' => 'Actif', 'pending_payment' => 'Paiement attendu', 'expired' => 'Expiré', 'suspended' => 'Suspendu', 'cancelled' => 'Annulé'];
    $payoutStatusLabels = ['pending' => 'en attente', 'scheduled' => 'programmé', 'paid' => 'payé', 'failed' => 'échoué'];
@endphp

<x-dashboards.owner-shell title="Paiements" subtitle="Contrôlez les encaissements, méthodes de paiement et références prestataires." active="payments" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :billing-preference-label="$billingPreferenceLabel">
    @if (session('payout_status'))
        <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('payout_status') }}</div>
    @endif
    @if ($errors->has('payout'))
        <div class="mb-4 rounded-2xl border border-[#ffd0d0] bg-[#fff6f6] px-4 py-3 text-sm font-extrabold text-[#b42318]">{{ $errors->first('payout') }}</div>
    @endif

    <section class="mb-6 rounded-[26px] border border-white/80 bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/12">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Reversements disponibles</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.04em]">Demandez votre argent 48h après l’événement.</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-white/70">BAOBAA affiche ici les réservations terminées, payées et prêtes à être reversées.</p>
            </div>
            <a href="{{ route('owner.settings') }}" class="rounded-full bg-white px-4 py-2 text-xs font-extrabold text-[#07152f]">Configurer mon compte</a>
        </div>
        <div class="mt-5 grid gap-3">
            @forelse ($payoutEligibleBookings as $booking)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/10 p-4">
                    <div>
                        <p class="text-sm font-extrabold">{{ $booking->reference }} · {{ $booking->venue?->name }}</p>
                        <p class="mt-1 text-xs font-bold text-white/60">{{ $booking->event_date?->format('d/m/Y') }} · {{ number_format($booking->payments->sum('amount'), 0, ',', ' ') }} {{ $booking->currency }}</p>
                    </div>
                    <form method="POST" action="{{ route('owner.payouts.store', $booking) }}">
                        @csrf
                        <button class="rounded-full bg-white px-4 py-2 text-xs font-extrabold text-[#2f6bff]">Demander le reversement</button>
                    </form>
                </div>
            @empty
                <p class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm font-semibold text-white/70">Aucun reversement disponible pour le moment. Les demandes apparaissent 48h après une réservation terminée et payée.</p>
            @endforelse
        </div>
    </section>

    <section class="mb-6 grid gap-4 xl:grid-cols-2">
        <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Historique des paiements à BAOBAA</h2>
            <p class="mt-1 text-sm font-semibold text-[#6f7890]">Abonnements payés ou à renouveler.</p>
            <div class="mt-4 space-y-3">
                @forelse ($baobaaHistory as $subscription)
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <div>
                            <p class="text-sm font-extrabold text-[#151821]">{{ $subscription->subscriptionPlan?->name ?? 'Formule BAOBAA' }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $subscription->starts_on?->format('d/m/Y') }} - {{ $subscription->ends_on?->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-extrabold text-[#07152f]">{{ number_format($subscription->amount, 0, ',', ' ') }} {{ $subscription->currency }}</p>
                            <p class="mt-1 text-xs font-bold text-[#2f6bff]">{{ $subscriptionStatusLabels[$subscription->status->value] ?? 'À suivre' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-4 text-sm font-semibold text-[#6f7890]">Aucun paiement d’abonnement enregistré.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Commissions BAOBAA</h2>
            <p class="mt-1 text-sm font-semibold text-[#6f7890]">Montants retenus sur les reversements finalisés ou programmés.</p>
            <div class="mt-4 space-y-3">
                @forelse ($commissionHistory as $payout)
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                        <div>
                            <p class="text-sm font-extrabold text-[#151821]">{{ $payout->reference }}</p>
                            <p class="mt-1 text-xs font-bold text-[#6f7890]">Reversement {{ $payoutStatusLabels[$payout->status] ?? 'à suivre' }}</p>
                        </div>
                        <p class="text-sm font-extrabold text-[#07152f]">{{ number_format($payout->commission_amount, 0, ',', ' ') }} {{ $payout->currency }}</p>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-4 text-sm font-semibold text-[#6f7890]">Aucune commission enregistrée.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 flex flex-wrap gap-3">
            <select name="status" class="min-w-52 rounded-2xl border border-[#dce6f7] bg-[#f7faff] px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les paiements</option>
                @foreach (['initiated' => 'Initialisé', 'pending' => 'En attente', 'succeeded' => 'Réussi', 'failed' => 'Échoué', 'refunded' => 'Remboursé'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Réservation</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Méthode</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Statut</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($payments as $payment)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4 font-extrabold text-[#151821]">{{ $payment->reference }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->booking?->reference }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->payer?->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $payment->payment_method }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $paymentStatusLabels[$payment->status->value] ?? 'À suivre' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucun paiement trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $payments->links() }}</div>
    </section>
</x-dashboards.owner-shell>
