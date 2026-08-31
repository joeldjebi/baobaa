<x-dashboards.sap-shell title="Partenaires PEE" subtitle="Contrôlez les profils propriétaires, leur vérification et leur activité." active="owners" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Nom, email, entreprise" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold"><option value="">Tous les statuts</option><option value="pending" @selected(request('status') === 'pending')>En attente</option><option value="verified" @selected(request('status') === 'verified')>Vérifié</option><option value="rejected" @selected(request('status') === 'rejected')>Refusé</option></select>
            <button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button>
        </form>
        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]"><tr><th class="px-4 py-3">Partenaire</th><th class="px-4 py-3">Contact</th><th class="px-4 py-3">Ville</th><th class="px-4 py-3">Espaces</th><th class="px-4 py-3">Sponsorings</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Action</th></tr></thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($owners as $owner)
                        <tr>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ $owner->business_name }}</td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $owner->user?->email }}</td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $owner->city }}</td>
                            <td class="px-4 py-4 font-bold">{{ $owner->venues_count }}</td>
                            <td class="px-4 py-4 font-bold">{{ $owner->sponsorship_campaigns_count }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $owner->verification_status->value }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <x-dashboards.action-menu>
                                    <a href="{{ route('owner-profiles.show', $owner) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir le profil public</a>
                                    <a href="{{ route('sap.venues', ['q' => $owner->business_name]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir ses espaces</a>

                                    @if ($owner->user)
                                        <form method="POST" action="{{ route('sap.users.status', $owner->user) }}" class="border-t border-[#edf2fb] pt-1">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $owner->user->status->value === 'suspended' ? 'active' : 'suspended' }}">
                                            <button class="w-full rounded-xl px-3 py-2 text-left {{ $owner->user->status->value === 'suspended' ? 'text-[#2f6bff] hover:bg-[#f2f7ff]' : 'text-[#b42318] hover:bg-[#fff6f6]' }}">{{ $owner->user->status->value === 'suspended' ? 'Réactiver le compte' : 'Suspendre le compte' }}</button>
                                        </form>
                                    @endif
                                </x-dashboards.action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucun partenaire trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $owners->links() }}</div>
    </section>
</x-dashboards.sap-shell>
