@php($statusLabels = ['pending' => 'En attente', 'verified' => 'Vérifié', 'rejected' => 'Refusé', 'suspended' => 'Suspendu'])

<x-dashboards.sap-shell title="{{ $provider->business_name }}" subtitle="Fiche prestataire · services, statuts et réservations." active="providers" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('sap.providers') }}" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-extrabold text-[#2f6bff] shadow-sm ring-1 ring-[#dce6f7]">← Retour à la liste</a>
            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$provider->verification_status->value] ?? $provider->verification_status->value }}</span>
        </div>

        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[22px] border border-white/80 bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Ville</p>
                <p class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $provider->city ?: '–' }}</p>
                <p class="mt-1 text-xs font-bold text-[#6f7890]">Zone de couverture</p>
            </div>
            <div class="rounded-[22px] border border-white/80 bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Services publiés</p>
                <p class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $services->where('status', 'published')->count() }}</p>
                <p class="mt-1 text-xs font-bold text-[#6f7890]">En catalogue public</p>
            </div>
            <div class="rounded-[22px] border border-white/80 bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Réservations</p>
                <p class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $recentReservations->count() }}</p>
                <p class="mt-1 text-xs font-bold text-[#6f7890]">Dernières demandes</p>
            </div>
            <div class="rounded-[22px] border border-white/80 bg-white p-4 shadow-sm ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Contact</p>
                <p class="mt-2 text-xl font-extrabold tracking-[-0.04em] text-[#07152f]">{{ $provider->user?->name ?? 'Non renseigné' }}</p>
                <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $provider->user?->email ?? '—' }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.7fr_1fr]">
            <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold tracking-[-0.03em] text-[#07152f]">Services</h2>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">{{ $services->count() }} au total</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-[#edf2fb]">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                            <tr>
                                <th class="px-4 py-3">Nom</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Prix</th>
                                <th class="px-4 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#edf2fb]">
                            @forelse ($services as $service)
                                <tr>
                                    <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ $service->name }}</td>
                                    <td class="px-4 py-4 font-bold text-[#64708a]">{{ $service->type?->name ?? 'Non classé' }}</td>
                                    <td class="px-4 py-4 font-extrabold">{{ number_format($service->starting_price, 0, ',', ' ') }} {{ $service->currency ?: 'XOF' }}</td>
                                    <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $service->status->value }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucun service enregistré pour ce prestataire.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold tracking-[-0.03em] text-[#07152f]">Réservations récentes</h2>
                    <span class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#7d8aa7]">Suivi</span>
                </div>

                <div class="space-y-3">
                    @forelse ($recentReservations as $item)
                        <div class="rounded-2xl border border-[#edf2fb] bg-[#f9fbff] p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-extrabold text-[#07152f]">{{ $item->title }}</p>
                                    <p class="text-xs font-bold text-[#64708a]">{{ $item->eventProject?->client?->name ?? 'Client' }}</p>
                                </div>
                                <span class="rounded-full bg-[#eef4ff] px-2 py-1 text-[10px] font-extrabold uppercase text-[#2f6bff]">{{ $item->status->value }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs font-bold text-[#64708a]">
                                <span>{{ $item->eventProject?->name ?? 'Projet' }}</span>
                                <span>{{ number_format((int) ($item->quoted_amount ?? 0), 0, ',', ' ') }} XOF</span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-[#dce6f7] bg-[#f7faff] p-4 text-sm font-bold text-[#64708a]">Aucune réservation liée à ce prestataire pour le moment.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-dashboards.sap-shell>
