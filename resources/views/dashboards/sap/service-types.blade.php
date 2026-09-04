<x-dashboards.sap-shell title="Types de services" subtitle="Définissez les familles d’offres utilisées par les prestataires événementiels." active="service-types" :pending-requests-count="$pendingAccessRequestsCount" :pending-sponsorships-count="$pendingSponsorshipsCount">
    <div class="grid gap-5 xl:grid-cols-[420px_1fr]">
        <form method="POST" action="{{ route('sap.service-types.store') }}" class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
            @csrf
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#2f6bff]">Catalogue SAP</p>
                <h2 class="mt-2 text-xl font-extrabold tracking-[-0.035em] text-[#07152f]">Ajouter un type de service</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-[#6f7890]">Ces types structurent les offres PSE : sonorisation, lumière, podium, captation, photographie, décoration.</p>
            </div>

            <div class="mt-5 grid gap-4">
                <label class="block">
                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Nom du type</span>
                    <input name="name" value="{{ old('name') }}" placeholder="Sonorisation complète" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:ring-4 focus:ring-[#2f6bff]/10">
                    @error('name') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Icône</span>
                    <input name="icon" value="{{ old('icon') }}" placeholder="microphone" class="mt-2 h-12 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:ring-4 focus:ring-[#2f6bff]/10">
                    @error('icon') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Description</span>
                    <textarea name="description" rows="4" placeholder="Expliquez ce que ce type de prestation couvre." class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:ring-4 focus:ring-[#2f6bff]/10">{{ old('description') }}</textarea>
                    @error('description') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-extrabold uppercase text-[#7d879d]">Champs recommandés</span>
                    <textarea name="required_fields" rows="4" placeholder="Puissance sonore&#10;Technicien inclus&#10;Zone d’intervention" class="mt-2 w-full rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 py-3 text-sm font-bold leading-6 text-[#07152f] outline-none transition focus:border-[#2f6bff] focus:ring-4 focus:ring-[#2f6bff]/10">{{ old('required_fields') }}</textarea>
                    @error('required_fields') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>

            <button class="mt-5 h-12 w-full rounded-2xl bg-[#2f6bff] text-sm font-extrabold text-white shadow-xl shadow-[#2f6bff]/20 transition hover:-translate-y-0.5">Enregistrer le type</button>
        </form>

        <section class="rounded-[28px] bg-white p-5 shadow-2xl shadow-[#173e7a]/10 ring-1 ring-[#dce6f7] sm:p-6">
            <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_170px_auto]">
                <input name="q" value="{{ request('q') }}" placeholder="Rechercher un type" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none">
                <select name="status" class="h-12 rounded-2xl border border-[#dce6f7] bg-[#f8fbff] px-4 text-sm font-bold text-[#07152f] outline-none">
                    <option value="">Tous les statuts</option>
                    <option value="active" @selected(request('status') === 'active')>Actifs</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactifs</option>
                </select>
                <button class="h-12 rounded-2xl bg-[#07152f] px-5 text-sm font-extrabold text-white">Filtrer</button>
            </form>

            <div class="mt-5 overflow-hidden rounded-3xl border border-[#e4ebf7]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e4ebf7] text-left text-sm">
                        <thead class="bg-[#f7faff] text-xs font-extrabold uppercase text-[#7d879d]">
                            <tr>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Services</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef3fb]">
                            @forelse($types as $type)
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <p class="font-extrabold text-[#07152f]">{{ $type->name }}</p>
                                        <p class="mt-1 max-w-md text-xs font-semibold leading-5 text-[#6f7890]">{{ $type->description ?: 'Aucune description renseignée.' }}</p>
                                        @if($type->required_fields)
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach($type->required_fields as $field)
                                                    <span class="rounded-full bg-[#eef5ff] px-2 py-1 text-[11px] font-extrabold text-[#2f6bff]">{{ $field }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 font-extrabold text-[#07152f]">{{ number_format($type->services_count, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $type->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $type->is_active ? 'Actif' : 'Inactif' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <form method="POST" action="{{ route('sap.service-types.toggle', $type) }}">
                                            @csrf
                                            <button class="rounded-full border border-[#d9e2f6] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $type->is_active ? 'Désactiver' : 'Activer' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm font-bold text-[#7d879d]">Aucun type de service pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5">{{ $types->links() }}</div>
        </section>
    </div>
</x-dashboards.sap-shell>
