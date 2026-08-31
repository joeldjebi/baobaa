<x-dashboards.sap-shell title="Clients" subtitle="Suivez les comptes clients, réservations et paiements." active="clients" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Nom, email ou téléphone" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
            <select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><option value="">Tous les statuts</option><option value="active" @selected(request('status') === 'active')>Actif</option><option value="suspended" @selected(request('status') === 'suspended')>Suspendu</option></select>
            <button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button>
        </form>
        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]"><tr><th class="px-4 py-3">Client</th><th class="px-4 py-3">Téléphone</th><th class="px-4 py-3">Réservations</th><th class="px-4 py-3">Paiements</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Action</th></tr></thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($clients as $client)
                        <tr><td class="px-4 py-4"><p class="font-extrabold text-[#07152f]">{{ $client->name }}</p><p class="text-xs font-bold text-[#64708a]">{{ $client->email }}</p></td><td class="px-4 py-4 font-bold text-[#64708a]">{{ $client->phone }}</td><td class="px-4 py-4 font-bold">{{ $client->bookings_count }}</td><td class="px-4 py-4 font-bold">{{ $client->payments_count }}</td><td class="px-4 py-4">{{ $client->status->value }}</td><td class="px-4 py-4 text-right"><x-dashboards.action-menu><a href="{{ route('sap.bookings', ['q' => $client->name]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir ses réservations</a><a href="{{ route('sap.payments', ['q' => $client->name]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir ses paiements</a><form method="POST" action="{{ route('sap.users.status', $client) }}" class="border-t border-[#edf2fb] pt-1">@csrf<input type="hidden" name="status" value="{{ $client->status->value === 'suspended' ? 'active' : 'suspended' }}"><button class="w-full rounded-xl px-3 py-2 text-left {{ $client->status->value === 'suspended' ? 'text-[#2f6bff] hover:bg-[#f2f7ff]' : 'text-[#b42318] hover:bg-[#fff6f6]' }}">{{ $client->status->value === 'suspended' ? 'Réactiver le compte' : 'Suspendre le compte' }}</button></form></x-dashboards.action-menu></td></tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucun client trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $clients->links() }}</div>
    </section>
</x-dashboards.sap-shell>
