@php($statusLabels = ['pending' => 'En attente', 'verified' => 'Vérifié', 'rejected' => 'Refusé', 'suspended' => 'Suspendu'])

<x-dashboards.sap-shell title="PSE" subtitle="Suivez les prestataires, leurs services publiés et leurs réservations actives." active="providers" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Entreprise, ville ou contact" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold">
                <option value="">Tous les statuts</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white">Filtrer</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr>
                        <th class="px-4 py-3">Prestataire</th>
                        <th class="px-4 py-3">Ville</th>
                        <th class="px-4 py-3">Services</th>
                        <th class="px-4 py-3">Réservations</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($providers as $provider)
                        <tr>
                            <td class="px-4 py-4">
                                <a href="{{ route('sap.providers.show', $provider) }}" class="font-extrabold text-[#07152f] hover:text-[#2f6bff]">{{ $provider->business_name }}</a>
                                <p class="text-xs font-bold text-[#64708a]">{{ $provider->user?->name ?? 'Contact non renseigné' }}</p>
                            </td>
                            <td class="px-4 py-4 font-bold text-[#64708a]">{{ $provider->city ?: '–' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($provider->published_services->take(2) as $service)
                                        <span class="rounded-full bg-[#eef4ff] px-2.5 py-1 text-[10px] font-extrabold text-[#2f6bff]">{{ $service->name }}</span>
                                    @empty
                                        <span class="text-xs font-bold text-[#64708a]">Aucun service publié</span>
                                    @endforelse
                                    @if ($provider->published_services->count() > 2)
                                        <span class="rounded-full border border-[#dce6f7] px-2.5 py-1 text-[10px] font-extrabold text-[#64708a]">+{{ $provider->published_services->count() - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($provider->reservation_items->take(2) as $reservation)
                                        <span class="rounded-full bg-[#f5f7ff] px-2.5 py-1 text-[10px] font-extrabold text-[#52617b]">{{ $reservation->title }}</span>
                                    @empty
                                        <span class="text-xs font-bold text-[#64708a]">Aucune réservation</span>
                                    @endforelse
                                    @if ($provider->reservation_items->count() > 2)
                                        <span class="rounded-full border border-[#dce6f7] px-2.5 py-1 text-[10px] font-extrabold text-[#64708a]">+{{ $provider->reservation_items->count() - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$provider->verification_status->value] ?? $provider->verification_status->value }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <x-dashboards.action-menu>
                                    <a href="{{ route('sap.providers.show', $provider) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir le détail</a>
                                    <a href="{{ route('sap.bookings', ['q' => $provider->business_name]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir les réservations</a>
                                </x-dashboards.action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucun prestataire trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $providers->links() }}</div>
    </section>
</x-dashboards.sap-shell>
