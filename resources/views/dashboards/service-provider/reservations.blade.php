@php
    $badgeClass = [
        'negotiating' => 'bg-amber-50 text-amber-700',
        'awaiting_client_confirmation' => 'bg-blue-50 text-blue-700',
        'awaiting_provider_confirmation' => 'bg-indigo-50 text-indigo-700',
        'awaiting_payment' => 'bg-purple-50 text-purple-700',
        'confirmed' => 'bg-emerald-50 text-emerald-700',
        'cancelled' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<x-dashboards.service-provider-shell title="Réservations" subtitle="Suivez les demandes clients liées à vos services, négociez les montants et validez les proformas de prestation." active="reservations" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <section class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Pipeline prestataire</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.04em] text-[#07152f]">Demandes de services</h2>
                <p class="mt-1 text-sm font-semibold text-[#6f7890]">Chaque ligne correspond à une réservation client. Les services demandés sont regroupés à l’intérieur.</p>
            </div>
            <a href="{{ route('service-provider.services.create') }}" class="rounded-2xl bg-[#07152f] px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#07152f]/15 transition hover:-translate-y-0.5 hover:bg-[#2f6bff]">Ajouter un service</a>
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_230px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Référence, client, service..." class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none focus:border-[#2f6bff]">
            <select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="h-12 rounded-2xl bg-[#2f6bff] px-5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="mt-5 overflow-hidden rounded-3xl border border-[#e4ebf7]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e4ebf7] text-left text-sm">
                    <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d879d]">
                        <tr>
                            <th class="px-4 py-3">Projet</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Montant</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef3fb]">
                        @forelse ($reservations as $reservation)
                            @php
                                $providerItems = $reservation->provider_items;
                                $providerTotal = (int) $providerItems->sum('quoted_amount');
                                $providerStatus = $providerItems->contains(fn ($item) => $item->status->value === 'negotiating')
                                    ? 'negotiating'
                                    : ($providerItems->first()?->status->value ?? 'negotiating');
                            @endphp
                            <tr class="align-top transition hover:bg-[#f8fbff]">
                                <td class="px-4 py-4">
                                    <p class="font-extrabold text-[#07152f]">{{ $reservation->name }}</p>
                                    <p class="mt-1 text-xs font-bold text-[#6f7890]">{{ $reservation->reference }} · {{ number_format($providerItems->count(), 0, ',', ' ') }} service{{ $providerItems->count() > 1 ? 's' : '' }}</p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($providerItems->take(3) as $item)
                                            <span class="rounded-full bg-[#eef4ff] px-2 py-1 text-[11px] font-extrabold text-[#2f6bff]">{{ $item->title }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-bold text-[#07152f]">{{ $reservation->client?->name }}</p>
                                    <p class="mt-1 text-xs font-semibold text-[#6f7890]">{{ $reservation->client?->email }}</p>
                                </td>
                                <td class="px-4 py-4 font-bold text-[#64708a]">{{ $reservation->event_date?->format('d/m/Y') ?? 'À préciser' }}</td>
                                <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($providerTotal, 0, ',', ' ') }} {{ $reservation->currency }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $badgeClass[$providerStatus] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$providerStatus] ?? 'À suivre' }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('service-provider.reservations.show', $reservation) }}" class="inline-flex rounded-full bg-[#2f6bff] px-3 py-1.5 text-xs font-extrabold text-white shadow-lg shadow-[#2f6bff]/15">Gérer</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <p class="text-base font-extrabold text-[#07152f]">Aucune demande pour le moment.</p>
                                    <p class="mt-2 text-sm font-semibold text-[#6f7890]">Les futures demandes liées à vos services apparaîtront ici.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $reservations->links() }}</div>
    </section>
</x-dashboards.service-provider-shell>
