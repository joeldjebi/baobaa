@php($statusLabels = ['draft' => 'Brouillon', 'published' => 'Publié', 'suspended' => 'Suspendu', 'archived' => 'Archivé'])

<x-dashboards.service-provider-shell title="Mes services" subtitle="Créez, publiez et organisez vos offres événementielles." active="services" :profile="$profile" :active-services-count="$activeServicesCount" :draft-services-count="$draftServicesCount" :requests-count="$requestsCount" :gross-revenue="$grossRevenue">
    <section class="rounded-[26px] border border-white/80 bg-white p-4 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7] sm:p-5">
        <form method="GET" class="grid gap-3 rounded-[22px] bg-[#f7faff] p-3 ring-1 ring-[#dce6f7] md:grid-cols-[minmax(0,1fr)_190px_170px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Nom du service" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="type_id" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected((string) request('type_id') === (string) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <select name="status" class="min-w-0 rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous les statuts</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Filtrer</button>
        </form>

        <div class="mt-5 overflow-x-auto rounded-2xl border border-[#edf2fb]">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d8aa7]">
                    <tr><th class="px-4 py-3">Service</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Zone</th><th class="px-4 py-3">Prix</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3 text-right">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-[#edf2fb]">
                    @forelse ($services as $service)
                        <tr class="transition hover:bg-[#fbfcff]">
                            <td class="px-4 py-4 font-extrabold text-[#151821]">{{ $service->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $service->type?->name }}</td>
                            <td class="px-4 py-4 font-semibold text-[#52617b]">{{ $service->city }} · {{ $service->service_area }}</td>
                            <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($service->starting_price, 0, ',', ' ') }} {{ $service->currency }}</td>
                            <td class="px-4 py-4"><span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-extrabold text-[#2f6bff]">{{ $statusLabels[$service->status->value] ?? 'À suivre' }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('service-provider.services.edit', $service) }}" class="rounded-full bg-[#07152f] px-3 py-1.5 text-xs font-extrabold text-white">Modifier</a>
                                    <form method="POST" action="{{ route('service-provider.services.toggle', $service) }}">
                                        @csrf
                                        <button class="rounded-full border border-[#c9d8ef] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $service->status->value === 'published' ? 'Désactiver' : 'Publier' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center font-semibold text-[#6f7890]">Aucun service trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $services->links() }}</div>
    </section>
</x-dashboards.service-provider-shell>
