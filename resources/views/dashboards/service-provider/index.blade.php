@php($statusLabels = ['draft' => 'Brouillon', 'published' => 'Publié', 'suspended' => 'Suspendu', 'archived' => 'Archivé'])

<x-dashboards.service-provider-shell title="Vue d’ensemble" subtitle="Pilotez vos offres, votre visibilité et vos prochaines demandes clients." active="overview" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Services récents</h2>
                    <p class="mt-1 text-sm font-semibold text-[#6f7890]">Vos offres prêtes à recevoir des demandes qualifiées.</p>
                </div>
                <a href="{{ route('service-provider.services.create') }}" class="rounded-full bg-[#2f6bff] px-4 py-2 text-xs font-extrabold text-white">Ajouter</a>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse ($recentServices as $service)
                    <article class="grid gap-4 rounded-2xl border border-[#edf2fb] bg-[#fbfcff] p-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-base font-extrabold text-[#151821]">{{ $service->name }}</p>
                            <p class="mt-1 text-sm font-semibold text-[#6f7890]">{{ $service->type?->name }} · {{ $service->city }} · {{ number_format($service->starting_price, 0, ',', ' ') }} {{ $service->currency }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 md:justify-end">
                            <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$service->status->value] ?? 'À suivre' }}</span>
                            <a href="{{ route('service-provider.services.edit', $service) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Modifier</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-8 text-center">
                        <p class="text-base font-extrabold text-[#07152f]">Aucun service publié pour le moment.</p>
                        <p class="mt-2 text-sm font-semibold text-[#6f7890]">Créez votre première offre à partir des types configurés par le SAP.</p>
                        <a href="{{ route('service-provider.services.create') }}" class="mt-4 inline-flex rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white">Créer un service</a>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="space-y-5">
            <div class="rounded-[26px] border border-white/80 bg-[#07152f] p-5 text-white shadow-xl shadow-[#07152f]/15">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8dc1ff]">Catalogue SAP</p>
                <h2 class="mt-3 text-xl font-extrabold tracking-[-0.03em]">{{ number_format($serviceTypesCount, 0, ',', ' ') }} type(s) disponible(s)</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-white/70">Les types de services structurent les fiches, les filtres et la comparaison côté client.</p>
            </div>
        </aside>
    </div>
</x-dashboards.service-provider-shell>
