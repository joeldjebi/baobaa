@php($labels = ['initiated' => 'Initialisé', 'pending' => 'En attente', 'succeeded' => 'Réussi', 'failed' => 'Échoué', 'refunded' => 'Remboursé', 'partially_refunded' => 'Partiellement remboursé'])
<x-dashboards.sap-shell title="Paiements" subtitle="Historique complet des transactions et paiements confirmés." active="payments" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_180px_auto]"><input name="q" value="{{ request('q') }}" placeholder="Référence, prestataire, client" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><option value="">Tous les statuts</option>@foreach($labels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select><button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button></form>
        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[1080px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Paiement</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Espace</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Canal</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-extrabold text-[#07152f]">{{ $payment->reference }}</p>
                                <p class="text-xs font-bold text-[#64708a]">{{ $payment->paid_at?->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $payment->payer?->name }}</td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $payment->booking?->venue?->name }}</td>
                            <td class="px-4 py-4 font-extrabold">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                            <td class="px-4 py-4 font-bold">{{ $payment->provider }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $labels[$payment->status->value] ?? $payment->status->value }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <x-dashboards.action-menu>
                                    <a href="{{ $payment->booking ? route('sap.bookings', ['q' => $payment->booking->reference]) : route('sap.payments') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir la réservation</a>

                                    @foreach (['succeeded' => 'Marquer comme réussi', 'failed' => 'Marquer comme échoué', 'refunded' => 'Marquer remboursé'] as $status => $label)
                                        @if ($payment->status->value !== $status)
                                            <form method="POST" action="{{ route('sap.payments.status', $payment) }}" class="border-t border-[#edf2fb] pt-1">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $status }}">
                                                <button class="w-full rounded-xl px-3 py-2 text-left {{ in_array($status, ['failed', 'refunded'], true) ? 'text-[#b42318] hover:bg-[#fff6f6]' : 'text-[#2f6bff] hover:bg-[#f2f7ff]' }}">{{ $label }}</button>
                                            </form>
                                        @endif
                                    @endforeach
                                </x-dashboards.action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucun paiement trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $payments->links() }}</div>
    </section>
</x-dashboards.sap-shell>
