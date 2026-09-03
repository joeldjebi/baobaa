<x-dashboards.owner-shell title="Modules complémentaires" subtitle="Pilotez les options vendables : sonorisation, mobilier, traiteur, sécurité ou services premium." active="addons" :owner-profile="$ownerProfile" :active-venues-count="$activeVenuesCount" :pending-bookings-count="$pendingBookingsCount" :confirmed-bookings-count="$confirmedBookingsCount" :gross-revenue="$grossRevenue" :active-subscription="$activeSubscription" :active-deposit-rule="$activeDepositRule" :billing-preference-label="$billingPreferenceLabel">
    <section class="rounded-[26px] border border-white/80 bg-white p-5 shadow-xl shadow-[#173e7a]/7 ring-1 ring-[#dce6f7]">
        @if (session('addon_status'))
            <div class="mb-4 rounded-2xl border border-[#b9d3ff] bg-[#f2f7ff] px-4 py-3 text-sm font-extrabold text-[#2f6bff]">{{ session('addon_status') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-extrabold tracking-[-0.03em] text-[#07152f]">Bibliothèque de modules</h2>
                <p class="mt-1 text-sm font-semibold text-[#6f7890]">Créez vos options une fois, puis sélectionnez-les dans chaque fiche espace.</p>
            </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase text-[#7d8aa7]">Modules affichés</p>
                <p class="mt-2 text-2xl font-extrabold text-[#07152f]">{{ number_format($modulesActiveCount, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-2xl bg-[#f7faff] p-4 ring-1 ring-[#dce6f7]">
                <p class="text-[11px] font-extrabold uppercase text-[#7d8aa7]">Modules désactivés</p>
                <p class="mt-2 text-2xl font-extrabold text-[#07152f]">{{ number_format($modulesInactiveCount, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-2xl bg-[#07152f] p-4 text-white">
                <p class="text-[11px] font-extrabold uppercase text-white/50">Prix moyen</p>
                <p class="mt-2 text-2xl font-extrabold">{{ number_format($modulesAveragePrice, 0, ',', ' ') }} XOF</p>
            </div>
        </div>

        <form method="POST" action="{{ route('owner.addons.store') }}" class="mt-5 grid gap-3 rounded-[22px] bg-[#f7faff] p-4 ring-1 ring-[#dce6f7] lg:grid-cols-[1fr_150px_auto]">
            @csrf
            <input name="name" placeholder="Ex : Sonorisation premium" class="rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <input name="price" type="number" placeholder="Prix" class="rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <button class="rounded-2xl bg-[#2f6bff] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/20">Ajouter</button>
            <input name="description" placeholder="Description courte du service proposé" class="rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff] lg:col-span-3">
        </form>

        <form method="GET" class="mt-4 grid gap-3 md:grid-cols-[1fr_190px_auto]">
            <input name="q" value="{{ request('q') }}" placeholder="Rechercher dans vos modules" class="rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
            <select name="status" class="rounded-2xl border border-[#dce6f7] bg-white px-4 py-3 text-sm font-bold outline-none focus:border-[#2f6bff]">
                <option value="">Tous</option>
                <option value="active" @selected(request('status') === 'active')>Actifs</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Désactivés</option>
            </select>
            <button class="rounded-2xl bg-[#07152f] px-5 py-3 text-sm font-extrabold text-white">Filtrer</button>
        </form>

        <div class="mt-5 grid gap-3 xl:grid-cols-2">
            @forelse ($moduleTemplates as $module)
                <article class="rounded-[22px] border border-[#edf2fb] bg-[#fbfcff] p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="rounded-full {{ $module->is_active ? 'bg-[#eaf7ef] text-[#128043]' : 'bg-[#fff6f6] text-[#b42318]' }} px-3 py-1 text-xs font-extrabold">{{ $module->is_active ? 'Actif' : 'Désactivé' }}</span>
                        <span class="text-sm font-extrabold text-[#07152f]">{{ number_format($module->price, 0, ',', ' ') }} {{ $module->currency }}</span>
                    </div>
                    <form method="POST" action="{{ route('owner.addons.update', $module) }}" class="grid gap-3 lg:grid-cols-[1fr_150px_auto]">
                        @csrf
                        @method('PATCH')
                        <label>
                            <span class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Nom du module</span>
                            <input name="name" value="{{ $module->name }}" class="mt-1.5 w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold text-[#151821] outline-none focus:border-[#2f6bff]">
                        </label>
                        <label>
                            <span class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Prix</span>
                            <input name="price" type="number" value="{{ $module->price }}" class="mt-1.5 w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold text-[#151821] outline-none focus:border-[#2f6bff]">
                        </label>
                        <button class="self-end rounded-xl bg-[#07152f] px-4 py-2.5 text-xs font-extrabold text-white">Enregistrer</button>
                        <label class="lg:col-span-3">
                            <span class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#7d8aa7]">Description affichée au client</span>
                            <input name="description" value="{{ $module->description }}" class="mt-1.5 w-full rounded-xl border border-[#dce6f7] bg-white px-3 py-2.5 text-sm font-bold text-[#151821] outline-none focus:border-[#2f6bff]">
                        </label>
                    </form>

                    <div class="mt-3 flex flex-wrap justify-end gap-2 border-t border-[#edf2fb] pt-3">
                            <form method="POST" action="{{ route('owner.addons.toggle', $module) }}">
                            @csrf
                                <button class="rounded-full border border-[#c9d8ef] px-3 py-1.5 text-xs font-extrabold text-[#2f6bff]">{{ $module->is_active ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                            <form method="POST" action="{{ route('owner.addons.delete', $module) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full bg-[#fff6f6] px-3 py-1.5 text-xs font-extrabold text-[#b42318]">Supprimer</button>
                            </form>
                    </div>
                </article>
            @empty
                <p class="rounded-2xl border border-dashed border-[#cbd8f4] bg-[#fbfcff] p-5 text-sm font-semibold text-[#6f7890]">Aucun module complémentaire enregistré.</p>
            @endforelse
        </div>

        <div class="mt-5">{{ $moduleTemplates->links() }}</div>
    </section>
</x-dashboards.owner-shell>
