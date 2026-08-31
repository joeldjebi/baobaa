@php
    $roleLabels = ['owner' => 'Partenaire PEE', 'client' => 'Client'];
    $statusLabels = ['pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Refusée'];
@endphp

<x-dashboards.sap-shell title="Validations SAP" subtitle="Approuvez les accès sensibles et les campagnes sponsorisées." active="requests" :owners-count="$ownersCount" :clients-count="$clientsCount" :published-venues-count="$publishedVenuesCount" :pending-access-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount" :gross-payments-amount="$grossPaymentsAmount" :active-bookings-count="$activeBookingsCount">
    <section class="rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold text-[#07152f]">Demandes de portails</h2>
                <p class="mt-1 text-sm font-semibold text-[#64708a]">Aucun accès PEE n’est ouvert sans validation SAP.</p>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="role" class="h-11 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-3 text-sm font-bold"><option value="">Tous les rôles</option>@foreach ($roleLabels as $value => $label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select>
                <select name="status" class="h-11 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-3 text-sm font-bold"><option value="">Tous les statuts</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
                <button class="h-11 rounded-2xl bg-[#2f6bff] px-4 text-sm font-extrabold text-white">Filtrer</button>
            </form>
        </div>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]"><tr><th class="px-4 py-3">Demandeur</th><th class="px-4 py-3">Accès</th><th class="px-4 py-3">Entreprise</th><th class="px-4 py-3">Ville</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Décision</th></tr></thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($requests as $accessRequest)
                        <tr>
                            <td class="px-4 py-4"><p class="font-extrabold text-[#07152f]">{{ $accessRequest->user?->name }}</p><p class="text-xs font-bold text-[#64708a]">{{ $accessRequest->user?->email }}</p></td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $roleLabels[$accessRequest->requested_role->value] ?? $accessRequest->requested_role->value }}</td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $accessRequest->business_name ?? 'Compte client' }}</td>
                            <td class="px-4 py-4 font-bold text-[#52617b]">{{ $accessRequest->city ?? 'Non renseignée' }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$accessRequest->status] ?? $accessRequest->status }}</span></td>
                            <td class="px-4 py-4 text-right">
                                <x-dashboards.action-menu>
                                    @if ($accessRequest->user)
                                        <a href="{{ route('sap.clients', ['q' => $accessRequest->user->email]) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir le compte</a>
                                    @endif
                                    @if ($accessRequest->status === 'pending')
                                        <form method="POST" action="{{ route('sap.portal-requests.decide', $accessRequest) }}" class="border-t border-[#edf2fb] pt-1">@csrf<input type="hidden" name="decision" value="approve"><button class="w-full rounded-xl px-3 py-2 text-left text-[#2f6bff] hover:bg-[#f2f7ff]">Approuver l’accès</button></form>
                                        <form method="POST" action="{{ route('sap.portal-requests.decide', $accessRequest) }}" class="border-t border-[#edf2fb] pt-1">@csrf<input type="hidden" name="decision" value="reject"><button class="w-full rounded-xl px-3 py-2 text-left text-[#b42318] hover:bg-[#fff6f6]">Refuser la demande</button></form>
                                    @else
                                        <p class="px-3 py-2 text-xs font-bold text-[#7d8aa7]">Traité le {{ $accessRequest->reviewed_at?->format('d/m/Y H:i') }}</p>
                                    @endif
                                </x-dashboards.action-menu>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-[#64708a]">Aucune demande trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $requests->links() }}</div>
    </section>

    <section class="mt-6 rounded-[28px] bg-white p-5 shadow-xl shadow-[#173e7a]/8 ring-1 ring-[#dce6f7]">
        <h2 class="text-xl font-extrabold text-[#07152f]">Sponsorings à valider</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            @forelse ($sponsorships as $campaign)
                <article class="rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4">
                    <p class="text-base font-extrabold text-[#07152f]">{{ $campaign->name }}</p>
                    <p class="mt-1 text-sm font-semibold text-[#64708a]">{{ $campaign->venue?->name }} · {{ number_format($campaign->budget_amount, 0, ',', ' ') }} {{ $campaign->currency }} · {{ $campaign->starts_on?->format('d/m/Y') }} au {{ $campaign->ends_on?->format('d/m/Y') }}</p>
                    <div class="mt-4">
                        <x-dashboards.action-menu>
                            @if ($campaign->venue)
                                <a href="{{ route('venues.show', $campaign->venue->slug) }}" class="block rounded-xl px-3 py-2 text-[#52617b] hover:bg-[#f2f7ff] hover:text-[#2f6bff]">Voir l’espace</a>
                            @endif
                            <form method="POST" action="{{ route('sap.sponsorships.decide', $campaign) }}" class="border-t border-[#edf2fb] pt-1">@csrf<input type="hidden" name="decision" value="approve"><button class="w-full rounded-xl px-3 py-2 text-left text-[#2f6bff] hover:bg-[#f2f7ff]">Approuver la campagne</button></form>
                            <form method="POST" action="{{ route('sap.sponsorships.decide', $campaign) }}" class="border-t border-[#edf2fb] pt-1">@csrf<input type="hidden" name="decision" value="reject"><button class="w-full rounded-xl px-3 py-2 text-left text-[#b42318] hover:bg-[#fff6f6]">Refuser la campagne</button></form>
                        </x-dashboards.action-menu>
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold text-[#64708a]">Aucune campagne en attente.</p>
            @endforelse
        </div>
    </section>
</x-dashboards.sap-shell>
