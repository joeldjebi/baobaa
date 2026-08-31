@php($labels = ['draft' => 'Brouillon', 'pending_owner' => 'Validation PEE', 'pending_payment' => 'Paiement attendu', 'confirmed' => 'Confirmée', 'declined' => 'Refusée', 'cancelled' => 'Annulée', 'completed' => 'Terminée', 'disputed' => 'Litige'])
<x-dashboards.sap-shell title="Réservations" subtitle="Suivi transverse des demandes, clients, propriétaires et montants." active="bookings" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_200px_auto]"><input name="q" value="{{ request('q') }}" placeholder="Référence, espace, client" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><option value="">Tous les statuts</option>@foreach($labels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select><button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button></form>
        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[1080px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Référence</th><th class="px-4 py-3">Espace</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Montant</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ $booking->reference }}</td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $booking->venue?->name }}</td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $booking->client?->name }}</td>
                            <td class="px-4 py-4 font-bold">{{ $booking->event_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-4 font-extrabold">{{ number_format($booking->total_amount, 0, ',', ' ') }} {{ $booking->currency }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $labels[$booking->status->value] ?? $booking->status->value }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <x-dashboards.action-menu>
                                    <a href="{{ $booking->venue ? route('venues.show', $booking->venue->slug) : route('sap.bookings') }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir l’espace</a>
                                    <a href="{{ route('sap.payments', ['q' => $booking->reference]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir les paiements</a>

                                    @foreach (['confirmed' => 'Confirmer la réservation', 'completed' => 'Marquer terminée', 'disputed' => 'Ouvrir un litige', 'cancelled' => 'Annuler la réservation'] as $status => $label)
                                        @if ($booking->status->value !== $status)
                                            <form method="POST" action="{{ route('sap.bookings.status', $booking) }}" class="border-t border-[#edf2fb] pt-1">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $status }}">
                                                <button class="w-full rounded-xl px-3 py-2 text-left {{ in_array($status, ['cancelled', 'disputed'], true) ? 'text-[#b42318] hover:bg-[#fff6f6]' : 'text-[#2f6bff] hover:bg-[#f2f7ff]' }}">{{ $label }}</button>
                                            </form>
                                        @endif
                                    @endforeach
                                </x-dashboards.action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucune réservation trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $bookings->links() }}</div>
    </section>
</x-dashboards.sap-shell>
